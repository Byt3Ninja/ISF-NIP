<?php

namespace App\Repositories\Employee;

use App\Employee;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use App\Services\FileManagerService;

class EmployeeRepository implements EmployeeRepositoryInterface
{
    public function getByCompany($companyId): \Illuminate\Database\Eloquent\Collection
    {
        return Employee::where('Company_id', $companyId)->get();
    }

    public function create(array $data, array $documents = []): Employee
    {
        $documentPaths = [];
        
        // Handle document uploads
        if (!empty($documents)) {
            foreach ($documents as $document) {
                if ($document && $document->isValid()) {
                    $path = app(FileManagerService::class)->upload($document, 'employee_documents', 'public', 'common');
                    $documentPaths[] = $path;
                }
            }
        }
        
        $data['documents'] = $documentPaths;
        
        return Employee::create($data);
    }

    public function delete(int $id): bool
    {
        $employee = Employee::findOrFail($id);
        
        // Delete associated documents
        if (!empty($employee->documents)) {
            foreach ($employee->documents as $document) {
                Storage::disk('public')->delete($document);
            }
        }
        
        return $employee->delete();
    }
}
