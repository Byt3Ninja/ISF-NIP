<?php
namespace App\Repositories;

use App\BuilderForm;
use Illuminate\Support\Facades\DB;

class FormRepository
{
    public function getAllFormsWithSteps(array $filters = [])
{
    $query = BuilderForm::with('steps');

    if (!empty($filters['search'])) {
        $query->where('form_name', 'like', '%' . $filters['search'] . '%');
    }

    return $query->get();
}

}

