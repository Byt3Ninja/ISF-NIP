<?php

namespace App\Http\Controllers;

use stdClass;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\{Validator, DB, Mail};
use App\Exports\{SubmissionsExport, SubmissionDetailsExport};
use App\{Field, Formdata, BuilderForm, BuilderField, Organization};

class FormController extends Controller
{
	public function index()
	{
		$request = request();
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
			FROM
				builder_forms as BF
			LEFT JOIN
				organizations as O ON O.id = BF.organization_id
			WHERE
				BF.deleted_at IS NULL
		";
		$orgIds = $user->organizations_ids;
		if( !$user->hasRole('admin') && !empty($orgIds) )
		{
			$forms .= " AND BF.organization_id IN (" . implode(',', array_map('intval', $orgIds)) . ") ";
		}
		$forms = DB::select($forms);
		$forms = collect($forms);

		// A submission is one filled-in form: a distinct (step, startup, submitter)
		// group of answers in form_data. Counting `steps` rows instead would report
		// how many cycle steps reference the form, not how many people submitted it.
		$submissionsCountByForm = DB::table('form_data as FD')
			->join('steps as S', 'S.id', '=', 'FD.step_id')
			->leftJoin('cycles as CYC', 'CYC.id', '=', 'S.cycle_id')
			->whereNull('FD.deleted_at')
			->whereNull('S.deleted_at')
			->whereNotNull('S.data')
			->when(!$user->hasRole('admin') && !empty($orgIds), function ($query) use ($orgIds) {
				return $query->whereIn('CYC.organization_id', $orgIds);
			})
			->select('S.data as form_id', DB::raw('COUNT(DISTINCT FD.step_id, FD.company_id, FD.user_id) as submissions'))
			->groupBy('S.data')
			->pluck('submissions', 'form_id');

		$data = $forms->map(function ($form) use ($l, $submissionsCountByForm) {
			$data = new stdClass;
			$data->form_id = $form->BF_id ?? 'null';
			$data->form_name = $form->BF_form_name ?? 'null';
			$data->assigned_organization = $form->{'o_name_'.$l} ?? 'null';
			$data->org_id = $form->o_id ?? 'null';
			$data->last_modified = $form->BF_updated_at ?? 'null';
			$data->created_date = $form->BF_created_at ?? 'null';
			$data->submissions = (int) ($submissionsCountByForm[$form->BF_id] ?? 0);
			$data->status = 'active' ?? 'null';
			return $data;
		});

		$submissionsData = Formdata::formsData( $request )->all();
		$submissions = collect($submissionsData);

		$top_organizations = collect();

		// Both series are keyed off the submissions data so they describe the same
		// organization: a submission belongs to the organization of the cycle it was
		// made in, which is not necessarily the organization the form is assigned to.
		$submissionsByOrg = $submissions
				->filter(fn($item) => !empty($item->organization_name))
				->groupBy('organization_name');

		foreach ($submissionsByOrg as $org => $orgSubmissions) {
				$top_organizations[$org] = [
						'forms_with_submissions' => $orgSubmissions->pluck('form_id')->filter()->unique()->count(),
						'total_submissions'      => $orgSubmissions->count(),
				];
		}

		$top_organizations = $top_organizations
				->sortByDesc('total_submissions')
				->take(6)
				->toArray();


		$analytics = [
				'total_forms' => $data->count(),
				'total_submissions' => $data->sum(function ($item) {
						return $item->submissions;
				}),
				'total_organizations' => $data
						->pluck('assigned_organization')
						->filter(fn($org) => $org !== 'null')
						->unique()
						->count(),
				'total_submitters' => $submissions
						->pluck('submitted_by')
						->filter()
						->map(fn($names) => explode(',', $names))
						->flatten()
						->map(fn($name) => trim($name))
						->unique()
						->count(),
				'top_organizations' => $top_organizations,
		];

		return view('admin.settings.forms.index', compact('data', 'submissionsData', 'analytics'));
	}
	
	public function submissions()
	{
		$request = request();
		$data = Formdata::formsData( $request )->all();
		return view('admin.settings.forms.form-submissions.index', compact('data'));
	}

