<?php

namespace App\Services;

use App\Notifications\OrganizationAssignedNotification;
use App\Notifications\OrganizationManagerWelcomeNotification;
use App\Organization;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class OrganizationManagerService
{
    /**
     * Get available managers for assignment.
     *
     * @param int $orgId Organization ID
     * @param array $filters ['search_term' => string, 'start' => int, 'length' => int]
     * @return array ['success' => bool, 'data' => array, 'message' => string]
     */
    public function getAvailableManagers(int $orgId, array $filters = []): array
    {
        $organization = Organization::find($orgId);

        if (!$organization) {
            return [
                'success' => false,
                'data' => [],
                'message' => __('admin.Organization not found'),
            ];
        }

        $locale = app()->getLocale();

        // Get users with organizationmanager or Sub-Admin roles
        $query = User::whereHas('roles', function ($q) {
            $q->whereIn('default_for', ['organizationmanager', 'subadmin']);
        });

        // Search filter
        if (!empty($filters['search_term'])) {
            $searchTerm = $filters['search_term'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('email', 'like', "%{$searchTerm}%")
                    ->orWhere('first_name_en', 'like', "%{$searchTerm}%")
                    ->orWhere('last_name_en', 'like', "%{$searchTerm}%")
                    ->orWhere('first_name_ar', 'like', "%{$searchTerm}%")
                    ->orWhere('last_name_ar', 'like', "%{$searchTerm}%");
            });
        }

        $query = $query->orderBy('created_at', 'desc');
        // Get assigned manager IDs for this organization
        $assignedManagerIds = $organization->assignedUsers()->pluck('users.id')->toArray();

        // Pagination
        $start = $filters['start'] ?? 0;
        $length = $filters['length'] ?? 5;
        $totalRecords = $query->count();

        $managers = $query->skip($start)->take($length)->get();

        $data = $managers->map(function ($user) use ($locale, $assignedManagerIds) {
            $name = $locale === 'ar'
                ? (($user->first_name_ar ?? $user->first_name_en) . ' ' . ($user->last_name_ar ?? $user->last_name_en))
                : ($user->first_name_en . ' ' . $user->last_name_en);

            $roleName = $user->roles->first()->default_for ?? '';
            $roleName = trim(strtolower($roleName));
            $roleDisplay = $roleName === 'organizationmanager'
                ? __('admin.Organization Manager')
                : ($roleName === 'subadmin' ? __('admin.Sub-Admin') : $roleName);

            // Calculate organizations count based on role
            if ($roleName === 'organizationmanager') {
                $orgIds = $user->organizations()->pluck('organizations.id')->toArray();
                if ($user->organization_id) {
                    $orgIds[] = $user->organization_id;
                }
                $organizationsCount = count(array_unique($orgIds));
            } else {
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

        return [
            'success' => true,
            'data' => [
                'records' => $data->toArray(),
                'total' => $totalRecords,
                'filtered' => $totalRecords,
            ],
            'message' => '',
        ];
    }

    /**
     * Assign managers to organization.
     *
     * @param int $orgId Organization ID
     * @param array $managerIds Array of user IDs
     * @return array ['success' => bool, 'data' => Organization|null, 'message' => string]
     */
    public function assignManagers(int $orgId, array $managerIds): array
    {
        $organization = Organization::find($orgId);

        if (!$organization) {
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Organization not found'),
            ];
        }

        if (empty($managerIds)) {
            return [
                'success' => false,
                'data' => $organization,
                'message' => __('admin.Please select or create at least one organization manager'),
            ];
        }

        DB::beginTransaction();

        try {
            $newlyAssignedIds = $this->syncManagersToOrganization($organization, $managerIds);

            // Update wizard step if moving forward (max 3 steps in wizard)
            if ($organization->wizard_step < 3) {
                $organization->wizard_step = 3;
            }

            $organization->save();

            DB::commit();

            // Sent after commit so queued jobs never race uncommitted pivot rows.
            // Only newly attached managers are notified — re-saving this step must
            // not re-notify managers who were already assigned.
            $this->notifyNewlyAssignedManagers($organization, $newlyAssignedIds);

            return [
                'success' => true,
                'data' => $organization,
                'message' => __('admin.Managers saved successfully'),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Error saving managers') . ': ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Create a new manager user.
     *
     * @param int $orgId Organization ID
     * @param array $userData User data
     * @return array ['success' => bool, 'data' => User|null, 'message' => string]
     */
    public function createManager(int $orgId, array $userData): array
    {
        $organization = Organization::find($orgId);

        if (!$organization) {
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Organization not found'),
            ];
        }

        $locale = app()->getLocale();

        DB::beginTransaction();

        try {
            $user = new User();
            $user->first_name_en = $userData['first_name_en'];
            $user->last_name_en = $userData['last_name_en'];
            $user->first_name_ar = $userData['first_name_ar'] ?? null;
            $user->last_name_ar = $userData['last_name_ar'] ?? null;
            $user->gender = $userData['gender'] ?? null;
            $user->email = $userData['email'];
            $user->country_code = $userData['country_code'] ?? null;
            $user->phone = $userData['phone'] ?? null;
            $user->password = Hash::make($userData['password']);
            $user->organization_id = $organization->id;
            $user->email_verified_at = now();
            $user->save();

            // Assign Organization Manager role
            $user->allotRole('organizationmanager');

            // Add to user_organizations pivot table
            $organization->assignedUsers()->attach($user->id);

            DB::commit();

            // Sent after commit so the queued job never races an uncommitted user,
            // and so a rolled-back creation cannot trigger a welcome email.
            $this->notifyManagerOfWelcome($user, $organization);

            $name = $locale === 'ar'
                ? (($user->first_name_ar ?? $user->first_name_en) . ' ' . ($user->last_name_ar ?? $user->last_name_en))
                : ($user->first_name_en . ' ' . $user->last_name_en);

            return [
                'success' => true,
                'data' => [
                    'id' => $user->id,
                    'name' => $name,
                    'email' => $user->email,
                ],
                'message' => __('admin.Manager created successfully'),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Error creating manager') . ': ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Remove a manager from organization.
     *
     * @param int $orgId Organization ID
     * @param int $userId User ID
     * @return array ['success' => bool, 'data' => null, 'message' => string]
     */
    public function removeManager(int $orgId, int $userId): array
    {
        $organization = Organization::find($orgId);

        if (!$organization) {
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Organization not found'),
            ];
        }

        $user = User::find($userId);

        if (!$user) {
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.User not found'),
            ];
        }

        DB::beginTransaction();

        try {
            // Remove from pivot table
            $organization->assignedUsers()->detach($userId);

            // Handle role switching
            $orgCount = $user->organizations()->count();

            // If user now has only 1 org and has Sub-Admin role, switch back to organizationmanager
            if ($orgCount == 1 && $user->hasRole('subadmin')) {
                $this->switchToOrgManager($user);
            }

            DB::commit();

            return [
                'success' => true,
                'data' => null,
                'message' => __('admin.Manager removed successfully'),
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'data' => null,
                'message' => __('admin.Error removing manager') . ': ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Sync managers to organization and handle role switching.
     *
     * @param Organization $organization
     * @param array $managerIds
     * @return array IDs of managers newly attached by this sync (already-assigned
     *               managers are excluded so re-saving the step does not re-notify).
     */
    protected function syncManagersToOrganization(Organization $organization, array $managerIds): array
    {
        // Get current assignments
        $currentManagerIds = $organization->assignedUsers()->pluck('users.id')->toArray();

        // Managers being added for the first time by this sync
        $newlyAssignedIds = array_values(array_diff($managerIds, $currentManagerIds));

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

        return $newlyAssignedIds;
    }

    /**
     * Send the welcome email to a manager whose account was just created.
     *
     * Delivery failures are logged and swallowed: the manager has already been
     * created and committed at this point, so a mail/queue error must not surface
     * as a failed request (the caller would retry and hit the email unique rule).
     *
     * @param User $user
     * @param Organization $organization
     * @return void
     */
    protected function notifyManagerOfWelcome(User $user, Organization $organization): void
    {
        try {
            $user->notify(new OrganizationManagerWelcomeNotification($organization, auth()->user()));
        } catch (\Throwable $e) {
            Log::error('Failed to send organization manager welcome notification', [
                'user_id' => $user->id,
                'organization_id' => $organization->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify existing users that they have been assigned to an organization.
     *
     * Failures are logged per-user so one bad recipient cannot stop the rest,
     * and never surface as a failed request — the assignment itself is committed.
     *
     * @param Organization $organization
     * @param array $userIds
     * @return void
     */
    protected function notifyNewlyAssignedManagers(Organization $organization, array $userIds): void
    {
        if (empty($userIds)) {
            return;
        }

        $admin = auth()->user();

        foreach (User::whereIn('id', $userIds)->get() as $user) {
            try {
                $user->notify(new OrganizationAssignedNotification($organization, $admin));
            } catch (\Throwable $e) {
                Log::error('Failed to send organization assignment notification', [
                    'user_id' => $user->id,
                    'organization_id' => $organization->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Switch user role from Organization Manager to Sub-Admin.
     *
     * @param User $user
     * @return void
     */
    protected function switchToSubAdmin(User $user): void
    {
        $user->deAllotRole('organizationmanager');
        $user->allotRole('subadmin');
        // Clear the organization_id field as sub-admins use pivot table
        $user->organization_id = null;
        $user->save();
    }

    /**
     * Switch user role from Sub-Admin to Organization Manager.
     *
     * @param User $user
     * @return void
     */
    protected function switchToOrgManager(User $user): void
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
}
