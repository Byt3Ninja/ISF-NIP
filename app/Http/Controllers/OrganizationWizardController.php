<?php

namespace App\Http\Controllers;

use App\Organization;
use App\OrganizationTypes;
use App\StaticCategory;
use App\Country;
use App\State;
use App\City;
use App\Http\Requests\OrganizationStep1Request;
use App\Http\Requests\OrganizationStep2Request;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        $organization = Organization::with(['organizationType', 'industries', 'country', 'state', 'city'])->findOrFail($id);
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

        return view('admin.organizations.wizard.create', compact(
            'organization',
            'organizationTypes',
            'industries',
            'countries',
            'states',
            'cities'
        ));
    }

    /**
     * Store step 1 data for a new organization.
     */
    public function storeStep1(OrganizationStep1Request $request)
    {
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
     * Save organization as draft (no validation required for mandatory fields).
     */
    public function saveDraft(Request $request, $id = null)
    {
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
