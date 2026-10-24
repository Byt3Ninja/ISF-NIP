<?php
namespace App\Services;

use App\Company;
use App\Milestone;
use App\Team_member;
use Illuminate\Support\Facades\DB;
use App\Repositories\CompanyRepository;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\CompanyProfileResource;
use App\Services\ProtectedFiles\ProtectedFiles;
use App\Enums\ProtectedFileSet;
use Illuminate\Http\UploadedFile;

class CompanyService
{
    protected CompanyRepository $companyRepository;
    protected FileManagerService $fileManager;

    public function __construct(CompanyRepository $companyRepository, ?FileManagerService $fileManager = null)
    {
        $this->companyRepository = $companyRepository;
        $this->fileManager = $fileManager ?? app(FileManagerService::class);
    }

    public function getCompanies(array $filters, bool $isApi = true, bool $paginate = true)
    {
        return $this->companyRepository->getCompanies($filters, $isApi, $paginate);
    }

    public function getCompanyProfile($id, $user)
    {
        if (!$user->ableToAny(['company.profile_view', 'company.profile_view_mine'])) {
            return [ 'type' => 'forbidden' ];
        }

        $company = $this->companyRepository->findWithRelations($id);

        if (!$company) {
            return [ 'type' => 'not_found' ];
        }

        $companyOrgId = $company->cycleRelation?->organization_id;

        // Organization staff are limited to their own organizations; a founder still sees only
        // their own company (a founder "owns" their program's organization for scoping purposes,
        // which must not open every other startup of that organization to them).
        $allowed = $user->hasRole('admin')
            || ($user->hasRole('Registered') && (int) $user->company_id === (int) $company->id)
            || ($user->hasAnyRole(['organizationManager', 'subadmin']) && $user->ownsOrganization($companyOrgId));

        if ($allowed) {
            return [
                'type' => 'success',
                'data' => new CompanyProfileResource($company)
            ];
        }

        return [ 'type' => 'forbidden' ];
    }

     public function updateCompanyProfile(array $data, $user)
    {
        $company = $this->companyRepository->findWithRelations($user->company_id);
        if (!$company) {
            return ['status' => 'not_found'];
        }

        DB::transaction(function () use ($data, $company, $user) {

            if (isset($data['name'])) $company->name_en = $data['name'];
            if (isset($data['country_id'])) $company->country_id = $data['country_id'];
            if (array_key_exists('state_id', $data)) $company->state_id = $data['state_id'];
            if (array_key_exists('sector_id', $data)) $company->sector = $data['sector_id'];

            if (isset($data['categories'])) {
                $translated = [];
                foreach ($data['categories'] as $key) {
                    $translationKey = __("admin.categories.$key");
                    if ($translationKey) {
                        $translated[] = $translationKey;
                    }
                }
                $company->business_categories = json_encode($translated);
            }

            // Basic Info
            $company->address_en = $data['address'] ?? $company->address_en;
            $company->years_of_operations = $data['yearsOfOperations'] ?? $company->years_of_operations;
            $company->product_or_service = $data['productOrService'] ?? $company->product_or_service;
            $company->stage = $data['stage_id'] ?? $company->stage;

            // Logo
            if (!empty($data['logo'])) {
                $company->logo = $this->fileManager->update($data['logo'], $company->logo, 'images/logo', 'public_uploads', 'image');
            }

            // Social Links
            foreach (['email', 'phone', 'whatsapp', 'website', 'googleMapUrl', 'linkedin', 'x', 'facebook', 'youtube', 'instagram', 'tiktok'] as $field) {
                if (isset($data[$field])) {
                    $column = match ($field) {
                        'x' => 'twitter',
                        'googleMapUrl' => 'google_map',
                        default => $field,
                    };
                    $company->{$column} = $data[$field];
                }
            }

            // About Fields
            foreach ([
                'aboutStartup' => 'info_company',
                'aboutProblem' => 'about_problem',
                'aboutMarketOpportunities' => 'about_market_opportunity',
                'aboutCompetitors' => 'about_competitors',
                'aboutMonetization' => 'about_monetize',
                'aboutChallenges' => 'about_challenges'
            ] as $req => $col) {
                if (isset($data[$req])) $company->{$col} = $data[$req];
            }

            // Sustainability
            if (isset($data['sustainabilityFeatures'])) {
                $company->Sustainable_development = json_encode($data['sustainabilityFeatures']);
            }

            // Financial and Business
            foreach ([
                'currency' => 'Currency',
                'preMoneyEvaluation' => 'Pre_money_Valuation',
                'raisedFund' => 'raised_fund',
                'fundingTarget' => 'desired_funding_amount',
                'targetMarket' => 'targeted_market',
                'businessType' => 'business_type',
                'financialPlan' => 'financial_plan_description'
            ] as $req => $col) {
                if (isset($data[$req])) {
                    $company->{$col} = in_array($req, ['targetMarket']) ? json_encode($data[$req]) : $data[$req];
                }
            }

            // File uploads (financialPlanAttachment, pitchDeckFile, documents, businessPlanAttachment)
            $this->handleFileUploads($company, $data, $user);

            // Team details
            $company->team_num = $data['teamNumber'] ?? $company->team_num;
            $company->working_team_summary = $data['workingTeamDetails'] ?? $company->working_team_summary;

            // Business Model
            $company->business_model = $data['businessModel'] ?? $company->business_model;
            $company->business_model_description = $data['businessModelDescription'] ?? $company->business_model_description;

            // Startup status
            if (isset($data['haveStartup'])) {
                if ($data['haveStartup'] === 'Yes') {
                    $company->CreatedTax = $data['createdTax'] ?? 0;
                    // Commercial_Register / Tax_Card hold uploaded files (stored paths). The API's
                    // `commercialRegister` / `taxCard` are free-text strings, so they are no longer
                    // written into those columns: a typed value must never be read back as a file path.
                    $company->Country_Register_id = $data['country_Register_id'] ?? null;
                } else {
                    $company->CreatedTax = 0;
                    $company->Commercial_Register = null;
                    $company->Tax_Card = null;
                    $company->Country_Register_id = null;
                }
            }

            $this->companyRepository->save($company);
        });

        return [
            'status' => 'success',
            'company' => new CompanyProfileResource($company->fresh(['teamMembers', 'state', 'city', 'country', 'stage', 'sector']))
        ];
    }

