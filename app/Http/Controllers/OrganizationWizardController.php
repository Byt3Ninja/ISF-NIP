<?php

namespace App\Http\Controllers;

use App\Organization;
use App\OrganizationTypes;
use App\StaticCategory;
use App\Http\Requests\OrganizationStep1Request;
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

        return view('admin.organizations.wizard.create', compact('organizationTypes', 'industries'));
    }

    /**
     * Show the wizard form for editing an existing organization.
     */
    public function edit($id)
    {
        $organization = Organization::with(['organizationType', 'industries'])->findOrFail($id);
        $organizationTypes = OrganizationTypes::all();
        $industries = StaticCategory::all();

        return view('admin.organizations.wizard.create', compact('organization', 'organizationTypes', 'industries'));
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

            return redirect()
                ->route('organization.wizard.edit', $organization->id)
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

            return redirect()
                ->route('organization.wizard.edit', $organization->id)
                ->with('success', __('admin.Organization basics updated successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', __('admin.Error updating organization') . ': ' . $e->getMessage());
        }
    }

    /**
     * Save organization as draft (no validation required for mandatory fields).
     */
    public function saveDraft(Request $request, $id = null)
    {
        // Minimal validation for draft - only validate if values are provided
        $rules = [
            'name_en' => 'nullable|string|max:255',
            'name_ar' => 'nullable|string|max:255',
            'organization_type_id' => 'nullable|exists:organization_types,id',
            'status' => 'nullable|boolean',
            'bio' => 'nullable|string|max:1000',
            'industries' => 'nullable|array',
            'industries.*' => 'exists:static_categories,id',
            'img_url' => 'nullable|image|mimes:png,jpg,jpeg|max:5120',
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

            // Only update fields that are provided
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

            // Sync industries if provided
            if ($request->has('industries')) {
                $organization->industries()->sync($request->industries ?? []);
            }

            DB::commit();

            return redirect()
                ->route('organization.wizard.edit', $organization->id)
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
