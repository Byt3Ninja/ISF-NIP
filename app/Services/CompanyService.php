<?php
namespace App\Services;

use App\Milestone;
use App\Team_member;
use App\Repositories\CompanyRepository;

class CompanyService
{
    protected CompanyRepository $companyRepository;

    public function __construct(CompanyRepository $companyRepository)
    {
        $this->companyRepository = $companyRepository;
    }

    public function getCompanies(array $filters, bool $isApi = true)
    {
        return $this->companyRepository->getCompanies($filters, $isApi);
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

public function findMilestone(int $companyId, int $id): ?object
{
    return $this->companyRepository->findMilestone($companyId, $id);
}

public function deleteMilestone(int $id): bool
{
    return $this->companyRepository->deleteMilestone($id);
}
}

