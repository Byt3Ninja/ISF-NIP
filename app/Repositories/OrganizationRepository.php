<?php
namespace App\Repositories;

use App\Organization;

class OrganizationRepository
{
    public function allWithRelations($status = null)
    {
        $query = Organization::with(['user.company', 'cycles']);
        if (!is_null($status)) {
            $query->where('status', $status);
        }
        return $query->get();
    }

    public function counts($status = null)
    {
        if (!is_null($status)) {
            $count = Organization::where('status', $status)->count();
            return [
                'totalOrganizations' => $count,
                'totalActiveOrganizations' => $status == 1 ? $count : 0,
                'totalInactiveOrganizations' => $status == 0 ? $count : 0,
            ];
        }

        return [
            'totalOrganizations' => Organization::count(),
            'totalActiveOrganizations' => Organization::where('status', 1)->count(),
            'totalInactiveOrganizations' => Organization::where('status', 0)->count(),
        ];
    }

    public function find($id)
    {
        return Organization::find($id);
    }

    public function findWithCycles($id)
    {
        return Organization::with(['cycles', 'cycles.companies'])->find($id);
    }

    public function create(array $data)
    {
        return Organization::create($data);
    }

    public function update(Organization $organization, array $data)
    {
        return $organization->update($data);
    }

    public function delete(Organization $organization)
    {
        return $organization->delete();
    }

    public function forceDelete(Organization $organization)
    {
        return $organization->forceDelete();
    }
}
