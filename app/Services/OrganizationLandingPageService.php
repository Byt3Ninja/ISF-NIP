<?php

namespace App\Services;

use App\Cycle;
use App\Organization;
use App\OrganizationLandingPage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrganizationLandingPageService
{
    /**
     * Update landing page for an organization.
     *
     * @param int $orgId Organization ID
     * @param array $data Validated data from OrganizationStep4Request
     * @param array $files Uploaded files array
     * @return array ['success' => bool, 'data' => Organization|null, 'message' => string]
     */
    public function update(int $orgId, array $data, array $files = []): array
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
            // Update or generate slug
            if (!empty($data['slug'])) {
                $organization->slug = $data['slug'];
            } elseif (!$organization->slug) {
                $organization->slug = Organization::generateSlug($organization->name_en, $organization->id);
            }

            $organization->save();
            
            // Get or create landing page
            $landingPage = $organization->landingPage ?? new OrganizationLandingPage();
            $landingPage->organization_id = $organization->id;

            $sectionType = $data['section_type'] ?? null;

            // Landing Page Enabled
            $landingPage->landing_enabled = $data['landing_enabled'] ?? true;

            // How It Works Section
            if (!$sectionType || $sectionType === 'howItWorks') {
                $landingPage->how_it_works_enabled = $data['how_it_works_enabled'] ?? false;
                $landingPage->how_it_works_title = $data['how_it_works_title'] ?? null;
                $landingPage->how_it_works_subtitle = $data['how_it_works_subtitle'] ?? null;
                $landingPage->how_it_works_description = $data['how_it_works_description'] ?? null;
                $landingPage->process_steps = $this->processProcessSteps($data['process_steps'] ?? []);
            }

            // Achievements Section
            if (!$sectionType || $sectionType === 'achievements') {
                $landingPage->achievements_enabled = $data['achievements_enabled'] ?? false;
                $landingPage->achievements_title = $data['achievements_title'] ?? null;
                $landingPage->achievements_subtitle = $data['achievements_subtitle'] ?? null;
                $landingPage->achievements_description = $data['achievements_description'] ?? null;
                $landingPage->metrics = $this->processMetrics($data['metrics'] ?? []);
            }

            // Media & Recognition Section
            if (!$sectionType || $sectionType === 'media') {
                $landingPage->media_enabled = $data['media_enabled'] ?? false;
                $landingPage->media_title = $data['media_title'] ?? null;
                $landingPage->media_subtitle = $data['media_subtitle'] ?? null;
                $landingPage->media_description = $data['media_description'] ?? null;
                $landingPage->articles = $this->processArticles(
                    $organization->id,
                    $data['articles'] ?? [],
                    $files['articles'] ?? [],
                    $landingPage->articles ?? []
                );
            }

            // Partners Section
            if (!$sectionType || $sectionType === 'partners') {
                $landingPage->partners_enabled = $data['partners_enabled'] ?? false;
                $landingPage->partners_title = $data['partners_title'] ?? null;
                $landingPage->partners_subtitle = $data['partners_subtitle'] ?? null;
                $landingPage->partners_description = $data['partners_description'] ?? null;
                $landingPage->partners_list = $this->processPartners(
                    $organization->id,
                    $data['partners_list'] ?? [],
                    $files['partners_list'] ?? [],
                    $landingPage->partners_list ?? []
                );
            }

            // Programs Section
            if (!$sectionType || $sectionType === 'programs') {
                $landingPage->programs_enabled = $data['programs_enabled'] ?? false;
                $landingPage->programs_title = $data['programs_title'] ?? null;
                $landingPage->programs_subtitle = $data['programs_subtitle'] ?? null;
                $landingPage->programs_description = $data['programs_description'] ?? null;
                $landingPage->programs_order_by = $data['programs_order_by'] ?? 'newest';

                // Bulk-update program landing visibility
                // Use sectionType check so that unchecked-all (key absent) still resets visibility
                if ($sectionType === 'programs' || isset($data['programs_visibility'])) {
                    $visibleIds = array_values(array_filter($data['programs_visibility'] ?? []));
                    
                    Cycle::where('organization_id', $organization->id)
                        ->whereNull('deleted_at')
                        ->update(['landing_visible' => false]);

                    if (!empty($visibleIds)) {
                        Cycle::where('organization_id', $organization->id)
                            ->whereNull('deleted_at')
                            ->whereIn('id', $visibleIds)
                            ->update(['landing_visible' => true]);
                    }
                }
            }

            // Theme
            $landingPage->landing_theme = $data['landing_theme'] ?? 'gradient';

            // Section Background Colors
            $this->processSectionBackgrounds($landingPage, $data, $sectionType);

            $landingPage->save();

            DB::commit();

            return [
                'success' => true,
                'data' => $organization->load('landingPage'),
                'message' => __('admin.Landing page saved successfully'),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Error saving landing page') . ': ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Update organization slug.
     *
     * @param int $orgId Organization ID
     * @param string $slug New slug
     * @return array ['success' => bool, 'data' => Organization|null, 'message' => string]
     */
    public function updateSlug(int $orgId, string $slug): array
    {
        $organization = Organization::find($orgId);

        if (!$organization) {
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Organization not found'),
            ];
        }

        // Check if slug is already taken
        $exists = Organization::where('slug', $slug)
            ->where('id', '!=', $orgId)
            ->exists();

        if ($exists) {
            return [
                'success' => false,
                'data' => $organization,
                'message' => __('admin.This URL is already in use'),
            ];
        }

        $organization->slug = $slug;
        $organization->save();

        return [
            'success' => true,
            'data' => $organization,
            'message' => __('admin.Slug updated successfully'),
        ];
    }

    /**
     * Get preview URL for landing page.
     *
     * @param int $orgId Organization ID
     * @return array ['success' => bool, 'data' => string|null, 'message' => string]
     */
    public function getPreviewUrl(int $orgId): array
    {
        $organization = Organization::with('landingPage')->find($orgId);

        if (!$organization) {
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Organization not found'),
            ];
        }

        // Generate slug if not exists
        if (!$organization->slug) {
            $organization->slug = Organization::generateSlug($organization->name_en, $organization->id);
            $organization->save();
        }

        $token = md5('preview-org-' . $organization->id . '-gl');
        $url = '/o/' . $organization->slug . '?preview=' . $token;

        return [
            'success' => true,
            'data' => $url,
            'message' => '',
        ];
    }

    /**
     * Delete an asset from landing page.
     *
     * @param int $orgId Organization ID
     * @param string $type Asset type ('articles' or 'partners')
     * @param string $filename Filename to delete
     * @return array ['success' => bool, 'data' => null, 'message' => string]
     */
    public function deleteAsset(int $orgId, string $type, string $filename): array
    {
        $validTypes = ['articles', 'partners'];

        if (!in_array($type, $validTypes)) {
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Invalid asset type'),
            ];
        }

        $disk = Storage::disk('organization_landing');
        $path = "{$orgId}/{$type}/{$filename}";

        if (!$disk->exists($path)) {
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.File not found'),
            ];
        }

        $disk->delete($path);

        return [
            'success' => true,
            'data' => null,
            'message' => __('admin.Asset deleted successfully'),
        ];
    }

    /**
     * Process process steps data.
     *
     * @param array $steps
     * @return array
     */
    protected function processProcessSteps(array $steps): array
    {
        $validSteps = [];

        foreach ($steps as $step) {
            // Only include if at least one field is filled
            if (!empty($step['title']) || !empty($step['description']) || !empty($step['icon'])) {
                $validSteps[] = [
                    'title' => $step['title'] ?? '',
                    'description' => $step['description'] ?? '',
                    'icon' => $step['icon'] ?? '',
                ];
            }
        }

        return array_slice($validSteps, 0, 4);
    }

    /**
     * Process metrics data.
     *
     * @param array $metrics
     * @return array
     */
    protected function processMetrics(array $metrics): array
    {
        $validMetrics = [];

        foreach ($metrics as $metric) {
            // Only include if at least one field is filled
            if (!empty($metric['number']) || !empty($metric['label']) || !empty($metric['icon'])) {
                $validMetrics[] = [
                    'number' => $metric['number'] ?? '',
                    'label' => $metric['label'] ?? '',
                    'icon' => $metric['icon'] ?? '',
                ];
            }
        }

        return array_slice($validMetrics, 0, 8);
    }

    /**
     * Process articles with file uploads.
     *
     * @param int $organizationId
     * @param array $articles Articles data
     * @param array $files Uploaded files
     * @param array $existingArticles Existing articles data
     * @return array
     */
    protected function processArticles(int $organizationId, array $articles, array $files, array $existingArticles): array
    {
        $processedArticles = [];

        foreach ($articles as $index => $article) {
            // Only include if at least one field is filled (excluding image)
            if (empty($article['title']) && empty($article['publication_name']) && empty($article['link'])) {
                continue;
            }

            $existingImage = $article['existing_image'] ?? ($existingArticles[$index]['image'] ?? null);

            $processedArticle = [
                'publication_name' => $article['publication_name'] ?? '',
                'date' => $article['date'] ?? null,
                'title' => $article['title'] ?? '',
                'summary' => $article['summary'] ?? '',
                'link' => $article['link'] ?? '',
                'image' => $existingImage,
            ];

            // Handle new image upload
            if (isset($files[$index]['image']) && $files[$index]['image'] instanceof \Illuminate\Http\UploadedFile) {
                // Validate the replacement first (image whitelist + real content type): a rejected
                // upload must never cost the existing image, and the extension is never the client's.
                $extension = app(FileManagerService::class)->assertAllowed($files[$index]['image'], 'image');

                // Delete old image
                if ($processedArticle['image']) {
                    Storage::disk('organization_landing')->delete("{$organizationId}/articles/{$processedArticle['image']}");
                }

                $file = $files[$index]['image'];
                $filename = time() . '_' . $index . '.' . $extension;
                Storage::disk('organization_landing')->put("{$organizationId}/articles/{$filename}", file_get_contents($file));
                $processedArticle['image'] = $filename;
            }

            $processedArticles[] = $processedArticle;
        }

        return array_slice($processedArticles, 0, 8);
    }

    /**
     * Process partners with logo uploads.
     *
     * @param int $organizationId
     * @param array $partners Partners data
     * @param array $files Uploaded files
     * @param array $existingPartners Existing partners data
     * @return array
     */
    protected function processPartners(int $organizationId, array $partners, array $files, array $existingPartners): array
    {
        $processedPartners = [];

        foreach ($partners as $index => $partner) {
            $existingLogo = $partner['existing_logo'] ?? ($existingPartners[$index]['logo'] ?? null);

            $processedPartner = [
                'name' => $partner['name'] ?? '',
                'logo' => $existingLogo,
            ];

            // Handle new logo upload
            if (isset($files[$index]['logo']) && $files[$index]['logo'] instanceof \Illuminate\Http\UploadedFile) {
                // Validate the replacement first (see the article image above).
                $extension = app(FileManagerService::class)->assertAllowed($files[$index]['logo'], 'image');

                // Delete old logo
                if ($processedPartner['logo']) {
                    Storage::disk('organization_landing')->delete("{$organizationId}/partners/{$processedPartner['logo']}");
                }

                $file = $files[$index]['logo'];
                $filename = time() . '_' . $index . '.' . $extension;
                Storage::disk('organization_landing')->put("{$organizationId}/partners/{$filename}", file_get_contents($file));
                $processedPartner['logo'] = $filename;
            }

            // Only add if has logo (logo is required for partners)
            if ($processedPartner['logo'] || (isset($files[$index]['logo']) && $files[$index]['logo'] instanceof \Illuminate\Http\UploadedFile)) {
                $processedPartners[] = $processedPartner;
            }
        }

        return array_slice($processedPartners, 0, 20);
    }

    /**
     * Process section background colors.
     *
     * @param OrganizationLandingPage $landingPage
     * @param array $data
     * @return void
     */
    protected function processSectionBackgrounds(OrganizationLandingPage $landingPage, array $data, ?string $sectionType = null): void
    {
        // Hero (always updated — no section_type guard)
        $landingPage->hero_bg_type = $data['hero_bg_type'] ?? 'solid';
        $landingPage->hero_bg_color = $data['hero_bg_color'] ?? null;
        $landingPage->hero_bg_gradient_start = $data['hero_bg_gradient_start'] ?? null;
        $landingPage->hero_bg_gradient_end = $data['hero_bg_gradient_end'] ?? null;

        // How It Works
        if (!$sectionType || $sectionType === 'howItWorks') {
            $landingPage->how_it_works_bg_type = $data['how_it_works_bg_type'] ?? 'solid';
            $landingPage->how_it_works_bg_color = $data['how_it_works_bg_color'] ?? null;
            $landingPage->how_it_works_bg_gradient_start = $data['how_it_works_bg_gradient_start'] ?? null;
            $landingPage->how_it_works_bg_gradient_end = $data['how_it_works_bg_gradient_end'] ?? null;
        }

        // Achievements
        if (!$sectionType || $sectionType === 'achievements') {
            $landingPage->achievements_bg_type = $data['achievements_bg_type'] ?? 'solid';
            $landingPage->achievements_bg_color = $data['achievements_bg_color'] ?? null;
            $landingPage->achievements_bg_gradient_start = $data['achievements_bg_gradient_start'] ?? null;
            $landingPage->achievements_bg_gradient_end = $data['achievements_bg_gradient_end'] ?? null;
        }

        // Media
        if (!$sectionType || $sectionType === 'media') {
            $landingPage->media_bg_type = $data['media_bg_type'] ?? 'solid';
            $landingPage->media_bg_color = $data['media_bg_color'] ?? null;
            $landingPage->media_bg_gradient_start = $data['media_bg_gradient_start'] ?? null;
            $landingPage->media_bg_gradient_end = $data['media_bg_gradient_end'] ?? null;
        }

        // Partners
        if (!$sectionType || $sectionType === 'partners') {
            $landingPage->partners_bg_type = $data['partners_bg_type'] ?? 'solid';
            $landingPage->partners_bg_color = $data['partners_bg_color'] ?? null;
            $landingPage->partners_bg_gradient_start = $data['partners_bg_gradient_start'] ?? null;
            $landingPage->partners_bg_gradient_end = $data['partners_bg_gradient_end'] ?? null;
        }

        // Programs
        if (!$sectionType || $sectionType === 'programs') {
            $landingPage->programs_bg_type = $data['programs_bg_type'] ?? 'solid';
            $landingPage->programs_bg_color = $data['programs_bg_color'] ?? null;
            $landingPage->programs_bg_gradient_start = $data['programs_bg_gradient_start'] ?? null;
            $landingPage->programs_bg_gradient_end = $data['programs_bg_gradient_end'] ?? null;
        }
    }
}
