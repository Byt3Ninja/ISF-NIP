<?php

namespace App\Services;

use App\Cycle;
use App\CycleBenefit;
use Illuminate\Support\Facades\DB;

class ProgramLandingService
{
    /**
     * Get programs for landing page display.
     */
    public function getLandingPrograms(int $orgId, string $orderBy = 'newest'): \Illuminate\Database\Eloquent\Collection
    {
        $query = Cycle::where('organization_id', $orgId)
            ->landingVisible()
            ->whereNull('deleted_at')
            ->with(['focusAreas', 'benefits', 'organization']);

        switch ($orderBy) {
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'custom':
                $query->orderBy('landing_sort_order', 'asc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
                break;
        }

        return $query->get();
    }

    /**
     * Update program landing visibility and data.
     */
    public function updateProgramLanding(int $cycleId, array $data): array
    {
        $cycle = Cycle::find($cycleId);
        if (!$cycle) {
            return ['success' => false, 'message' => __('admin.Program not found')];
        }

        DB::beginTransaction();
        try {
            $cycle->landing_visible = $data['landing_visible'] ?? false;
            $cycle->short_description = $data['short_description'] ?? null;
            $cycle->location = $data['location'] ?? null;
            $cycle->landing_sort_order = $data['landing_sort_order'] ?? null;

            // Update public visibility
            if (array_key_exists('is_public', $data)) {
                $cycle->is_public = !empty($data['is_public']) ? 1 : 0;
            }

            // Update public slug
            if (array_key_exists('public_slug', $data)) {
                $newSlug = trim($data['public_slug'] ?? '');
                if ($cycle->is_public && empty($newSlug)) {
                    DB::rollBack();
                    return ['success' => false, 'message' => __('cycles.Public URL required')];
                }
                if (!empty($newSlug) && $newSlug !== $cycle->public_slug) {
                    $slug = url_slugify($newSlug);
                    $exists = Cycle::where('public_slug', $slug)->where('id', '!=', $cycle->id)->exists();
                    if ($exists) {
                        $counter = 1;
                        do {
                            $slug = url_slugify($newSlug) . '-' . $counter++;
                            $exists = Cycle::where('public_slug', $slug)->where('id', '!=', $cycle->id)->exists();
                        } while ($exists);
                    }
                    $cycle->public_slug = $slug;
                } elseif (empty($newSlug)) {
                    $cycle->public_slug = null;
                }
            }

            $cycle->save();

            // Sync focus areas
            if (isset($data['focus_area_ids'])) {
                $cycle->focusAreas()->sync($data['focus_area_ids']);
            }

            // Sync benefits (delete + recreate)
            if (isset($data['benefits'])) {
                $cycle->benefits()->delete();
                foreach ($data['benefits'] as $index => $benefit) {
                    if (!empty($benefit['label_en'])) {
                        CycleBenefit::create([
                            'cycle_id' => $cycle->id,
                            'label_en' => $benefit['label_en'],
                            'label_ar' => $benefit['label_ar'] ?? null,
                            'icon' => $benefit['icon'] ?? null,
                            'sort_order' => $index,
                        ]);
                    }
                }
            }

            DB::commit();
            return ['success' => true, 'data' => $cycle, 'message' => __('admin.Program updated successfully')];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Reorder programs for landing page.
     */
    public function reorderPrograms(int $orgId, array $orderedIds): array
    {
        DB::beginTransaction();
        try {
            foreach ($orderedIds as $index => $cycleId) {
                Cycle::where('id', $cycleId)
                    ->where('organization_id', $orgId)
                    ->update(['landing_sort_order' => $index]);
            }
            DB::commit();
            return ['success' => true, 'message' => __('admin.Programs reordered successfully')];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
