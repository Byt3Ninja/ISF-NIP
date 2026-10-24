<?php

namespace App\Services;

use App\Organization;
use App\OrganizationCustomSection;
use App\User;
use Illuminate\Support\Facades\DB;

class OrganizationCustomSectionService
{
    /**
     * Update custom sections for an organization.
     * Syncs sections: deletes removed, updates existing, creates new.
     *
     * @param int $orgId Organization ID
     * @param array $sections Array of section data
     * @return array ['success' => bool, 'data' => Organization|null, 'message' => string]
     */
    public function update(int $orgId, array $sections): array
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
            // Get existing section IDs
            $existingSectionIds = $organization->customSections()->pluck('id')->toArray();

            // Track which IDs we're keeping
            $keepIds = [];

            // Process each section
            foreach ($sections as $index => $sectionData) {
                // Skip empty sections
                if (empty($sectionData['title']) && empty($sectionData['content'])) {
                    continue;
                }

                $sectionId = $sectionData['id'] ?? null;

                $data = [
                    'organization_id' => $orgId,
                    'title' => $sectionData['title'] ?? '',
                    'content' => HtmlSanitizer::clean($sectionData['content'] ?? ''),
                    'visibility' => $sectionData['visibility'] ?? 'public',
                    'bg_type' => $sectionData['bg_type'] ?? 'solid',
                    'bg_color' => $sectionData['bg_color'] ?? null,
                    'bg_gradient_start' => $sectionData['bg_gradient_start'] ?? null,
                    'bg_gradient_end' => $sectionData['bg_gradient_end'] ?? null,
                    'sort_order' => $sectionData['sort_order'] ?? $index,
                ];

                if ($sectionId && in_array($sectionId, $existingSectionIds)) {
                    // Update existing section
                    OrganizationCustomSection::where('id', $sectionId)->update($data);
                    $keepIds[] = $sectionId;
                } else {
                    // Create new section
                    $newSection = OrganizationCustomSection::create($data);
                    $keepIds[] = $newSection->id;
                }
            }

            // Delete sections that are no longer in the list
            $toDelete = array_diff($existingSectionIds, $keepIds);
            if (!empty($toDelete)) {
                OrganizationCustomSection::whereIn('id', $toDelete)->delete();
            }

            DB::commit();

            return [
                'success' => true,
                'data' => $organization->load('customSections'),
                'message' => __('admin.Custom sections saved successfully'),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Error saving custom sections') . ': ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a single custom section.
     *
     * @param int $orgId Organization ID
     * @param int $sectionId Section ID
     * @return array ['success' => bool, 'data' => null, 'message' => string]
     */
    public function delete(int $orgId, int $sectionId): array
    {
        $section = OrganizationCustomSection::where('id', $sectionId)
            ->where('organization_id', $orgId)
            ->first();

        if (!$section) {
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Section not found'),
            ];
        }

        $section->delete();

        return [
            'success' => true,
            'data' => null,
            'message' => __('admin.Section deleted successfully'),
        ];
    }

    /**
     * Reorder sections based on new order.
     *
     * @param int $orgId Organization ID
     * @param array $orderedIds Array of section IDs in desired order
     * @return array ['success' => bool, 'data' => null, 'message' => string]
     */
    public function reorder(int $orgId, array $orderedIds): array
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
            foreach ($orderedIds as $index => $sectionId) {
                OrganizationCustomSection::where('id', $sectionId)
                    ->where('organization_id', $orgId)
                    ->update(['sort_order' => $index]);
            }

            DB::commit();

            return [
                'success' => true,
                'data' => null,
                'message' => __('admin.Sections reordered successfully'),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Error reordering sections'),
            ];
        }
    }

    /**
     * Get custom sections filtered by visibility for a specific user.
     *
     * @param int $orgId Organization ID
     * @param User|null $user Current user (null for guests)
     * @return array Array of visible sections
     */
    public function getVisibleSections(int $orgId, ?User $user): array
    {
        $organization = Organization::with('customSections')->find($orgId);

        if (!$organization) {
            return [];
        }

        $sections = $organization->customSections;
        $visibleSections = [];

        foreach ($sections as $section) {
            if ($this->canUserSeeSection($section, $organization, $user)) {
                $visibleSections[] = $section;
            }
        }

        return $visibleSections;
    }

    /**
     * Check if a user can see a specific section based on visibility.
     *
     * @param OrganizationCustomSection $section
     * @param Organization $organization
     * @param User|null $user
     * @return bool
     */
    protected function canUserSeeSection(OrganizationCustomSection $section, Organization $organization, ?User $user): bool
    {
        switch ($section->visibility) {
            case 'public':
                return true;

            case 'participants':
                if (!$user) {
                    return false;
                }
                // Participants can see + Admins can see
                return $organization->isUserParticipant($user) || $this->isAdmin($user);

            case 'admins_only':
                if (!$user) {
                    return false;
                }
                return $this->isAdmin($user);

            default:
                return false;
        }
    }

    /**
     * Check if a user is an admin.
     *
     * @param User $user
     * @return bool
     */
    protected function isAdmin(User $user): bool
    {
        // Check for admin roles (Super Admin, Tenant Admin, Sub Admin)
        return in_array($user->role, ['superadmin', 'tenant_admin', 'sub_admin']);
    }
}
