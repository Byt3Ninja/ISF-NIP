<?php

namespace App\Http\Controllers;

use stdClass;
use App\Field;
use App\BuilderForm;
use App\BuilderField;
use App\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\{Validator, DB, Mail};

class FormController extends Controller
{
    public function index() {
      $user = Auth::user();
  
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
      if($user->organization_id != null){
        $forms .= " AND BF.organization_id = ? ";
        $forms = DB::select($forms, [$user->organization_id]);
      }else{
        $forms = DB::select($forms);
      }
      $forms = collect($forms);
      $steps = DB::table('steps')->whereNull('deleted_at')->get();

      $data = $forms->map(function ($form) use ($l, $steps) {
        $data = new stdClass;
        $data->form_id = $form->BF_id ?? 'null';
        $data->form_name = $form->BF_form_name ?? 'null';
        $data->assigned_organization = $form->{'o_name_'.$l} ?? 'null';
        $data->last_modified = $form->BF_updated_at ?? 'null';
        $data->created_date = $form->BF_created_at ?? 'null';
          $filteredSteps = $steps->whereIn('data', $form->BF_id) ?? 'null';
          $data->submissions = $filteredSteps->count();
          $data->status = 'active' ?? 'null';
        return $data;
    });

   



    // return response()->json($data);
      return view('admin.settings.forms.index', compact('data'));
    }
    
    public function submissions() {
      $user = Auth::user();
      $data = [];
      $l = app()->getLocale();
      $forms = "SELECT 
      FD.company_id as 'FD_company_id',
      FD.user_id as 'FD_user_id',
      FD.step_id as 'FD_step_id',
      FD.created_at as 'FD_created_at',
      FD.updated_at as 'FD_updated_at',
      FD.deleted_at as 'FD_deleted_at',
      C.name_en as 'C_name_en',
      C.name_ar as 'C_name_ar',
      C.id as 'C_id',
      Concat(U.first_name_en, ' ', U.last_name_en) as 'user_name_en',
      Concat(U.first_name_ar, ' ', U.last_name_ar) as 'user_name_ar',
      U.id as 'U_id',
      S.title as 'S_title',
      S.id as 'S_id',
      S.cycle_id as 'S_cycle_id',
      CYC.title as 'CYC_title',
      CYC.id as 'CYC_id',
      CYC.organization_id as 'CYC_organization_id',
      O.name_en as 'O_name_en',
      O.name_ar as 'O_name_ar',
      O.id as 'O_id'
      FROM form_data as FD 
      LEFT JOIN companies as C ON C.id = FD.company_id
      LEFT JOIN users as U ON U.id = FD.user_id
      LEFT JOIN steps as S ON S.id = FD.step_id
      LEFT JOIN cycles as CYC ON CYC.id = S.cycle_id
      LEFT JOIN organizations as O ON O.id = CYC.organization_id
      WHERE FD.deleted_at IS NULL";
      if($user->organization_id != null){
        $forms .= " AND CYC.organization_id = ? ";
        $forms = DB::select($forms, [$user->organization_id]);
      }else{
        $forms = DB::select($forms);
      }
     $forms = collect($forms)->unique('FD_step_id')->values();

$C = 1;
$data = $forms->map(function ($form) use ($l, &$C) {
    $item = new stdClass;
    $item->submission_id = $C++;
    $item->step_id = $form->FD_step_id;
    $item->startup_name = $form->{'C_name_' . $l};
    $item->submitted_by = $form->{'user_name_' . $l};
    $item->step_name = $form->S_title;
    $item->program_name = $form->CYC_title;
    $item->organization_name = $form->O_name_en;
    $item->created_at = $form->FD_created_at;
    return $item;
});
      return view('admin.settings.forms.form-submissions.index', compact('data'));
    }
  
    public function submissionDetails(Request $request, $id) {
      
      $user = Auth::user();
      $data = [];
      $l = app()->getLocale();
      $forms = "SELECT 
      FD.company_id as 'FD_company_id',
      FD.user_id as 'FD_user_id',
      FD.step_id as 'FD_step_id',
      FD.question as 'FD_question',
      FD.answer as 'FD_answer',
      FD.created_at as 'FD_created_at',
      FD.updated_at as 'FD_updated_at',
      FD.deleted_at as 'FD_deleted_at',
      C.name_en as 'C_name_en',
      C.name_ar as 'C_name_ar',
      C.id as 'C_id',
      Concat(U.first_name_en, ' ', U.last_name_en) as 'user_name_en',
      Concat(U.first_name_ar, ' ', U.last_name_ar) as 'user_name_ar',
      U.id as 'U_id',
      S.title as 'S_title',
      S.id as 'S_id',
      S.cycle_id as 'S_cycle_id',
      S.data as 'S_data',
      CYC.title as 'CYC_title',
      CYC.id as 'CYC_id',
      CYC.organization_id as 'CYC_organization_id',
      O.name_en as 'O_name_en',
      O.name_ar as 'O_name_ar',
      O.id as 'O_id',
      BF.id as 'BF_id',
      BF.form_name as 'BF_form_name'
      FROM form_data as FD 
      LEFT JOIN companies as C ON C.id = FD.company_id
      LEFT JOIN users as U ON U.id = FD.user_id
      LEFT JOIN steps as S ON S.id = FD.step_id
      LEFT JOIN builder_forms as BF ON BF.id = S.data
      LEFT JOIN cycles as CYC ON CYC.id = S.cycle_id
      LEFT JOIN organizations as O ON O.id = CYC.organization_id
      WHERE FD.deleted_at IS NULL AND FD.step_id = ? ";
      $params = [];
     
      if($user->organization_id != null){
        $forms .= " AND CYC.organization_id = ? ";
        $forms = DB::select($forms, [$id, $user->organization_id]);
      }else{
        $forms = DB::select($forms, [$id]);
      }
      $forms = collect($forms);
      $C = 1;
      // نبدأ بتجميع كل الأسئلة حسب step_id
      $grouped = $forms->groupBy('FD_step_id');

      $data = $grouped->map(function ($group, $stepId) use ($l) {
          $first = $group->first();
      
          $submission = new stdClass;
          $submission->submission_id = $stepId;
          $submission->form_name = $first->BF_form_name;
          $submission->startup_name = $first->{'C_name_' . $l};
          $submission->submitted_by = $first->{'user_name_' . $l};
          $submission->step_name = $first->S_title;
          $submission->program_name = $first->CYC_title;
          $submission->organization_name = $first->O_name_en;
          $submission->submissionDate = $first->FD_created_at ?? $first->FD_updated_at;
      
          // answers
          $submission->answers = $group->map(function ($item) {
              return (object)[
                  'label' => $item->FD_question,
                  'value' => $item->FD_answer,
              ];
          })->values();
      
          return $submission;
      })->values()->all(); // remove collection keys (numeric indexing)
      
      $data = array_values($data);


            // return response()->json([
            //   'status' => true,
            //   'message' => 'Data retrieved successfully',
            //   'data' => $data
            // ], 200);
      return view('admin.settings.forms.form-submissions.show', compact('data'));
    }
}
