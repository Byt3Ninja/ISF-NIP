<?php
namespace App\Repositories;

use App\Cycle;
use App\Almuni;
use App\Company;
use App\Milestone;
use App\Team_member;
use App\CompanyRelation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class CompanyRepository
{
    public function getCompanies(array $filters, bool $isApi = true, bool $paginate = true): LengthAwarePaginator|Collection
    {
        $locale = app()->getLocale();
        $nameField = $locale == 'ar' ? 'name_ar' : 'name_en';

        $query = Company::query()->with(['teamMembers', 'country', 'state', 'city', 'cycleRelation.organization', 'sectoR', 'stagE']);

        // Only show companies linked to 'registered' users and exclude admins/managers
        $query->whereHas('user', function ($q) {
            $q->whereHas('roles', function ($rq) {
                $rq->where('default_for', 'registered');
            })->whereDoesntHave('roles', function ($rq) {
                $rq->whereIn('default_for', ['admin', 'organizationmanager', 'subadmin']);
            });
        });
        $user = auth()->user();

        if ($user && !$user->hasRole('admin')) {
            $orgIds = $user->organizations_ids;
            if (!empty($orgIds)) {
                $isAlumni = !empty($filters['status']) && $filters['status'] === 'alumni';
                if ($isAlumni) {
                    $query->whereIn('id', function ($q) use ($orgIds) {
                        $q->select('company_id')
                            ->from('alumni')
                            ->whereNotNull('company_id')
                            ->whereIn('cycle_id', function ($q2) use ($orgIds) {
                                $q2->select('id')->from('cycles')
                                    ->whereIn('organization_id', $orgIds);
                            });
                    });
                } else {
                    $query->whereHas('cycleRelation.organization', function ($q) use ($orgIds) {
                        $q->whereIn('id', $orgIds);
                    });
                }
            }
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
                $query->enrolledInAny($cycleIds);
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
        
        if (!empty($filters['is_alumni'])) {
            if($filters['is_alumni'] == 1){
                $query->whereIn('id', function ($q) {
                    $q->select('company_id')
                    ->from('alumni')
                    ->whereNotNull('company_id');
                });
            }elseif($filters['is_alumni'] == 2){
                $query->whereNotIn('id', function ($q) {
                    $q->select('company_id')
                    ->from('alumni')
                    ->whereNotNull('company_id');
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
            $query->enrolledIn($filters['programId']);
        }

        if(!empty($filters['status'])){
            if($filters['status'] == 'alumni') {
                $query->whereIn('id', function ($q) {
                    $q->select('company_id')
                    ->from('alumni')
                    ->whereNotNull('company_id');
                });
            } elseif($filters['status'] == 'incubated') {
                $query->whereNotNull('cycle')->whereHas('stepdata', function ($q) {
                    $q->where('stype', '!=', 1);
                });
            } elseif($filters['status'] == 'enrolled') {
                // "Enrolled" = currently in a program, at any phase. Disapproval nulls
                // companies.cycle (ScreeningController::changestep()), so rejected
                // startups drop out.
                $query->whereHas('cycleRelation');
            }

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

        // Exports need every matching row, not the 9-per-page listing (C5).
        if (!$paginate) {
            return $query->get();
        }

        return $isApi
            ? $query->paginate($filters['limit'] ?? 10)->withQueryString()
            : $query->paginate(9)->withQueryString();
    }

     public function findWithRelations($id)
    {
        return Company::with(['country', 'state', 'city', 'teamMembers', 'sectoR', 'user', 'stage'])->find($id);
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
            'phone' => $data['phone'] ?? null,
            'LinkedIn' => $data['linkedin'] ?? null,
            'join_date' => $data['join_date'] ?? null,
            'founder_type' => $data['founder_type'] ?? null,
            'working_commitment' => $data['working_commitment'] ?? null,
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
public function team_MemberGet($companyId, $team = null)
{
     $teamMembers = DB::table('team_members')
        ->where('Company_id', $companyId);
        if($team != null){
            $teamMembers = $teamMembers->where('TeamMember', $team);
        }
        $teamMembers = $teamMembers->get();

        return $teamMembers;
}

public function findAllMilestones($companyId)
{
     $milestone = DB::table('milestones')
        ->where('Company_id', $companyId)
        ->where('visibility', '!=', 'private')
        ->orderBy('created_at')
        ->get();
        return $milestone;

}
public function findCompanyRelation($companyId)
{
    $companyRelation = CompanyRelation::where('company_id', $companyId)->with('technology', 'sector', 'industry')->get();

    return $companyRelation;
}

    /**
     * Upsert the Founder team_members row attached to $company, matched by user_id.
     */
    public function upsertFounderFromImport(Company $company, array $data, int $userId): Team_member
    {
        $existing = $company->teamMembers()
            ->where('TeamMember', 'Founder')
            ->where('user_id', $userId)
            ->first();

        $attrs = [
            'Name'               => trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
            'Email'              => $data['email']  ?? null,
            'phone'              => $data['phone']  ?? null,
            'join_date'          => $data['join_date']          ?? null,
            'founder_type'       => $data['founder_type']       ?? null,
            'working_commitment' => $data['working_commitment'] ?? null,
            'national_id'        => $data['national_id']        ?? null,
            'educational_status' => $data['educational_status'] ?? null,
            'date_of_birth'      => $data['date_of_birth']      ?? null,
            'TeamMember'         => 'Founder',
        ];

        // user_id is not mass-assignable ($fillable); it is the server-resolved account, set explicitly.
        if ($existing) {
            $existing->fill($attrs);
            $existing->user_id = $userId;
            $existing->save();
            return $existing->fresh();
        }
        $founder = $company->teamMembers()->make($attrs);
        $founder->user_id = $userId;
        $founder->save();
        return $founder;
    }

    /**
     * Upsert the Co-founder team_members row attached to $company, matched by Email (case-insensitive).
     * No User account is created — co-founders do not log in per spec.
     */
    public function upsertCoFounderFromImport(Company $company, array $data): Team_member
    {
        $emailLc = mb_strtolower(trim($data['email'] ?? ''), 'UTF-8');

        $existing = $company->teamMembers()
            ->where('TeamMember', 'Co-founder')
            ->whereRaw('LOWER(COALESCE(Email, "")) = ?', [$emailLc])
            ->first();

        $attrs = [
            'Name'               => $data['name']  ?? null,
            'Email'              => $data['email'] ?? null,
            'phone'              => $data['phone'] ?? null,
            'working_commitment' => $data['working_commitment'] ?? null,
            'educational_status' => $data['educational_status'] ?? null,
            'TeamMember'         => 'Co-founder',
        ];

        if ($existing) {
            $existing->update($attrs);
            return $existing->fresh();
        }
        return $company->teamMembers()->create($attrs);
    }

}
