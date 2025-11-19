<?php

namespace App\Services;

use App\Repositories\Employee\EmployeeRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class EmployeeService
{
    protected $employeeRepository;

    public function __construct(EmployeeRepositoryInterface $employeeRepository)
    {
        $this->employeeRepository = $employeeRepository;
    }

    public function getCompanyEmployees($companyId)
    {
        return $this->employeeRepository->getByCompany($companyId);
    }

    public function createEmployee(array $data, array $documents = [])
    {
        $validator = Validator::make($data, [
            'company_id' => 'required|exists:companies,id',
            'full_name' => 'required|string|max:100',
            'job_title' => 'required|string|max:100',
            'gender' => 'required|in:male,female',
            'employment_type' => 'required|in:full-time,part-time,contractor,intern',
            'hiring_date' => 'required|date',
        ]);

        // Validate documents if any
        if (!empty($documents)) {
            $documentRules = [
                'documents.*' => 'file|mimes:pdf,doc,docx,png,jpg,jpeg|max:10240', // 10MB max
            ];
            
            $documentValidator = Validator::make(['documents' => $documents], $documentRules);
            
            if ($documentValidator->fails()) {
                throw new ValidationException($documentValidator);
            }
        }

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $this->employeeRepository->create($data, $documents);
    }

    public function deleteEmployee($id)
    {
        return $this->employeeRepository->delete($id);
    }
}
