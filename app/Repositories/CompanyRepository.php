<?php
namespace App\Repositories;

use App\Cycle;
use App\Company;
use App\Milestone;
use App\Team_member;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class CompanyRepository
{
    public function getCompanies(array $filters, bool $isApi = true): LengthAwarePaginator|Collection
    {
        $locale = app()->getLocale();
        $nameField = $locale == 'ar' ? 'name_ar' : 'name_en';

        $query = Company::query()->with(['teamMembers', 'country', 'state', 'city', 'cycleRelation.organization', 'sectoR', 'stagE']);
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

        // Handle multiple sectors filter
        if (!empty($filters['sectors']) && is_array($filters['sectors'])) {
            $query->whereIn('sector', $filters['sectors']);
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

     public function findWithRelations($id)
    {
        return Company::with(['country', 'state', 'city', 'teamMembers', 'sectoR'])->find($id);
    }

    public function update(Company $company, array $data): bool
    {
        return $company->update($data);
    }

    public function save(Company $company): bool
    {
        return $company->save();
    }

      public function createFounder(Company $company, array $data)
    {
        return $company->teamMembers()->create([
            'Name' => $data['name'],
            'Email' => $data['email'] ?? null,
            'LinkedIn' => $data['linkedin'] ?? null,
            'TeamMember' => 'Founder',
        ]);
    }

    public function updateFounder(Team_member $founder, array $data)
    {
        $founder->update([
         'Name' => $data['name'] ?? $founder->Name,
         'Email' => $data['email'] ?? $founder->Email,
         'LinkedIn' => $data['linkedin'] ?? $founder->LinkedIn,
     ]);
    return $founder;
    }

    public function createShareholder(Company $company, array $data)
    {
        return $company->teamMembers()->create([
            'Name' => $data['name'],
            'RoleShereholder' => $data['role'] ?? null,
            'PresentagShereholder' => $data['ownership'],
            'TeamMember' => 'Shareholder',
        ]);
    }

    public function updateShareholder(Team_member $shareholder, array $data)
    {
       $shareholder->update([
        'Name' => $data['name'] ?? $shareholder->Name,
        'RoleShereholder' => $data['role'] ?? $shareholder->RoleShereholder,
        'PresentagShereholder' => $data['ownership'] ?? $shareholder->PresentagShereholder,
      ]);
       return $shareholder;
    }

    public function createInvestor(Company $company, array $data)
    {
        return $company->teamMembers()->create([
            'Name' => $data['name'],
            'investorType' => $data['investmentType'] ?? null,
            'TeamMember' => 'Investor',
        ]);
    }

    public function updateInvestor(Team_member $investor, array $data)
    {
        $investor->update([
        'Name' => $data['name'] ?? $investor->Name,
        'investorType' => $data['investmentType'] ?? $investor->investorType,
      ]);
      return $investor;
    }

    public function createMilestone(Company $company, array $data)
    {
        return $company->milestones()->create([
            'plan' => $data['plan'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
        ]);
    }

    public function updateMilestone(Milestone $milestone, array $data)
    {
        $milestone->update([
          'plan' => $data['plan'] ?? $milestone->plan,
          'name' => $data['name'] ?? $milestone->name,
          'description' => $data['description'] ?? $milestone->description,
        ]);
        return $milestone;
    }

    public function deleteFounder(int $companyId, int $id): bool
{
    return DB::table('team_members')
        ->where('id', $id)
        ->where('Company_id', $companyId)
        ->where('TeamMember', 'Founder')
        ->delete();
}

public function deleteShareholder(int $companyId, int $id): bool
{
    return DB::table('team_members')
        ->where('id', $id)
        ->where('Company_id', $companyId)
        ->where('TeamMember', 'Shareholder')
        ->delete();
}

public function deleteInvestor(int $companyId, int $id): bool
{
    return DB::table('team_members')
        ->where('id', $id)
        ->where('Company_id', $companyId)
        ->where('TeamMember', 'Investor')
        ->delete();
}

public function findMilestone(int $companyId, int $id): ?object
{
    return DB::table('milestones')
        ->where('Company_id', $companyId)
        ->where('id', $id)
        ->first();
}

public function deleteMilestone(int $id): bool
{
    return DB::table('milestones')->where('id', $id)->delete();
}
}
