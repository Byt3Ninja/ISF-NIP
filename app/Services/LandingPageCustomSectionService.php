<?php

namespace App\Services;

use App\LandingPage;
use App\LandingPageCustomSection;
use Illuminate\Support\Facades\DB;

class LandingPageCustomSectionService
{
    /**
     * Sync custom sections for a landing page: creates new, updates
     * existing, deletes anything no longer present in $sections.
     *
     * @param int $landingPageId
     * @param array $sections
     * @return array ['success' => bool, 'message' => string]
     */
    public function update(int $landingPageId, array $sections): array
    {
        $landingPage = LandingPage::find($landingPageId);

        if (!$landingPage) {
            return [
                'success' => false,
                'message' => __('admin.Landing page not found'),
            ];
        }

        DB::beginTransaction();

        try {
            $existingIds = $landingPage->customSections()->pluck('id')->toArray();
            $keepIds = [];

            foreach ($sections as $index => $sectionData) {
                // Skip a fully empty block.
                if (
                    empty($sectionData['title']) &&
                    empty($sectionData['title_ar']) &&
                    empty($sectionData['title_tag']) &&
                    empty($sectionData['title_tag_ar']) &&
                    empty($sectionData['content']) &&
                    empty($sectionData['content_ar'])
                ) {
                    continue;
                }

                $sectionId = $sectionData['id'] ?? null;

                $data = [
                    'landing_page_id' => $landingPageId,
                    'title' => $sectionData['title'] ?? null,
                    'title_ar' => $sectionData['title_ar'] ?? null,
                    'title_tag' => $sectionData['title_tag'] ?? null,
                    'title_tag_ar' => $sectionData['title_tag_ar'] ?? null,
                    'content' => isset($sectionData['content']) ? HtmlSanitizer::clean($sectionData['content']) : null,
                    'content_ar' => isset($sectionData['content_ar']) ? HtmlSanitizer::clean($sectionData['content_ar']) : null,
                    'visibility' => !empty($sectionData['visibility']),
                    // 'button_text' => $sectionData['button_text'] ?? null,
                    // 'button_link' => $sectionData['button_link'] ?? null,
                    'button_text' => null,
                    'button_link' => null,
                    'sort_order' => $index,
                ];

                if ($sectionId && in_array($sectionId, $existingIds)) {
                    LandingPageCustomSection::where('id', $sectionId)->update($data);
                    $keepIds[] = $sectionId;
                } else {
                    $newSection = LandingPageCustomSection::create($data);
                    $keepIds[] = $newSection->id;
                }
            }

            $toDelete = array_diff($existingIds, $keepIds);
            if (!empty($toDelete)) {
                LandingPageCustomSection::whereIn('id', $toDelete)->delete();
            }

            DB::commit();

            return [
                'success' => true,
                'message' => __('admin.Custom section saved successfully'),
            ];
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'success' => false,
                'message' => __('admin.Error saving custom sections') . ': ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Delete a single custom section belonging to the given landing page.
     *
     * @param int $landingPageId
     * @param int $sectionId
     * @return array ['success' => bool, 'message' => string]
     */
    public function delete(int $landingPageId, int $sectionId): array
    {
        $section = LandingPageCustomSection::where('landing_page_id', $landingPageId)
            ->where('id', $sectionId)
            ->first();

        if (!$section) {
            return [
                'success' => false,
                'message' => __('landing.error.custom_delete_failed'),
            ];
        }

        try {
            $section->delete();

            return [
                'success' => true,
                'message' => __('landing.success.custom_deleted'),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => __('landing.error.custom_delete_failed'),
            ];
        }
    }
}
