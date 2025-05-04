<?php

namespace App\Http\Controllers;

use stdClass;
use App\Field;
use App\BuilderForm;
use App\BuilderField;
use App\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Validator, DB, Mail};

class FormController extends Controller
{
    public function index() {
      $data = [];
      $l = app()->getLocale();
      $forms = "SELECT 
        BF.id as 'BF_id',
        BF.form_name as 'BF_form_name',
        BF.deleted_at as 'BF_deleted_at',
        BF.created_at as 'BF_created_at',
        BF.updated_at as 'BF_updated_at',
        O.name_en as 'o_name_en',
        O.name_ar as 'o_name_ar',
        O.id as 'o_id'
      FROM builder_forms as BF
      LEFT JOIN organizations as O ON O.id = BF.organization_id
      WHERE BF.deleted_at IS NULL";
      $forms = DB::select($forms);
      $forms = collect($forms);
      $steps = DB::table('steps')->whereNull('deleted_at')->get();

      $data = $forms->map(function ($form) use ($l, $steps) {
        $data = new stdClass;
        $data->form_id = $form->BF_id;
        $data->form_name = $form->BF_form_name;
        $data->assigned_organization = $form->{'o_name_'.$l};
        $data->last_modified = $form->BF_updated_at;
        $data->created_date = $form->BF_created_at;
          $filteredSteps = $steps->whereIn('data', $form->BF_id);
          $data->submissions = $filteredSteps->count();
          $data->status = 'active';

          // $step = $steps->map(function ($formstep) use ($l, $steps) {
          //   $data = new stdClass;
          //   $data->step_data = $formstep->data;
          //   return $data;
          // });
          // $data->steps = $step;

          // if($filteredSteps->count() > 0) {
          //   $data->last_submission = $filteredSteps->last()->created_at;
          // } else {
          //   $data->last_submission = null;
          // }
          
      


        // $activeornot = "SELECT * FROM steps as S
        // LEFT JOIN form_data as FD ON FD.step_id = S.id
        // WHERE S.deleted_at IS NULL";
        // $activeornot = DB::select($activeornot);
        // $activeornot = collect($activeornot);
        
        // if($activeornot->data == $form->BF_id) {
        //   $data->active = true;
        // } else {
        //   $data->active = false;
        // }
       
        return $data;
    });
    
     
      return view('admin.settings.forms.index', compact('data'));
    }
    
    public function submissions() {
      return view('admin.settings.forms.form-submissions.index');
    }
  
    public function submissionDetails() {
      return view('admin.settings.forms.form-submissions.show');
    }
}