    protected function handleFileUploads(Company $company, array $data, $user): void
    {
        // Validate every uploaded file before replacing any of them.
        $this->fileManager->assertAllAllowed(array_merge(
            [
                [$data['pitchDeckFile'] ?? null, 'common'],
                [$data['financialPlanAttachment'] ?? null, 'common'],
                [$data['businessPlanAttachment'] ?? null, 'common'],
            ],
            array_map(fn ($document) => [$document, 'document'], (array) ($data['documents'] ?? []))
        ));

        // Stored privately and recorded against this company (see ProtectedFiles).
        $protectedFiles = app(ProtectedFiles::class);
        $owner = ['owner_type' => 'company', 'owner_id' => $company->id, 'company_id' => $company->id];

        if (!empty($data['pitchDeckFile'])) {
            $file = $data['pitchDeckFile'];
            $company->pitch_deck = $protectedFiles->replace(
                $file,
                $company->pitch_deck,
                ProtectedFileSet::COMPANY_PITCH,
                $company->id . '/pitch_decks/documents',
                $owner + ['field' => 'pitch_deck'],
                'common'
            );
        }

        if (!empty($data['financialPlanAttachment'])) {
            $file = $data['financialPlanAttachment'];
            $company->financial_plan = $protectedFiles->replace(
                $file,
                $company->financial_plan,
                ProtectedFileSet::COMPANY_PITCH,
                $company->id . '/financial_plan/documents',
                $owner + ['field' => 'financial_plan'],
                'common'
            );
        }

        if (!empty($data['documents'])) {
            $existing = json_decode($company->getRawOriginal('documents') ?? '[]', true);
            if (!is_array($existing)) {
                $existing = [];
            }
            $newPaths = [];
            foreach ($data['documents'] as $document) {
                if ($document instanceof UploadedFile) {
                    $newPaths[] = $protectedFiles->store($document, ProtectedFileSet::COMPANY_PITCH, $company->id . '/pitch_decks/documents', $owner + ['field' => 'documents'], 'document')->path;
                }
            }
            $existing = array_merge($existing, $newPaths);
            $company->documents = json_encode($existing);
        }

        if (!empty($data['businessPlanAttachment']) && $data['businessPlanAttachment']->isValid()) {
            $file = $data['businessPlanAttachment'];
            $folder = match (true) {
                str_starts_with($file->getMimeType(), 'image/') => 'images',
                str_starts_with($file->getMimeType(), 'video/') => 'videos',
                in_array($file->getClientOriginalExtension(), ['pdf', 'doc', 'docx', 'ppt', 'pptx']) => 'documents',
                default => 'others',
            };

            $company->business_plan = $protectedFiles->replace(
                $file,
                $company->business_plan,
                ProtectedFileSet::COMPANY_PITCH,
                $company->id . '/business_plan/' . $folder,
                $owner + ['field' => 'business_plan'],
                'common'
            );
        }

        if (isset($data['pitchDeckVideoUrl'])) {
            $company->pitch_deck_video = $data['pitchDeckVideoUrl'];
        }
    }

