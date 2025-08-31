<?php
namespace App\Repositories;

use App\Company;
use App\Cycle;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CompanyRepository
{
    public function getCompanies(array $filters, bool $isApi = true): LengthAwarePaginator|Collection
    {
        $locale = app()->getLocale();
        $nameField = $locale == 'ar' ? 'name_ar' : 'name_en';

        $query = Company::query()->with(['teamMembers', 'country', 'state', 'city', 'cycleRelation.organization']);
        $user = auth()->user();

        if ($user && $user->hasRole('organizationManager')) {
            $organizationId = $user->organization_id;
            $query->whereHas('cycleRelation.organization', function ($q) use ($organizationId) {
                $q->where('id', $organizationId);
            });
        }

        if (!empty($filters['role']) && $filters['role'] === 'organizationManager') {
            $query->whereHas('cycleRelation.organization', function ($q) use ($filters) {
                $q->where('id', $filters['organization_id']);
            });
        }

        if (!empty($filters['organization'])) {
            if ($isApi) {
                $query->whereHas('cycleRelation.organization', function ($q) use ($filters, $locale) {
                    if (is_numeric($filters['organization'])) {
                        $q->where('id', $filters['organization']);
                    } else {
                        $q->where("name_{$locale}", 'like', "%{$filters['organization']}%");
                    }
                });
            } else {
                $cycleIds = Cycle::where('organization_id', $filters['organization'])->pluck('id');
                $query->whereIn('cycle', $cycleIds);
            }
        }

        if (!empty($filters['sector'])) {
            if (is_numeric($filters['sector'])) {
                $query->where('sector', $filters['sector']);
            } else {
                $query->whereHas('sectoR', function ($q) use ($filters, $locale) {
                    $q->where("name_{$locale}", 'like', "%{$filters['sector']}%");
                });
            }
        }

        if (!empty($filters['stage'])) {
            $query->where('stage', 'like', '%' . $filters['stage'] . '%');
        }

        if (!empty($filters['country'])) {
            $query->whereHas('country', function ($q) use ($filters, $locale) {
                $q->where("name_{$locale}", 'like', "%{$filters['country']}%");
            });
        }

        if (!empty($filters['state'])) {
            $query->whereHas('state', function ($q) use ($filters, $locale) {
                $q->where("name_{$locale}", 'like', "%{$filters['state']}%");
            });
        }

        if (!empty($filters['programId'])) {
            $query->where('cycle', $filters['programId']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($sub) use ($search, $locale) {
                $sub->where("name_{$locale}", 'like', "%{$search}%")
                    ->orWhereHas('sectoR', fn($q) => $q->where("name_{$locale}", 'like', "%{$search}%"))
                    ->orWhereHas('city', fn($q) => $q->where("name_{$locale}", 'like', "%{$search}%"))
                    ->orWhereHas('teamMembers', fn($q) => $q->where('Name', 'like', "%{$search}%"));
            });
        }

        return $isApi
            ? $query->paginate($filters['limit'] ?? 10)
            : $query->get();
    }
}
