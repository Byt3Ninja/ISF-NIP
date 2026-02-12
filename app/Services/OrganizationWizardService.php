<?php

namespace App\Services;

use App\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class OrganizationWizardService
{
    /**
     * Create a new organization from Step 1 data.
     *
     * @param array $data Validated data from OrganizationStep1Request
     * @return array ['success' => bool, 'data' => Organization|null, 'message' => string]
     */
    public function create(array $data): array
    {
        DB::beginTransaction();

        try {
            $organization = new Organization();
            $organization->name_en = $data['name_en'];
            $organization->name_ar = $data['name_ar'] ?? null;
            $organization->organization_type_id = $data['organization_type_id'];
            $organization->status = $data['status'] ?? true;
            $organization->bio = $data['bio'] ?? null;
            $organization->is_draft = true;
            $organization->wizard_step = 2;

            // Handle image upload
            if (isset($data['img_url']) && $data['img_url'] instanceof \Illuminate\Http\UploadedFile) {
                $organization->img_url = $this->handleLogoUpload($organization, $data['img_url']);
            }

            $organization->save();

            // Sync industries
            if (!empty($data['industries']) && is_array($data['industries'])) {
                $organization->industries()->sync($data['industries']);
            }

            DB::commit();

            return [
                'success' => true,
                'data' => $organization,
                'message' => __('admin.Organization basics saved successfully'),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Error saving organization') . ': ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update Step 1 data for an existing organization.
     *
     * @param int $orgId Organization ID
     * @param array $data Validated data from OrganizationStep1Request
     * @return array ['success' => bool, 'data' => Organization|null, 'message' => string]
     */
    public function updateStep1(int $orgId, array $data): array
    {
        $organization = Organization::find($orgId);

        if (!$organization) {
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Organization not found'),
            ];
        }

        // Check if organization is published and prevent reverting to draft
        if (!$organization->is_draft && isset($data['revert_to_draft'])) {
            return [
                'success' => false,
                'data' => $organization,
                'message' => __('admin.Published organizations cannot be reverted to draft'),
            ];
        }

        DB::beginTransaction();

        try {
            $organization->name_en = $data['name_en'];
            $organization->name_ar = $data['name_ar'] ?? $organization->name_ar;
            $organization->organization_type_id = $data['organization_type_id'];
            $organization->status = $data['status'] ?? $organization->status;
            $organization->bio = $data['bio'] ?? $organization->bio;

            // Update wizard step if moving forward
            if ($organization->wizard_step < 2) {
                $organization->wizard_step = 2;
            }

            // Handle image upload
            if (isset($data['img_url']) && $data['img_url'] instanceof \Illuminate\Http\UploadedFile) {
                $organization->img_url = $this->handleLogoUpload($organization, $data['img_url']);
            }

            $organization->save();

            // Sync industries
            if (isset($data['industries'])) {
                $organization->industries()->sync($data['industries'] ?? []);
            }

            DB::commit();

            return [
                'success' => true,
                'data' => $organization,
                'message' => __('admin.Organization basics updated successfully'),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Error updating organization') . ': ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update Step 2 data (Contact Information & Social Media).
     *
     * @param int $orgId Organization ID
     * @param array $data Validated data from OrganizationStep2Request
     * @return array ['success' => bool, 'data' => Organization|null, 'message' => string]
     */
    public function updateStep2(int $orgId, array $data): array
    {
        $organization = Organization::find($orgId);

        if (!$organization) {
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Organization not found'),
            ];
        }

        DB::beginTransaction();

        try {
            // Contact Information
            $organization->contact_email = $data['contact_email'] ?? null;
            $organization->country_code = $data['country_code'] ?? null;
            $organization->contact_phone = $data['contact_phone'] ?? null;
            $organization->country_id = $data['country_id'] ?? null;
            $organization->state_id = $data['state_id'] ?? null;
            $organization->city_id = $data['city_id'] ?? null;
            $organization->address = $data['address'] ?? null;
            $organization->google_map_url = $data['google_map_url'] ?? null;

            // Social Media
            $organization->linkedin_company_url = $data['linkedin_company_url'] ?? null;
            $organization->twitter_handle = $data['twitter_handle'] ?? null;
            $organization->facebook_page_url = $data['facebook_page_url'] ?? null;
            $organization->instagram_profile_url = $data['instagram_profile_url'] ?? null;
            $organization->youtube_channel_url = $data['youtube_channel_url'] ?? null;
            $organization->tiktok_url = $data['tiktok_url'] ?? null;

            // Update wizard step if moving forward
            if ($organization->wizard_step < 3) {
                $organization->wizard_step = 3;
            }

            $organization->save();

            DB::commit();

            return [
                'success' => true,
                'data' => $organization,
                'message' => __('admin.Contact information saved successfully'),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Error saving contact information') . ': ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Save organization as draft.
     *
     * @param int|null $orgId Organization ID (null for new)
     * @param array $data Partial data
     * @param int $currentStep Current wizard step
     * @return array ['success' => bool, 'data' => Organization|null, 'message' => string]
     */
    public function saveDraft(?int $orgId, array $data, int $currentStep = 1): array
    {
        DB::beginTransaction();

        try {
            if ($orgId) {
                $organization = Organization::find($orgId);

                if (!$organization) {
                    return [
                        'success' => false,
                        'data' => null,
                        'message' => __('admin.Organization not found'),
                    ];
                }

                // Prevent saving as draft if already published
                if (!$organization->is_draft) {
                    return [
                        'success' => false,
                        'data' => $organization,
                        'message' => __('admin.Published organizations cannot be saved as draft'),
                    ];
                }
            } else {
                $organization = new Organization();
                $organization->is_draft = true;
                $organization->wizard_step = 1;
            }

            // Step 1 fields - auto-fill missing name from the other language
            $nameEn = $data['name_en'] ?? null;
            $nameAr = $data['name_ar'] ?? null;
            if (!empty($nameEn) && empty($nameAr)) {
                $nameAr = $nameEn;
            } elseif (!empty($nameAr) && empty($nameEn)) {
                $nameEn = $nameAr;
            }
            if (!empty($nameEn)) {
                $organization->name_en = $nameEn;
            }
            if (!empty($nameAr)) {
                $organization->name_ar = $nameAr;
            }
            if (!empty($data['organization_type_id'])) {
                $organization->organization_type_id = $data['organization_type_id'];
            }
            if (isset($data['status'])) {
                $organization->status = $data['status'];
            }
            if (!empty($data['bio'])) {
                $organization->bio = $data['bio'];
            }

            // Step 2: Contact Information
            if (!empty($data['contact_email'])) {
                $organization->contact_email = $data['contact_email'];
            }
            if (!empty($data['country_code'])) {
                $organization->country_code = $data['country_code'];
            }
            if (!empty($data['contact_phone'])) {
                $organization->contact_phone = $data['contact_phone'];
            }
            if (!empty($data['country_id'])) {
                $organization->country_id = $data['country_id'];
            }
            if (!empty($data['state_id'])) {
                $organization->state_id = $data['state_id'];
            }
            if (!empty($data['city_id'])) {
                $organization->city_id = $data['city_id'];
            }
            if (!empty($data['address'])) {
                $organization->address = $data['address'];
            }
            if (!empty($data['google_map_url'])) {
                $organization->google_map_url = $data['google_map_url'];
            }

            // Step 2: Social Media
            if (isset($data['linkedin_company_url'])) {
                $organization->linkedin_company_url = $data['linkedin_company_url'];
            }
            if (isset($data['twitter_handle'])) {
                $organization->twitter_handle = $data['twitter_handle'];
            }
            if (isset($data['facebook_page_url'])) {
                $organization->facebook_page_url = $data['facebook_page_url'];
            }
            if (isset($data['instagram_profile_url'])) {
                $organization->instagram_profile_url = $data['instagram_profile_url'];
            }
            if (isset($data['youtube_channel_url'])) {
                $organization->youtube_channel_url = $data['youtube_channel_url'];
            }
            if (isset($data['tiktok_url'])) {
                $organization->tiktok_url = $data['tiktok_url'];
            }

            // Handle image upload
            if (isset($data['img_url']) && $data['img_url'] instanceof \Illuminate\Http\UploadedFile) {
                $organization->img_url = $this->handleLogoUpload($organization, $data['img_url']);
            }

            // Update wizard_step based on current step (max 3 steps in wizard)
            if ($currentStep >= $organization->wizard_step) {
                $organization->wizard_step = min($currentStep + 1, 3);
            }

            $organization->save();

            // Sync industries if provided
            if (isset($data['industries'])) {
                $organization->industries()->sync($data['industries'] ?? []);
            }

            DB::commit();

            return [
                'success' => true,
                'data' => $organization,
                'message' => __('admin.Organization saved as draft successfully'),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Error saving draft') . ': ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Publish a draft organization.
     *
     * @param int $orgId Organization ID
     * @return array ['success' => bool, 'data' => Organization|null, 'message' => string]
     */
    public function publish(int $orgId): array
    {
        $organization = Organization::find($orgId);

        if (!$organization) {
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Organization not found'),
            ];
        }

        // Check if already published
        if (!$organization->is_draft) {
            return [
                'success' => false,
                'data' => $organization,
                'message' => __('admin.Organization is already published'),
            ];
        }

        // Check if has at least one manager
        if ($organization->assignedUsers()->count() === 0) {
            return [
                'success' => false,
                'data' => $organization,
                'message' => __('admin.Organization must have at least one manager to publish'),
            ];
        }

        $organization->is_draft = false;
        $organization->save();

        return [
            'success' => true,
            'data' => $organization,
            'message' => __('admin.Organization published successfully'),
        ];
    }

    /**
     * Generate a unique slug for an organization.
     *
     * @param int $orgId Organization ID
     * @param string|null $name Optional name to use for slug generation
     * @return array ['success' => bool, 'data' => string|null, 'message' => string]
     */
    public function generateSlug(int $orgId, ?string $name = null): array
    {
        $organization = Organization::find($orgId);

        if (!$organization && !$name) {
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Name is required'),
            ];
        }

        $baseName = $name ?? $organization->name_en;
        $slug = Organization::generateSlug($baseName, $orgId);

        return [
            'success' => true,
            'data' => $slug,
            'message' => __('admin.Slug generated successfully'),
        ];
    }

    /**
     * Check if a slug is available.
     *
     * @param string $slug Slug to check
     * @param int|null $excludeId Organization ID to exclude from check
     * @return array ['success' => bool, 'data' => bool (available), 'message' => string]
     */
    public function checkSlugAvailability(string $slug, ?int $excludeId = null): array
    {
        if (empty($slug)) {
            return [
                'success' => false,
                'data' => false,
                'message' => __('admin.Please enter a URL slug'),
            ];
        }

        // Validate slug format
        if (!preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            return [
                'success' => false,
                'data' => false,
                'message' => __('admin.URL must contain only lowercase letters, numbers, and hyphens'),
            ];
        }

        // Check if slug exists
        $query = Organization::where('slug', $slug);
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        $exists = $query->exists();

        return [
            'success' => true,
            'data' => !$exists,
            'message' => $exists
                ? __('admin.This URL is already in use')
                : __('admin.This URL is available'),
        ];
    }

    /**
     * Handle logo upload for organization.
     *
     * @param Organization $organization
     * @param \Illuminate\Http\UploadedFile $file
     * @return string Filename
     */
    protected function handleLogoUpload(Organization $organization, $file): string
    {
        // Delete old image if exists
        if ($organization->img_url && Storage::disk('organizations')->exists($organization->img_url)) {
            Storage::disk('organizations')->delete($organization->img_url);
        }

        $imageName = time() . '.' . $file->getClientOriginalExtension();
        Storage::disk('organizations')->put($imageName, file_get_contents($file));

        return $imageName;
    }
}
