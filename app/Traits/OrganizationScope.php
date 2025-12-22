<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

/**
 * Trait OrganizationScope
 *
 * Provides organization-based filtering for Eloquent models.
 * Use this trait on models that have an `organization_id` column
 * to easily filter queries based on the current user's accessible organizations.
 */
trait OrganizationScope
{
    /**
     * Scope query to user's accessible organizations.
     *
     * This scope filters records based on the authenticated user's organization access:
     * - Admin users: No filter applied (see all records)
     * - Sub-Admin users: Filter by their assigned organizations from pivot table
     * - Organization Manager users: Filter by their single organization_id
     * - Other users: Filter by their organization_id if set
     *
     * @param Builder $query
     * @param \App\User|null $user Optional user to filter for (defaults to authenticated user)
     * @return Builder
     */
    public function scopeForUserOrganizations(Builder $query, $user = null): Builder
    {
        $user = $user ?? auth()->user();

        // No user = no access
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // Admin has full access
        if ($user->hasRole('admin')) {
            return $query;
        }

        // Get user's accessible organization IDs
        $orgIds = $user->organizations_ids;

        // No organizations = no access
        if (empty($orgIds)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('organization_id', $orgIds);
    }

    /**
     * Scope query to a specific organization with access check.
     *
     * Filters records to a specific organization, but only if the
     * authenticated user has access to that organization.
     *
     * @param Builder $query
     * @param int $organizationId
     * @param \App\User|null $user Optional user to check access for
     * @return Builder
     */
    public function scopeForOrganization(Builder $query, int $organizationId, $user = null): Builder
    {
        $user = $user ?? auth()->user();

        // No user = no access
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        // Check if user can access this organization
        if (!$user->canAccessOrganization($organizationId)) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('organization_id', $organizationId);
    }
}
