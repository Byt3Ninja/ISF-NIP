<?php

namespace App\Repositories\Employee;

use App\Employee;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;

interface EmployeeRepositoryInterface
{
    public function getByCompany($companyId): Collection;
    public function create(array $data, array $documents = []): Employee;
    public function delete(int $id): bool;
}