	public function export( $type )
	{
		$tail = 'xlsx';
		$type = strtolower( $type );
		$type = in_array( $type, ['csv', 'excel'] ) ? $type : 'excel';
		if( $type == 'csv' ) { $tail = 'csv'; } elseif( $type == 'excel' ) { $tail = 'xlsx'; } else { return abort(404); }
		$request = request();
		$data = Formdata::formsData( $request );

		$date = date('Y_m_d_H_i_s');
		return Excel::download(new SubmissionsExport( $data ), 'form_submissions_' . $date . '.' . $tail);
	}
  
	public function submissionDetails(Request $request, $id)
	{
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
			FROM
				form_data as FD 
			LEFT JOIN
				companies as C ON C.id = FD.company_id
			LEFT JOIN
				users as U ON U.id = FD.user_id
			LEFT JOIN
				steps as S ON S.id = FD.step_id
			LEFT JOIN
				builder_forms as BF ON BF.id = S.data
			LEFT JOIN
				cycles as CYC ON CYC.id = S.cycle_id
			LEFT JOIN
				organizations as O ON O.id = CYC.organization_id
			WHERE
				FD.deleted_at IS NULL
				AND FD.step_id = ? 
		";
		$orgIds = $user->organizations_ids;
		if( !$user->hasRole('admin') && !empty($orgIds) )
		{
			$forms .= " AND CYC.organization_id IN (" . implode(',', array_map('intval', $orgIds)) . ") ";
		}
		$forms = DB::select($forms, [$id]);

		$forms = collect($forms);
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
			$submission->answers = $group->map(function ($item) use ($l) {
				$value = $item->FD_answer;
				// Check if the value is a file path
				if( is_string($value) && (strpos($value, '/') !== false || strpos($value, '\\') !== false) )
				{
					// Extract filename from path
					$filename = basename($value);
					$value = ['type' => 'file', 'path' => $value, 'file_name' => $filename, 'file_url' => asset($value)];
				}

				$output = new stdClass;
				$output->user_id = $item->FD_user_id;
				$output->name = $item->{'user_name_' . $l};
				$output->label = $item->FD_question;
				$output->value = $this->parse_answer( $value );

				return $output;
			})->groupBy('user_id')->values();

			return $submission;
		})->values()->all(); // remove collection keys (numeric indexing)
		
		$data = array_values($data); 	
		// return response()->json(['status' => true, 'message' => 'Data retrieved successfully', 'data' => $data], 200);
		return view('admin.settings.forms.form-submissions.show', compact('data', 'id'));
	}

	public function exportSubmissionDetails(Request $request, $id)
	{
		$user = Auth::user();
		$l = app()->getLocale();
		$forms = DB::table('form_data AS FD')
			->select([
				'FD.id AS FD_id',
				'FD.company_id AS FD_company_id',
				'FD.user_id AS FD_user_id',
				'FD.step_id AS FD_step_id',
				'FD.question AS FD_question',
				'FD.answer AS FD_answer',
				'FD.created_at AS FD_created_at',
				'FD.updated_at AS FD_updated_at',
				'FD.deleted_at AS FD_deleted_at',
				'C.name_en AS C_name_en',
				'C.name_ar AS C_name_ar',
				'C.id AS C_id',
				DB::raw("Concat(U.first_name_en, ' ', U.last_name_en) AS 'user_name_en'"),
				DB::raw("Concat(U.first_name_ar, ' ', U.last_name_ar) AS 'user_name_ar'"),
				'U.id AS U_id',
				'S.title AS S_title',
				'S.id AS S_id',
				'S.cycle_id AS S_cycle_id',
				'S.data AS S_data',
				'CYC.title AS CYC_title',
				'CYC.id AS CYC_id',
				'CYC.organization_id AS CYC_organization_id',
				'O.name_en AS O_name_en',
				'O.name_ar AS O_name_ar',
				'O.id AS O_id',
				'BF.id AS BF_id',
				'BF.form_name AS BF_form_name',
				'JC.id AS JC_id',
				'JC.form_data_id AS JC_form_data_id',
				'JC.comment AS JC_comment',
				'JC.user_id AS JC_user_id',
				DB::raw("Concat(UJ.first_name_en, ' ', UJ.last_name_en) AS 'judger_name_en'"),
				DB::raw("Concat(UJ.first_name_ar, ' ', UJ.last_name_ar) AS 'judger_name_ar'"),
			])
			->leftJoin('companies as C', 'C.id', '=', 'FD.company_id')
			->leftJoin('users as U', 'U.id', '=', 'FD.user_id')
			->leftJoin('steps AS S', 'S.id', '=', 'FD.step_id')
			->leftJoin('builder_forms AS BF', 'BF.id', '=', 'S.data')
			->leftJoin('cycles AS CYC', 'CYC.id', '=', 'S.cycle_id')
			->leftJoin('organizations as O', 'O.id', '=', 'CYC.organization_id')
			->leftJoin('form_data_judges_comments AS JC', 'JC.form_data_id', '=', 'FD.id')
			->leftJoin('users AS UJ', 'UJ.id', '=', 'JC.user_id')
			->whereNull('FD.deleted_at')
			->where('FD.step_id', $id)
			->when(!$user->hasRole('admin') && !empty($user->organizations_ids), function ($query) use ($user) {
				return $query->whereIn('CYC.organization_id', $user->organizations_ids);
			})
		->get();

		$grouped = $forms->groupBy('FD_step_id');
		$data = $grouped->map(function ($group, $stepId) use ($l) {
			$grouped = $group->groupBy('FD_id')->map(function ($item) use ($stepId, $l) {
				$first = $item->first();
				$comments = $item->where('JC_form_data_id', $first->FD_id)->map(function($c) use ($l) {
					$comment = new stdClass;
					$comment->id = $c->JC_id;
					$comment->form_data_id = $c->JC_form_data_id;
					$comment->comment = $c->JC_comment;
					$comment->user_id = $c->JC_user_id;
					$comment->name = $c->{'judger_name_' . $l};
					return $comment;
				})->values();

				$submission = new stdClass;
				$submission->submission_id = $stepId;
				$submission->form_name = $first->BF_form_name;
				$submission->startup_name = $first->{'C_name_' . $l};
				$submission->submitted_by = $first->{'user_name_' . $l};
				$submission->step_name = $first->S_title;
				$submission->program_name = $first->CYC_title;
				$submission->organization_name = $first->O_name_en;
				$submission->submissionDate = $first->FD_created_at ?? $first->FD_updated_at;

				$submission->answers = $item->map(function ($item) use ($l) {
					$value = $item->FD_answer;
					if( is_string( $value ) && (strpos($value, '/') !== false || strpos($value, '\\') !== false) )
					{
						$filename = basename($value);
						$value = ['type' => 'file', 'path' => $value, 'file_name' => $filename, 'file_url' => asset($value)];
					}

					$output = new stdClass;
					$output->id = $item->FD_id;
					$output->submission_id = $item->FD_step_id;
					$output->user_id = $item->FD_user_id;
					$output->name = $item->{'user_name_' . $l};
					$output->label = $item->FD_question;
					$output->value = $this->parse_answer( $value );
					return $output;
				})->unique('id')->groupBy('user_id')->values()->flatten();

				$submission->answers = $submission->answers->map(function ($answer) use ($comments) {
					$answer->comments = $comments->where('form_data_id', $answer->id);
					return $answer;
				});
				return $submission;
			})->values()->map(function ($item) {
				$item->answers = $item->answers?->first() ?? collect([]);
				return $item;
			});
			$grouped = $grouped->groupBy('answers.user_id')->map(function($items) {
				$item = $items->first();
				$item->answers = $items->pluck('answers');
				return $item;
			})->values();

			return $grouped;
		})->values()->flatten();

		$tail = 'xlsx';
		$type = $request->has('type') ? $request->input('type', 'excel') : 'excel';
		$type = strtolower( $type );
		$type = in_array( $type, ['csv', 'excel'] ) ? $type : 'excel';
		if( $type == 'csv' ) { $tail = 'csv'; } elseif( $type == 'excel' ) { $tail = 'xlsx'; } else { return abort(404); }

		$date = date('Y_m_d_H_i_s');
		return Excel::download(new SubmissionDetailsExport( $data ), 'form_submission_details_' . $date . '.' . $tail);
	}

	private function parse_answer( $answer )
	{
		if( is_numeric( $answer ) ) { return $answer; }
		if( is_array( $answer ) ) { return $answer; }
		$decoded = json_decode( $answer, true );
		if( JSON_ERROR_NONE === json_last_error() )
		{
			$decoded = collect( $decoded )->implode(' , ');
			return $decoded;
		}
		return $answer;
	}
}
