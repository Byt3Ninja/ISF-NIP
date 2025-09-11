<?php
namespace App\Repositories;

use App\BuilderForm;
use Illuminate\Support\Facades\DB;

class FormRepository
{
    public function getAllFormsWithSteps()
{
    return BuilderForm::with('steps')->get();
}

}

