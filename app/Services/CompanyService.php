<?php
namespace App\Services;

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
}