     public function createFounder(array $data)
    {
        $company = auth()->user()->company;
        return $this->companyRepository->createFounder($company, $data);
    }

    public function updateFounder(int $id, array $data)
    {
        $founder = Team_member::where('id', $id)
            ->where('Company_id', auth()->user()->company_id)
            ->where('TeamMember', 'Founder')
            ->firstOrFail();

        return $this->companyRepository->updateFounder($founder, $data);
    }

    public function createShareholder(array $data)
    {
        $company = auth()->user()->company;
        return $this->companyRepository->createShareholder($company, $data);
    }

    public function updateShareholder(int $id, array $data)
    {
        $shareholder = Team_member::where('id', $id)
            ->where('Company_id', auth()->user()->company_id)
            ->where('TeamMember', 'Shareholder')
            ->firstOrFail();

        return $this->companyRepository->updateShareholder($shareholder, $data);
    }

    public function createInvestor(array $data)
    {
        $company = auth()->user()->company;
        return $this->companyRepository->createInvestor($company, $data);
    }

    public function updateInvestor(int $id, array $data)
    {
        $investor = Team_member::where('id', $id)
            ->where('Company_id', auth()->user()->company_id)
            ->where('TeamMember', 'Investor')
            ->firstOrFail();

        return $this->companyRepository->updateInvestor($investor, $data);
    }

    public function createMilestone(array $data)
    {
        $company = auth()->user()->company;
        return $this->companyRepository->createMilestone($company, $data);
    }

    public function updateMilestone(int $id, array $data)
    {
        $milestone = Milestone::where('id', $id)
            ->where('Company_id', auth()->user()->company_id)
            ->firstOrFail();

        return $this->companyRepository->updateMilestone($milestone, $data);
    }

    public function deleteFounder(int $companyId, int $id): bool
{
    return $this->companyRepository->deleteFounder($companyId, $id);
}

public function deleteShareholder(int $companyId, int $id): bool
{
    return $this->companyRepository->deleteShareholder($companyId, $id);
}

public function deleteInvestor(int $companyId, int $id): bool
{
    return $this->companyRepository->deleteInvestor($companyId, $id);
}

public function findMilestone(int $companyId, int $id = 0): ?object
{
    
    return $this->companyRepository->findMilestone($companyId, $id);
}

public function deleteMilestone(int $id): bool
{
    return $this->companyRepository->deleteMilestone($id);
}
public function getCompanyRelation($id)
{
    $company = $this->companyRepository->findWithRelations($id);
    if (!$company) {
        return [ 'type' => 'not_found' ];
    }
    return  $company;
}
public function getTeamMember($companyId, $team = null)
{
    $teamMembers = $this->companyRepository->team_MemberGet($companyId, $team);
    if (!$teamMembers) {
        return [ 'type' => 'not_found' ];
    }
    return $teamMembers;
    
}
public function getAllMilestones($companyId)
{
    $milestones = $this->companyRepository->findAllMilestones($companyId);
    if (!$milestones) {
        return [ 'type' => 'not_found' ];
    }
    return $milestones;
}
public function findCompanyRelation($companyId)
{
    $companyRelationRet = [];
    $companyRelationRet['companyRelation'] = $this->companyRepository->findCompanyRelation($companyId);
    if (!$companyRelationRet['companyRelation']) {
        return [ 'type' => 'not_found' ];
    }
        $companyRelationRet['technologies'] = $companyRelationRet['companyRelation']->whereNotNull('technology_id')
            ->pluck('technology')
            ->filter();
        $companyRelationRet['sectors'] = $companyRelationRet['companyRelation']->whereNotNull('sector_id')
            ->pluck('sector')
            ->filter();
        $companyRelationRet['industry'] = $companyRelationRet['companyRelation']->whereNotNull('industry_id')
            ->pluck('industry')
            ->filter()
            ->first();

    return $companyRelationRet;
}

    /**
     * Pure persistence path for importer — no HTTP/Request/Auth coupling.
     * Matches by name_en (case-insensitive).
     */
    public function upsertFromImport(array $data, ?int $existingCompanyId = null): Company
    {
        if ($existingCompanyId) {
            $company = Company::findOrFail($existingCompanyId);
            $this->companyRepository->update($company, $data);
            return $company->fresh();
        }

        $nameEn = trim($data['name_en'] ?? '');
        if ($nameEn === '') {
            throw new \InvalidArgumentException('upsertFromImport requires name_en');
        }

        $company = Company::query()
            ->whereRaw('LOWER(name_en) = ?', [mb_strtolower($nameEn, 'UTF-8')])
            ->first();

        if ($company) {
            $this->companyRepository->update($company, $data);
            return $company->fresh();
        }

        $new = new Company($data);
        $this->companyRepository->save($new);
        return $new->fresh();
    }

}

