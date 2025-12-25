<?php

namespace App\Http\Controllers;

use App\Organization;
use App\OrganizationTypes;
use App\StaticCategory;
use App\Country;
use App\State;
use App\City;
use App\User;
use App\Http\Requests\OrganizationStep1Request;
use App\Http\Requests\OrganizationStep2Request;
use App\Http\Requests\CreateOrganizationManagerRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class OrganizationWizardController extends Controller
{
    /**
     * Show the wizard form for creating a new organization.
     */
    public function create()
    {
        $organizationTypes = OrganizationTypes::all();
        $industries = StaticCategory::all();
        $countries = Country::all();
        $states = collect();
        $cities = collect();

        return view('admin.organizations.wizard.create', compact(
            'organizationTypes',
            'industries',
            'countries',
            'states',
            'cities'
        ));
    }

    /**
     * Show the wizard form for editing an existing organization.
     */
    public function edit($id)
    {
        $organization = Organization::with(['organizationType', 'industries', 'country', 'state', 'city', 'assignedUsers'])->findOrFail($id);
        $organizationTypes = OrganizationTypes::all();
        $industries = StaticCategory::all();
        $countries = Country::all();

        // Pre-load states and cities if organization already has selections
        $states = $organization->country_id
            ? State::where('country_id', $organization->country_id)->get()
            : collect();
        $cities = $organization->state_id
            ? City::where('state_id', $organization->state_id)->get()
            : collect();

        // Get assigned managers for step 3
        $assignedManagers = $organization->assignedUsers()->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->full_name ?? ($user->first_name_en . ' ' . $user->last_name_en),
                'email' => $user->email,
            ];
        });

        return view('admin.organizations.wizard.create', compact(
            'organization',
            'organizationTypes',
            'industries',
            'countries',
            'states',
            'cities',
            'assignedManagers'
        ));
    }

    /**
     * Store step 1 data for a new organization.
     */
    public function store(OrganizationStep1Request $request)
    {
        $_user = auth()->user();
        if( !$_user->ableTo('organization.wizard.create') ) { return abort(403); }
        DB::beginTransaction();

        try {
            $organization = new Organization();
            $organization->name_en = $request->name_en;
            $organization->name_ar = $request->name_ar;
            $organization->organization_type_id = $request->organization_type_id;
            $organization->status = $request->status;
            $organization->bio = $request->bio;
            $organization->is_draft = true;
            $organization->wizard_step = 2;

            // Handle image upload using Storage disk
            if ($request->hasFile('img_url')) {
                $image = $request->file('img_url');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                Storage::disk('organizations')->put($imageName, file_get_contents($image));
                $organization->img_url = $imageName;
            }

            $organization->save();

            // Sync industries (many-to-many)
            if ($request->has('industries') && is_array($request->industries)) {
                $organization->industries()->sync($request->industries);
            }

            DB::commit();

            // Redirect to step 2 after saving step 1
            return redirect()
                ->route('organization.wizard.edit', ['id' => $organization->id, 'step' => 2])
                ->with('success', __('admin.Organization basics saved successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', __('admin.Error saving organization') . ': ' . $e->getMessage());
        }
    }

    /**
     * Update step 1 data for an existing organization.
     */
    public function updateStep1(OrganizationStep1Request $request, $id)
    {
        $_user = auth()->user();
        if( !$_user->ableTo('organization.wizard.edit') ) { return abort(403); }
        $organization = Organization::findOrFail($id);

        // Check if organization is published and prevent reverting to draft
        if (!$organization->is_draft && $request->has('revert_to_draft')) {
            return back()
                ->withInput()
                ->with('error', __('admin.Published organizations cannot be reverted to draft'));
        }

        DB::beginTransaction();

        try {
            $organization->name_en = $request->name_en;
            $organization->name_ar = $request->name_ar;
            $organization->organization_type_id = $request->organization_type_id;
            $organization->status = $request->status;
            $organization->bio = $request->bio;

            // Update wizard step if moving forward
            if ($organization->wizard_step < 2) {
                $organization->wizard_step = 2;
            }

            // Handle image upload using Storage disk
            if ($request->hasFile('img_url')) {
                // Delete old image if exists
                if ($organization->img_url && Storage::disk('organizations')->exists($organization->img_url)) {
                    Storage::disk('organizations')->delete($organization->img_url);
                }

                $image = $request->file('img_url');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                Storage::disk('organizations')->put($imageName, file_get_contents($image));
                $organization->img_url = $imageName;
            }

            $organization->save();

            // Sync industries (many-to-many)
            if ($request->has('industries')) {
                $organization->industries()->sync($request->industries ?? []);
            }

            DB::commit();

            // Redirect to step 2 after saving step 1
            return redirect()
                ->route('organization.wizard.edit', ['id' => $organization->id, 'step' => 2])
                ->with('success', __('admin.Organization basics updated successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', __('admin.Error updating organization') . ': ' . $e->getMessage());
        }
    }

    /**
     * Update step 2 data (Contact Information & Social Media).
     */
    public function updateStep2(OrganizationStep2Request $request, $id)
    {
        $_user = auth()->user();
        if( !$_user->ableTo('organization.wizard.edit') ) { return abort(403); }
        $organization = Organization::findOrFail($id);

        DB::beginTransaction();

        try {
            // Contact Information
            $organization->contact_email = $request->contact_email;
            $organization->country_code = $request->country_code;
            $organization->contact_phone = $request->contact_phone;
            $organization->country_id = $request->country_id;
            $organization->state_id = $request->state_id;
            $organization->city_id = $request->city_id;
            $organization->address = $request->address;
            $organization->google_map_url = $request->google_map_url;

            // Social Media
            $organization->linkedin_company_url = $request->linkedin_company_url;
            $organization->twitter_handle = $request->twitter_handle;
            $organization->facebook_page_url = $request->facebook_page_url;
            $organization->instagram_profile_url = $request->instagram_profile_url;
            $organization->youtube_channel_url = $request->youtube_channel_url;
            $organization->tiktok_url = $request->tiktok_url;

            // Update wizard step if moving forward
            if ($organization->wizard_step < 3) {
                $organization->wizard_step = 3;
            }

            $organization->save();

            DB::commit();

            // Redirect to step 3 after saving step 2
            return redirect()
                ->route('organization.wizard.edit', ['id' => $organization->id, 'step' => 3])
                ->with('success', __('admin.Contact information saved successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', __('admin.Error saving contact information') . ': ' . $e->getMessage());
        }
    }

    /**
     * Get managers for AJAX DataTable.
     */
    public function getManagers(Request $request, $id)
    {
        $_user = auth()->user();
        if( !$_user->ableTo('organization.wizard.edit') ) { return abort(403); }
        $organization = Organization::findOrFail($id);
        $locale = app()->getLocale();

        // Get users with organizationmanager or Sub-Admin roles
        $query = User::whereHas('roles', function ($q) {
            $q->whereIn('default_for', ['organizationmanager', 'subadmin']);
        });

        // Search filter
        if ($request->filled('search_term')) {
            $searchTerm = $request->search_term;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('email', 'like', "%{$searchTerm}%")
                  ->orWhere('first_name_en', 'like', "%{$searchTerm}%")
                  ->orWhere('last_name_en', 'like', "%{$searchTerm}%")
                  ->orWhere('first_name_ar', 'like', "%{$searchTerm}%")
                  ->orWhere('last_name_ar', 'like', "%{$searchTerm}%");
            });
        }

        // Get assigned manager IDs for this organization
        $assignedManagerIds = $organization->assignedUsers()->pluck('users.id')->toArray();

        // Pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 5);
        $totalRecords = $query->count();

        $managers = $query->skip($start)->take($length)->get();


        $data = $managers->map(function ($user) use ($locale, $assignedManagerIds) {
            $name = $locale === 'ar'
                ? (($user->first_name_ar ?? $user->first_name_en) . ' ' . ($user->last_name_ar ?? $user->last_name_en))
                : ($user->first_name_en . ' ' . $user->last_name_en);

            $roleName = $user->roles->first()->default_for ?? '';
            $roleName = trim( strtolower( $roleName ) ) ;
            $roleDisplay = $roleName === 'organizationmanager'
                ? __('admin.Organization Manager')
                : ($roleName === 'subadmin' ? __('admin.Sub-Admin') : $roleName);

            // Calculate organizations count based on role
            // Organization Manager: count unique orgs (organization_id + pivot table)
            // Sub-Admin: count only from pivot table
            if ($roleName === 'organizationmanager') {
                // Get unique organization IDs from both organization_id and pivot
                $orgIds = $user->organizations()->pluck('organizations.id')->toArray();
                if ($user->organization_id) {
                    $orgIds[] = $user->organization_id;
                }
                $organizationsCount = count(array_unique($orgIds));
            } else {
                // Sub-Admin: only count from pivot table
                $organizationsCount = $user->organizations()->count();
            }

            return [
                'id' => $user->id,
                'name' => $name,
                'email' => $user->email,
                'role' => $roleDisplay,
                'organizations_count' => $organizationsCount,
                'is_selected' => in_array($user->id, $assignedManagerIds),
                'avatar' => $user->image_url,
            ];
        });


        return response()->json([
            'draw' => intval($request->input('draw', 1)),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $totalRecords,
            'data' => $data,
        ]);
    }

    /**
     * Update step 3 data (Organization Managers).
     */
    public function updateStep3(Request $request, $id)
    {
        $_user = auth()->user();
        if( !$_user->ableTo('organization.wizard.edit') ) { return abort(403); }
        $organization = Organization::findOrFail($id);

        // Validate at least one manager
        $request->validate([
            'manager_ids' => 'required|array|min:1',
            'manager_ids.*' => 'exists:users,id',
        ], [
            'manager_ids.required' => __('admin.Please select or create at least one organization manager'),
            'manager_ids.min' => __('admin.Please select or create at least one organization manager'),
        ]);

        DB::beginTransaction();

        try {
            $managerIds = $request->manager_ids;
            $this->syncManagersToOrganization($organization, $managerIds);

            // Update wizard step if moving forward
            if ($organization->wizard_step < 4) {
                $organization->wizard_step = 4;
            }

            $organization->save();

            DB::commit();

            // Redirect to step 4 after saving step 3
            return redirect()
                ->route('organization.wizard.edit', ['id' => $organization->id, 'step' => 4])
                ->with('success', __('admin.Managers saved successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', __('admin.Error saving managers') . ': ' . $e->getMessage());
        }
    }

    /**
     * Create a new manager via AJAX.
     */
    public function createManager(CreateOrganizationManagerRequest $request, $id)
    {
        $_user = auth()->user();
        if( !$_user->ableTo('organization.wizard.edit') ) { return abort(403); }
        $organization = Organization::findOrFail($id);
        $locale = app()->getLocale();

        DB::beginTransaction();

        try {
            // Create the user
            $user = new User();
            $user->first_name_en = $request->first_name_en;
            $user->last_name_en = $request->last_name_en;
            $user->first_name_ar = $request->first_name_ar;
            $user->last_name_ar = $request->last_name_ar;
            $user->gender = $request->gender;
            $user->email = $request->email;
            $user->country_code = $request->country_code;
            $user->phone = $request->phone;
            $user->password = Hash::make($request->password);
            $user->organization_id = $organization->id;
            $user->email_verified_at = now();
            $user->save();

            // Assign Organization Manager role
            $user->allotRole('organizationmanager');

            // Add to user_organizations pivot table
            $organization->assignedUsers()->attach($user->id);

            DB::commit();

            $name = $locale === 'ar'
                ? (($user->first_name_ar ?? $user->first_name_en) . ' ' . ($user->last_name_ar ?? $user->last_name_en))
                : ($user->first_name_en . ' ' . $user->last_name_en);

            return response()->json([
                'success' => true,
                'message' => __('admin.Manager created successfully'),
                'manager' => [
                    'id' => $user->id,
                    'name' => $name,
                    'email' => $user->email,
                ],
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => __('admin.Error creating manager') . ': ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync managers to organization and handle role switching.
     */
    protected function syncManagersToOrganization($organization, $managerIds)
    {
        // Get current assignments
        $currentManagerIds = $organization->assignedUsers()->pluck('users.id')->toArray();

        // Sync the pivot table
        $organization->assignedUsers()->sync($managerIds);

        // Handle role switching for each manager
        foreach ($managerIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            $orgCount = $user->organizations()->count();

            // If user now has more than 1 organization and has organizationmanager role, switch to Sub-Admin
            if ($orgCount > 1 && $user->hasRole('organizationmanager')) {
                $this->switchToSubAdmin($user);
            }
        }

        // Handle removed managers - check if they need role change
        $removedIds = array_diff($currentManagerIds, $managerIds);
        foreach ($removedIds as $userId) {
            $user = User::find($userId);
            if (!$user) continue;

            $orgCount = $user->organizations()->count();

            // If user now has only 1 org and has Sub-Admin role, switch back to organizationmanager
            if ($orgCount == 1 && $user->hasRole('subadmin')) {
                $this->switchToOrgManager($user);
            }
        }
    }

    /**
     * Switch user role from Organization Manager to Sub-Admin.
     */
    protected function switchToSubAdmin($user)
    {
        $user->deAllotRole('organizationmanager');
        $user->allotRole('subadmin');
        // Clear the organization_id field as sub-admins use pivot table
        $user->organization_id = null;
        $user->save();
    }

    /**
     * Switch user role from Sub-Admin to Organization Manager.
     */
    protected function switchToOrgManager($user)
    {
        $user->deAllotRole('subadmin');
        $user->allotRole('organizationmanager');
        // Set organization_id to their remaining organization
        $remainingOrg = $user->organizations()->first();
        if ($remainingOrg) {
            $user->organization_id = $remainingOrg->id;
        }
        $user->save();
    }

    /**
     * Save organization as draft (no validation required for mandatory fields).
     */
    public function saveDraft(Request $request, $id = null)
    {
        $_user = auth()->user();
        if( !$_user->ableTo('organization.wizard.edit') ) { return abort(403); }
        // Minimal validation for draft - only validate if values are provided
        $rules = [
            // Step 1 fields
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'organization_type_id' => 'nullable|exists:organization_types,id',
            'status' => 'nullable|boolean',
            'bio' => 'nullable|string|max:1000',
            'industries' => 'nullable|array',
            'industries.*' => 'exists:static_categories,id',
            'img_url' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
            // Step 2 fields (Contact Information)
            'contact_email' => 'nullable|email|max:255',
            'country_code' => 'nullable|string|max:10',
            'contact_phone' => 'nullable|string|max:20',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'address' => 'nullable|string|max:500',
            'google_map_url' => 'nullable|string|max:500',
            // Step 2 fields (Social Media)
            'linkedin_company_url' => 'nullable|url|max:255',
            'twitter_handle' => 'nullable|url|max:255',
            'facebook_page_url' => 'nullable|url|max:255',
            'instagram_profile_url' => 'nullable|url|max:255',
            'youtube_channel_url' => 'nullable|url|max:255',
            'tiktok_url' => 'nullable|url|max:255',
            // Step 3 fields (Managers)
            'manager_ids' => 'nullable|array',
            'manager_ids.*' => 'exists:users,id',
        ];

        $messages = [];
        $locale = app()->getLocale();

        if ($locale === 'ar') {
            $messages = [
                'img_url.image' => 'نوع الملف غير صالح، يرجى رفع ملفات من نوع PNG أو JPG فقط',
                'img_url.mimes' => 'نوع الملف غير صالح، يرجى رفع ملفات من نوع PNG أو JPG فقط',
                'img_url.max' => 'الملف اكبر من الحد المسموح. الحد الأقصى المسموح به هو 5 ميجابايت',
            ];
        } else {
            $messages = [
                'img_url.image' => 'Invalid file format. Please upload PNG or JPG files only.',
                'img_url.mimes' => 'Invalid file format. Please upload PNG or JPG files only.',
                'img_url.max' => 'File too large. Maximum size allowed is 5MB.',
            ];
        }

        $request->validate($rules, $messages);

        DB::beginTransaction();

        try {
            if ($id) {
                $organization = Organization::findOrFail($id);

                // Prevent saving as draft if already published
                if (!$organization->is_draft) {
                    return back()
                        ->withInput()
                        ->with('error', __('admin.Published organizations cannot be saved as draft'));
                }
            } else {
                $organization = new Organization();
                $organization->is_draft = true;
                $organization->wizard_step = 1;
            }

            // Only update fields that are provided (Step 1)
            if ($request->filled('name_en')) {
                $organization->name_en = $request->name_en;
            }
            if ($request->filled('name_ar')) {
                $organization->name_ar = $request->name_ar;
            }
            if ($request->filled('organization_type_id')) {
                $organization->organization_type_id = $request->organization_type_id;
            }
            if ($request->has('status')) {
                $organization->status = $request->status;
            }
            if ($request->filled('bio')) {
                $organization->bio = $request->bio;
            }

            // Step 2: Contact Information
            if ($request->filled('contact_email')) {
                $organization->contact_email = $request->contact_email;
            }
            if ($request->filled('country_code')) {
                $organization->country_code = $request->country_code;
            }
            if ($request->filled('contact_phone')) {
                $organization->contact_phone = $request->contact_phone;
            }
            if ($request->filled('country_id')) {
                $organization->country_id = $request->country_id;
            }
            if ($request->filled('state_id')) {
                $organization->state_id = $request->state_id;
            }
            if ($request->filled('city_id')) {
                $organization->city_id = $request->city_id;
            }
            if ($request->filled('address')) {
                $organization->address = $request->address;
            }
            if ($request->filled('google_map_url')) {
                $organization->google_map_url = $request->google_map_url;
            }

            // Step 2: Social Media
            if ($request->has('linkedin_company_url')) {
                $organization->linkedin_company_url = $request->linkedin_company_url;
            }
            if ($request->has('twitter_handle')) {
                $organization->twitter_handle = $request->twitter_handle;
            }
            if ($request->has('facebook_page_url')) {
                $organization->facebook_page_url = $request->facebook_page_url;
            }
            if ($request->has('instagram_profile_url')) {
                $organization->instagram_profile_url = $request->instagram_profile_url;
            }
            if ($request->has('youtube_channel_url')) {
                $organization->youtube_channel_url = $request->youtube_channel_url;
            }
            if ($request->has('tiktok_url')) {
                $organization->tiktok_url = $request->tiktok_url;
            }

            // Handle image upload using Storage disk
            if ($request->hasFile('img_url')) {
                // Delete old image if exists
                if ($organization->img_url && Storage::disk('organizations')->exists($organization->img_url)) {
                    Storage::disk('organizations')->delete($organization->img_url);
                }

                $image = $request->file('img_url');
                $imageName = time() . '.' . $image->getClientOriginalExtension();
                Storage::disk('organizations')->put($imageName, file_get_contents($image));
                $organization->img_url = $imageName;
            }

            // Determine current step from request (default to 1)
            $currentStep = (int) $request->input('current_step', 1);

            // Update wizard_step based on current step if it's higher
            // This enables the next step tab
            if ($currentStep >= $organization->wizard_step) {
                $organization->wizard_step = $currentStep + 1;
            }

            $organization->save();

            // Sync industries if provided
            if ($request->has('industries')) {
                $organization->industries()->sync($request->industries ?? []);
            }

            // Sync managers if provided (Step 3)
            if ($request->has('manager_ids') && is_array($request->manager_ids)) {
                $this->syncManagersToOrganization($organization, $request->manager_ids);
            }

            DB::commit();

            // Stay on the current step after saving draft
            return redirect()
                ->route('organization.wizard.edit', ['id' => $organization->id, 'step' => $currentStep])
                ->with('success', __('admin.Organization saved as draft successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', __('admin.Error saving draft') . ': ' . $e->getMessage());
        }
    }

    /**
     * Publish a draft organization.
     */
    public function publish($id)
    {
        $_user = auth()->user();
        if( !$_user->ableTo('organization.wizard.edit') ) { return abort(403); }
        $organization = Organization::findOrFail($id);

        // Check if already published
        if (!$organization->is_draft) {
            return back()->with('error', __('admin.Organization is already published'));
        }

        // Check if has at least one manager
        if ($organization->assignedUsers()->count() === 0) {
            return back()->with('error', __('admin.Organization must have at least one manager to publish'));
        }

        $organization->is_draft = false;
        $organization->save();

        return redirect()
            ->route('organization.wizard.edit', $organization->id)
            ->with('success', __('admin.Organization published successfully'));
    }

    /**
     * Serve organization logo from storage.
     */
    public function getLogo($filename)
    {
        $disk = Storage::disk('organizations');

        if (!$disk->exists($filename)) {
            abort(404);
        }

        return $disk->response($filename);
    }
}
