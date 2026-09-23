<?php

namespace App\Http\Controllers;

use App\Company;
use App\Exports\ScreeningEvaluationExport;
use App\Factories\OnlineMeetingFactory;
use App\FinalData;
use App\Finalscreening;
use App\Formfield;
use App\Notifications\TemplateEmail;
use App\Notifications\WelcomeJudgerNotification;
use App\Screening;
use App\ScreeningEvaluation;
use App\ScreeningEvaluationResults;
use App\Services\PanelWindowService;
use App\Step;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UploadScreeningGuidelinesImageRequest;

class FinalscreeningController extends Controller
{
	use \App\Traits\ManagesStepForm;

	protected $meetingService;
	public function __construct()
	{
		$this->meetingService =  OnlineMeetingFactory::create();
	}

	public function index(Request $request, $id = 0)
	{
		$user = auth()->user();
		$orgIds = $user->organizations_ids;

		/** -------------------------
		 * Base steps query with filters
		 * ------------------------- */
		$stepsQuery = Step::with(['cycle', 'cycle.organization'])->when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
			$q->whereHas('cycle', fn ($c) => $c->whereIn('organization_id', $orgIds) );
		})->where('stype', 5);

		$programs_in_final_screening = $stepsQuery->get();
		// Apply filters
		if ($request->filled('search'))
		{
			$search = $request->search;
			$stepsQuery->where(function ($q) use ($search) {
				$q->where('title', 'like', "%{$search}%")->orWhereHas('cycle', function ($c) use ($search) { $c->where('title', 'like', "%{$search}%"); } )
					->orWhereHas('cycle.organization', function ($o) use ($search) {
					$o->where('name_en', 'like', "%{$search}%")->orWhere('name_ar', 'like', "%{$search}%");
				});
			});
		}

		if( $request->filled('status') && $request->input('status', 'all') != 'all')
		{
			$today = now()->toDateString();
			switch ($request->status)
			{
				case 'upcoming':
					$stepsQuery->where('from', '>', $today);
				break;
				case 'in_progress':
					$stepsQuery->where('from', '<=', $today)->where('to', '>=', $today);
				break;
				case 'completed':
					$stepsQuery->where('to', '<', $today);
				break;
			}
		}

		if ($request->filled('program') && $request->input('program', 'all') != 'all') {
			$stepsQuery->whereHas('cycle', function ($c) use ($request) { $c->where('id', $request->program); });
		}

		if ($request->filled('judge') && $request->input('judge', 'all') != 'all')
		{
			$stepsQuery->where(function ($q) use ($request) {
				// Check if judge is in the JSON list field
				$q->whereRaw("JSON_CONTAINS(list, ?)", [json_encode($request->judge)])
					->orWhereRaw("JSON_CONTAINS(list, ?)", [json_encode([$request->judge])])
					->orWhereRaw("JSON_CONTAINS(list, ?)", [$request->judge]);
			});
		}

		if ($request->filled('startup') && $request->input('startup', 'all') != 'all') {
			$stepsQuery->whereHas('companies', function ($c) use ($request) { $c->where('id', $request->startup); });
		}

		$steps = $stepsQuery->orderBy('from', 'desc')->get();

		/** -------------------------
		 * Preload all related data
		 * ------------------------- */
		$stepIds   = $steps->pluck('id');
		$cycleIds  = $steps->pluck('cycle_id')->unique();

		$judges = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
				->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
				->where('roles.default_for', 'judger')
				->select('users.id', 'users.first_name_en', 'users.last_name_en')
				->get()
				->keyBy('id');

		$finalscreenings = Finalscreening::whereIn('step_id', $stepIds)
				->get()
				->groupBy('step_id');

		// Source of truth for "Assigned Startups" matches the details page (FinalscreeningController::show):
		// company IDs in screenings.users (written by addJudgingPanels).
		$panelAssignmentsByStep = Screening::whereIn('step_id', $stepIds)
			->whereNull('deleted_at')
			->get(['step_id', 'users'])
			->groupBy('step_id')
			->map(function ($rows) {
				return $rows->flatMap(function ($r) {
					$u = $r->users;
					if (is_string($u)) { $u = json_decode($u, true); }
					return is_array($u) ? $u : [];
				})->map(fn ($id) => intval($id))->unique()->values();
			});

		$allAssignedIds = $panelAssignmentsByStep->flatten()->unique()->values()->all();
		$assignedCompaniesById = !empty($allAssignedIds)
			? Company::whereIn('id', $allAssignedIds)->get()->keyBy('id')
			: collect();

		// The list page mirrors the assignment page: it shows startups that are
		// CURRENTLY at the step (companies.step === step id), consistent with the
		// initial-screening list. A startup assigned to a panel but already
		// advanced to another step is excluded from the count/preview here; its
		// evaluation still appears on the (informative) results/report pages.
		$companies = $panelAssignmentsByStep->map(function ($ids, $stepId) use ($assignedCompaniesById) {
			return $ids->map(fn ($id) => $assignedCompaniesById->get($id))
				->filter()
				->filter(fn ($c) => (int) $c->step === (int) $stepId)
				->values();
		});

		$forms = Step::whereIn('cycle_id', $cycleIds)
				->where('stype', 1)
				->get()
				->keyBy(fn ($f) => "{$f->cycle_id}-{$f->arr}");

		// Org-scoped BuilderForms for the list-page Modify Step form dropdown.
		$orgIds = $steps->pluck('cycle.organization_id')->filter()->unique()->values();
		$formsByOrg = \App\BuilderForm::whereIn('organization_id', $orgIds)->get()->groupBy('organization_id');

		/** -------------------------
		 * Build finalScreeningSteps
		 * ------------------------- */
		$finalScreeningSteps = $steps->map(function ($step) use ( $judges, $finalscreenings, $companies, $forms ) {
			$judgersList = collect(json_decode($step->list, true) ?? [])->map(fn ($j) => is_array($j) ? array_map('intval', $j) : [(int) $j]);

			/** Collect judges for step */
			$judgeIds = $judgersList->flatten()->unique();
			$judgesForStep = $judgeIds
				->map(fn ($id) => $judges->get($id))
				->filter()
				->map(fn ($j) => (object) ['id'   => $j->id, 'name' => "{$j->first_name_en} {$j->last_name_en}",])
				->values();

			/** Companies */
			$companiesForStep = ($companies[$step->id] ?? collect())->map(fn ($c) => (object) ['id'   => $c->id, 'name' => $c->name_en,])->values();

			/** Form */
			$formKey = "{$step->cycle_id}-" . ($step->arr - 1);
			$form	= $forms->get($formKey);

			/** Screening type */
			$screeningType = match ($step->data) {
				'onetoall'	 => 'one-to-all',
				'grouptoall'   => 'group-to-all',
				'grouptogroup' => 'group-to-group',
				default		=> 'one-to-all',
			};

			return (object) [
				'id'			=> $step->id,
				'title'		    => $step->title,
				'description'   => $step->description ?? '',
				'program'	    => $step->cycle ? (object) ['id' => $step->cycle->id, 'title' => $step->cycle->title] : null,
				'organization'  => $step->cycle?->organization ? (object) ['id'   => $step->cycle->organization->id, 'name' => $step->cycle->organization->name, ] : null,
				'start_date'	=> optional($step->from)->format('Y-m-d'),
				'end_date'	    => optional($step->to)->format('Y-m-d'),
				'screening_type'=> $screeningType,
				'judges'		=> $judgesForStep,
				'companies'	    => $companiesForStep,
				'form'		    => $form ? (object) ['id' => $form->id, 'title' => $form->title] : null,
				'form_id'         => $step->form_id,
				'organization_id' => $step->cycle?->organization_id,
				// Founder submissions for this step's current form, so the edit-step
				// modal warns (with the count) before a form change discards them.
				'submissions_count' => $this->stepSubmissionCounts($step)['total'],
			];
		});

		$currentPage = LengthAwarePaginator::resolveCurrentPage();
		$perPage = 9;

		$currentItems = $finalScreeningSteps->slice(($currentPage - 1) * $perPage, $perPage)->values();

		$paginatedFinalSteps = new LengthAwarePaginator(
			$currentItems,
			$finalScreeningSteps->count(),
			$perPage,
			$currentPage,
			[
				'path' => request()->url(),
				'query' => request()->query(),
			]
		);

		return view('admin.settings.final.index', compact('finalScreeningSteps', 'paginatedFinalSteps', 'programs_in_final_screening', 'formsByOrg'));
	}

	public function show($id = 0)
	{
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$steps = Step::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
			return $q->whereHas('cycle', function ($query) use ($orgIds) {
				$query->whereIn('organization_id', $orgIds);
			});
		})->where('stype', 5)->orderBy('from')->get();

		// $judges = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 7)->get();
		$judges = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
			->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
			->where('roles.default_for', 'judger')
			->select('users.*')
			->get();

		if ($id == 0) {
			$id = $steps->pluck('id')->first();
		}

		$cstep = Step::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
			return $q->whereHas('cycle', function ($query) use ($orgIds) {
				$query->whereIn('organization_id', $orgIds);
			});
		})->where('stype', 5)->where('id', $id)->first();

		if (! $cstep) {
			abort(404);
		}

		// Determine the selected form from the step's own pointers. form_id and
		// data are kept in sync by applyFormToStep(): attaching a form sets both,
		// removing a form nulls both. A null/empty form pointer therefore means
		// "no form" — either never configured or explicitly removed — and must be
		// honored. The old position-based fallback to the preceding Custom Form
		// step re-derived a removed form on every reload, so the removal never
		// appeared to persist (GV-3243). data is read as a fallback only for
		// legacy steps that predate the form_id column and still carry the form
		// id in data.
		$formId = $cstep->form_id ?: ($cstep->data ?: null);
		$form = $formId ? \App\BuilderForm::find($formId) : null;

		// Get all available forms for the organization (for the form selection dropdown)
		$forms = \App\BuilderForm::where('organization_id', $cstep->cycle->organization_id)->get();

		$judgers_count = 0;
		$cList = collect(json_decode($cstep?->list, true) ?? []);
		if( in_array( $cstep?->data, ['onetoall', 'grouptoall']) ) { $judgers_count = $cList->count(); }
		else
		{
			$judgers_count = $cList->flatten()->unique()->count();
		}
		$svgs = ['notebook', 'search', 'idea', 'comments', 'trophy'];

		$judgers_list = collect( json_decode($cstep?->list) )->map(function($user) {
			if( is_array( $user ) ) { $user = array_map('intval', $user); } else { $user = intval( $user ); }
			return $user;
		});

		$finalscreenings = collect([]);
		if( $cstep ) { $finalscreenings = Finalscreening::where('step_id', $cstep->id)->get(); }

		foreach( $judgers_list as $panel_id => $judgers_ids )
		{
			// Fetch assigned companies for this panel from the screenings table
			// In screenings table, 'users' column stores company IDs as JSON array
			$panelData = DB::table('screenings')
				->where('step_id', $cstep->id)
				->where('panel', $panel_id)
				->whereNull('deleted_at')
				// ->pluck('users')
				->get()
				->map(function($item) {
					$users = is_array($item->users) ? $item->users : (json_decode($item->users, true) ?: []);
					$session = [
						'checked' => false,
						'date' => $item->session_date,
						'meeting_link' => $item->meeting_link,
						// GV-8365: without this the passcode field rendered empty on
						// every reload, so a saved meeting looked like it had lost its
						// passcode. The value was never actually cleared in the DB.
						'meeting_password' => $item->meeting_password,
						'start_time' => $item->start_time,
						'end_time' => $item->end_time,
						'interval' => $item->interval,
					];
					$session['checked'] = !is_null($item->session_date) && !is_null($item->meeting_link);
					return ['companies' => $users, 'session' => $session];
				})
				// ->flatten()
				// ->unique()
				->values()
				->toArray();

			$panelData = count($panelData) > 0 ? array_values($panelData)[0] : ['companies' => [], 'session' => [] ];
			$judgers_list[$panel_id] = ['judgers' => $judgers_ids, 'companies' => $panelData['companies'], 'session' => $panelData['session'] ];
		}
		$check = ScreeningEvaluation::where('step_id', $id)->first();
		if( empty( $check ) )
		{
			$evaluation = new ScreeningEvaluation();
			$evaluation->step_id = $id;
			$evaluation->save();
		}
		$companies = [];
		$evaluationForm = ScreeningEvaluation::getEvaluationData( $id );
		// Only startups that have actually progressed to THIS step belong here.
		// Including the form id in the step filter compared a builder_forms id
		// against companies.step (a steps FK), so startups sitting at an
		// unrelated step were listed as selectable AND — failing the
		// current-step test below — rendered a second time as "moved (no longer
		// at this step)" (GV-9556). Same rule the initial screening already
		// applies (GV-3237).
		if( $cstep ) { $companies = Company::where('step', $cstep->id)->get(); } else { $cstep = []; }

		/** Prepare Judging Panels for View (Clean Code) */
		$judgingPanels = [];
		if ($cstep && isset($judgers_list) && count($judgers_list) > 0) {
			foreach ($judgers_list as $panelId => $panelData) {
				// Same History rule as the initial screening (GV-8300): once a
				// panel's meeting has ended it is read-only. The flag was only
				// ever computed on the initial-screening path, so a finished
				// FINAL panel stayed editable and never showed the badge — the
				// "status change is delayed" in GV-9568. Derived from the saved
				// row, not request input, so it cannot be spoofed.
				$screening = Screening::where('step_id', $cstep->id)->where('panel', $panelId)->first();
				$panelEntry = [
					'judges' => (array) ($panelData['judgers'] ?? []),
					'companies' => (array) ($panelData['companies'] ?? []),
					'is_history' => PanelWindowService::isPast(
						PanelWindowService::fromScreening($screening),
						auth()->user()
					),
					// The panel number this record is STORED under. Deleting an
					// earlier panel renumbers the accordion, so the loop index is
					// not identity — carry the real number through.
					'panel_number' => (int) $panelId,
					'session' => [
						'checked' => $panelData['session']['checked'] ?? false,
						'date' => $panelData['session']['date'] ?? null,
						'meeting_link' => $panelData['session']['meeting_link'] ?? null,
						// GV-8365: carry the saved passcode through to the view.
						'meeting_password' => $panelData['session']['meeting_password'] ?? null,
						'start_time' => $panelData['session']['start_time'] ?? null,
						'end_time' => $panelData['session']['end_time'] ?? null,
						// Interval mode must survive a reload: without these the
						// blade always rendered the switch OFF (GV-3295).
						'interval' => $panelData['session']['interval'] ?? null,
						'interval_checked' => !empty($panelData['session']['interval']),
					]
				];
				$judgingPanels[] = $panelEntry;
			}
		}

		if (empty($judgingPanels)) {
			$judgingPanels = [['judges' => [], 'companies' => [], 'session' => []]];
		}

		/** Collect assigned judge/company IDs from panels only (not all companies in the step) */
		$assignedJudgeIds = collect($judgingPanels)->pluck('judges')->flatten()->unique()->filter()->values();
		$assignedCompanyIds = collect($judgingPanels)->pluck('companies')->flatten()->unique()->filter()->values();

		// The "Assigned Startups" header reflects startups CURRENTLY at this step,
		// consistent with the list page and the initial-screening behavior. A
		// startup that was assigned to a panel but has since been advanced to
		// another step is dropped here (its evaluation still shows on the results
		// page). Keep it as the cstep's current companies intersected with the
		// panel assignments.
		// Derived from $companies itself so the selectable list and the off-step
		// list can never disagree about the same startup (GV-9556).
		$currentStepCompanyIds = collect($companies)->pluck('id')->map(fn ($i) => (int) $i)->all();
		$allPanelCompanyIds = collect($judgingPanels)->pluck('companies')->flatten()->map(fn ($i) => (int) $i)->filter()->unique()->values();
		$assignedCompanyIds = $allPanelCompanyIds->intersect($currentStepCompanyIds)->values();

		// Off-step references: startups already saved into this step's panels that
		// have since moved to another step. They are shown in their panel as
		// read-only reference chips (cannot be added/removed until they return to
		// this step). Provide id+name so the partial can render their option.
		$offStepCompanyIds = $allPanelCompanyIds->reject(fn ($i) => in_array($i, $currentStepCompanyIds, true))->values();
		$offStepCompanies = $offStepCompanyIds->isNotEmpty()
			? Company::whereIn('id', $offStepCompanyIds)->get()
			: collect();

		// Founder submissions tied to this step's current form. Used by the
		// edit-step modal to warn (with the count) before a form change/removal
		// discards them — the same form-de-attach rule as the inline selector.
		$stepFormSubmissions = $cstep ? $this->stepSubmissionCounts($cstep)['total'] : 0;

		return view('admin.settings.final.show', compact('cstep', 'judgers_list', 'judgers_count', 'companies', 'judges', 'form', 'forms', 'steps', 'id', 'svgs', 'evaluationForm', 'judgingPanels', 'assignedJudgeIds', 'assignedCompanyIds', 'offStepCompanies', 'stepFormSubmissions'));
	}

	public function update(Request $request)
	{
		if ($request->step) {
			$list = $request->judges;
		   // dd();
			$companies = $request->users;
			
			if (!$companies or !$list) {return Redirect::back()->with('message2', 'You need to select judges and companies to update');}
			foreach ($companies as $company) {
				Finalscreening::where('company_id',$company)->where('step_id', $request->step)->delete();
				Finalscreening::insert(['company_id'=>$company,'step_id'=>$request->step, 'judges'=>json_encode($list)]);

			}
			foreach ($list as $judge) {
				
				$result = User::findOrFail($judge);
				$name = $result->first_name_en . ' ' . $result->last_name_en;
				$email = $result->email;
				
				$data = [
					'role'=>'Judger',
					'Type' => 'Final_Screening',
					'name' => $name,
					'email' => $email,
					'start_date' => '',
					'end_date' => '',
				];
				// إرسال الإشعار
				if( !in_array(env('APP_ENV', 'local'), ['local']) )
				{
					// Disabled for now: this is not an account-creation event. A
					// separate "assigned to screening" email will replace it.
					// try {
					// 	$result->notify(new WelcomeJudgerNotification($data));
					// } catch (\Throwable $th) {
					// 	//throw $th;
					// }
				}
			}
			


			return Redirect::back()->with('message', 'Saved');
		} else {
			return Redirect::back()->with('message2', 'error');
		}
	}

	public function view($sid, $cid)
	{
		return abort(404);
		// Insure company belong to Judger first
		$myid = Auth::id();
		$fs = Finalscreening::where('company_id',$cid)->where('step_id',$sid)->where('judges', 'like', "%\"{$myid}\"%")->get();
		if (!$fs) { return Redirect::back()->with('message2', 'error');}
		// Check step
		$step = Step::findOrFail($sid);
		if (!$step) {return Redirect::back()->with('message2', 'error');}
		$field_map_ids = Formfield::where('step_id',$sid)->where('builder_forms_id', $step->data)->orderBy('id')->select('id')->pluck('id')->toArray();
		$data = [
			'title' => $step->title,
			'fields' => $step->fields()->where('builder_forms_id', $step->data)->orderBy('form_fields.id', 'asc')->get(),
			'form_id' => $step->id,
			'field_ids' => implode(",", $field_map_ids)
		];
		$answers = Formfield::join('final_data', 'form_fields.id', 'final_data.field_id')->where('judge_id',$myid)->
		where('company_id',$cid)->orderBy('form_fields.id', 'ASC')->select('answer')->pluck('answer')->toArray();
		$company = Company::findOrFail($cid);
		return view('admin.settings.final.form', $data)->with('step',$step)->with('company', $company)->with('answers',$answers);
	}

	public function save(Request $request)
	{
		return abort(404);
		$id = $request->id;
		$validator = Validator::make($request->all(), [
			'id' => 'required|integer|exists:steps,id',
			'field_ids' => 'required|string',
		]);
		abort_if($validator->fails(), 422, "Data error");
		// generating validation rules based on dynamic field config data
		$field_map_ids = explode(",", $request->input('field_ids'));
		$field_required_rules = collect($field_map_ids)->mapWithKeys(function($id){
			$field_options = Formfield::findOrFail($id)->options;
			if($field_options->validation->required == 1) {
				$rules = ['required'];
				if(isset($field_options->type) && $field_options->type == "email") $rules[] = 'email';
				return [
					'field_'. $id => implode("|", $rules)
				];
			}
			else {
				return ['field_'. $id => 'nullable'];
			}
		});
		$dynamic_validator = Validator::make($request->all(), $field_required_rules->toArray(), [
			'required' => "field can't be left blank",
			'email' => 'field must be a valid email address'
		]);
		if ($dynamic_validator->fails()) {
			return redirect()->back()
				->withErrors($dynamic_validator)
				->withInput();
		}
		// gather the form data as field_name => [ label, data ]
		$uid = Auth::id();
		$cid = $request->cid;
		foreach($field_map_ids as $fid){
			$field_options = Formfield::findOrFail($fid)->options;
			$field_name = 'field_'. $fid;
			FinalData::where('field_id',$fid)->where('judge_id',$uid)->where('company_id',$cid)->delete();
			FinalData::updateOrCreate(['field_id'=>$fid, 'judge_id'=>$uid, 'company_id'=>$cid, 'step_id'=>$id, 'question'=>$field_options->label,'answer'=>$request->input($field_name)]);
		}
		return Redirect::back()->with(['msg' => 'StoredSuccessfully','class'=>'success']);
	}

	public function addzoom(Request $request)
	{
		$request->validate([
			'date' => 'required|date',
			'period' => 'required|integer|min:30'
		],[
			'date.required' => __('admin.Date_is_required'),
			'date.date' => __('admin.Date_must_be_a_valid_date'),
			'period.required' => __('admin.Period_is_required'),
			'period.integer' => __('admin.Period_must_be_an_integer'),
			'period.min' => __('admin.Period_must_be_at_least_30_minutes'),
		]);
		if ($request->period) {
			$topic = "Final Screening (C" . $request->company . ')';
			$data = ['topic' => $topic,
			 "start_time" => $request->date,
			  'duration' => $request->period,
			   'host_video' => 1,
				'participant_video' => 1
			];
			
			$onlineMeeting = $this->meetingService->createMeeting($data);
			if (!$onlineMeeting['success']) {
				return back()->with(['message2' => "API Error, can't create zoom meeting."]);
			}
			
			$finalScreening = Finalscreening::where('step_id', $request->step)->where('company_id', $request->company)->first();
			
			$finalScreening->update([
				'datetime' => $request->date,
				'duration' => $request->period,
				'zoom_id' => $onlineMeeting['data']['id'],
				'start_url' =>  $onlineMeeting['data']['start_url'],
				'join_url' =>  $onlineMeeting['data']['join_url'],
				'zoom_passcode' => $onlineMeeting['data']['passcode'] ?? null,
			]);
				
			$startup_owner = Company::findOrFail($request->company)->user;
			$startup_owner_name = $startup_owner->name;
				$start_date = $finalScreening->datetime;
				$duration = $request->period;
				$end_dateCarbon = Carbon::parse($start_date)->addMinutes($duration);
				$end_date = $end_dateCarbon->toDateTimeString(); // Format: 2025-02-10 13:00:00

			$judges_names = '';
			foreach ($finalScreening->judges as $judge) {

				// $judge_name = $judge->name;
				// $user = new User();
				// $user->email = $judge->email;
				// $user->id = $judge->id;

				// $MSG = new TemplateEmail;
				// $MSG->Message=["New Final Screen session - VI portal","Dear ".$judge_name.",","Kindly note you have a New Final Screen session with '".$startup_owner_name."', Session will start:".$request->date.
				//	 ", Check your account to join the session ","/dashboard"];

				// $user->notify($MSG);
				// $judges_names .= $judge->name .', ';

				$result = User::findOrFail($judge);
				$name = $result->first_name_en . ' ' . $result->last_name_en;
				$email = $result->email;
				
				$data = [
					   'role'=>'Judger',
					'Type' => 'Final_Screening',
					'name' => $name,
					'email' => $email,
					'start_date' => $start_date,
					'end_date' => $end_date,
				];
				// إرسال الإشعار
				if( !in_array(env('APP_ENV', 'local'), ['local']) )
				{
					// Disabled for now: this is not an account-creation event. A
					// separate "assigned to screening" email will replace it.
					// try {
					// 	$result->notify(new WelcomeJudgerNotification($data));
					// } catch (\Throwable $th) {
					// 	// throw $th;
					// }
				}
			}
			
			
			/// Notify Startup Owner
			$startup_owner = User::findOrFail($startup_owner->id);
			
			$startup_owner_name = $startup_owner->name;
			
			$user = new User();
			$user->email = $startup_owner->email;
			$user->id = $startup_owner->id;
			$MSG = new TemplateEmail;
			$MSG->Message=["New Final Screen session - VI portal","Dear ".$startup_owner_name.",","Kindly note you have a New Final Screen session with '".$judges_names."', Session will start:".$request->date.
				", Check your account to join the session ","/dashboard"];
			if( !in_array(env('APP_ENV', 'local'), ['local']) || env('APP_NAME', 'incubator') != "BIAC" )
			{
				try {
					$user->notify($MSG);
				} catch (\Throwable $th) {
					// throw $th;
				}
			}
			/// End of mail send
			
			return Redirect::back()->with('message', 'Meeting_added_successfully');
		} else {return Redirect::back()->with('message2', 'Error');}
	}

	/**
	 * Upload image for custom page content
	 * 
	 * @param UploadScreeningGuidelinesImageRequest $request
	 * @param int $id
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function uploadImage(UploadScreeningGuidelinesImageRequest $request, $id)
	{
		// Verify Step ID matches route parameter
		$user = auth()->user();
		if ($id != $request->step_id ) {
			return response()->json([
				'status' => false,
				'message' => __('cycles.invalid_step_for_upload')
			], 403);
		}
		if( !$user || !$user->ableToAny(['screening.update', 'screening.store']) )
		{
			return response()->json([
				'status' => false,
				'message' => __('cycles.guidelines_permissions')
			], 403);
		}

		try {
			$image = $request->file('image');
			$stepId = $request->step_id;

			// Generate unique filename: timestamp_cycleId_randomString.ext
			$filename = time() . '_' . $stepId . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

			// Store in cycle-specific subfolder
			$path = "{$stepId}/{$filename}";
			Storage::disk('screening_guidelines')->put($path, file_get_contents($image));

			// Optional: Resize image to optimize storage (using Intervention Image)
			$fullPath = Storage::disk('screening_guidelines')->path($path);
			if (class_exists('\Intervention\Image\Facades\Image')) {
				\Intervention\Image\Facades\Image::make($fullPath)
					->resize(1200, null, function ($constraint) {
						$constraint->aspectRatio();
						$constraint->upsize();
					})
					->save();
			}

			// Generate URL for image access
			$url = route('screening.guidelines.image', [
				'step_id' => $stepId,
				'filename' => $filename
			]);

			return response()->json([
				'status' => true,
				'url' => $url,
				'message' => __('admin.Image uploaded successfully')
			]);

		} catch (\Exception $e) {
			\Log::error('Screening guidelines image upload failed: ' . $e->getMessage());

			return response()->json([
				'status' => false,
				'message' => __('admin.Image upload failed')
			], 500);
		}
	}

	/**
	 * Serve uploaded image with access control
	 *
	 * @param Request $request
	 * @param int $step_id
	 * @param string $filename
	 * @return \Illuminate\Http\Response
	 */
	public function serveImage(Request $request, $step_id, $filename)
	{
		$user = auth()->user();

		// Verify cycle exists and user has access
		$step = Step::findOrFail($step_id);

		// Build file path
		$path = "{$step_id}/{$filename}";
		$disk = Storage::disk('screening_guidelines');

		// Check if file exists
		if (!$disk->exists($path)) {
			abort(404, __('admin.Image not found'));
		}

		// Get file contents and MIME type
		$file = $disk->get($path);
		$mimeType = $disk->mimeType($path);

		// Return file response
		return response($file, 200)->header('Content-Type', $mimeType);
	}

	public function results(Request $request, $id = 0)
	{
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$steps = Step::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
			return $q->whereHas('cycle', function ($query) use ($orgIds) {
				$query->whereIn('organization_id', $orgIds);
			});
		})->where('stype', 5)->orderBy('from')->get();

		if ($id == 0) {
			$id = $steps->pluck('id')->first();
			$cstep = Step::where('stype', 5)->where('id', $id)->first();
		} else {
			$cstep = Step::where('stype', 5)->where('id', $id)->first();
		}
		$companies = Company::where('step',$id)->get();
		$c =  $cj = null;
		if ($request->c) {
			$c = Company::findOrFail($request->c);
			$cj = FinalData::where('company_id',$c->id)->where('step_id', $id)->distinct('judge_id')->get(['judge_id']);
		}
		$check = ScreeningEvaluation::where('step_id', $id)->first();
		if( empty( $check ) )
		{
			$evaluation = new ScreeningEvaluation();
			$evaluation->step_id = $id;
			$evaluation->save();
		}
		$evaluationForm = ScreeningEvaluation::getEvaluationData( $id );
		// Results page is informative: include every startup belonging to this
		// final step's evaluation regardless of current step. metadata() already
		// returns the widened set, so the old ->where('step', $id) filters (which
		// dropped startups advanced past the step) are removed.
		$metadata = ScreeningEvaluationResults::metadata( $id, null, null, true );
		$companies = $metadata['companies']->values();
		$_steps = $metadata['_steps'];
		$judges = $metadata['judges'];
		$evaluation = $metadata['evaluation'];
		$results = $metadata['results'];
		$_companies = $metadata['_companies']->values();

		$judges = $judges->map(function( $item ) {
			$item->companies_ids = collect($item->companies)->pluck('id')->toArray();
			return $item;
		});
		$evaluations_data = ScreeningEvaluationResults::results( $id, $_companies->pluck('id')->toArray() );
		$_companies = $_companies->map(function( $company ) use ( $evaluations_data ) {
		   $_results =  $evaluations_data[$company->id] ?? [
			   'total' => 0,
			   'criteria' => collect(),
			   'judges' => collect()
			];
			$company->results = $_results;
			return $company;
		});
		return view('admin.settings.final.results', compact('cstep', 'judges', 'steps', '_steps', 'id', '_companies', 'companies', 'evaluation', 'results', 'c', 'cj', ));
	}

	public function delsession($id, Request $request)
	{
		// $zoomid = TrainingSession::findOrFail($id)->zoom_id;
		if($request)
		{
			// if ($zoomid)
			// {
			//	 $zoom = $this->delete($zoomid);
			//	 if (!$zoom['success']) { return response()->json(['state' => 'failed'], 500); }
			// }
			Finalscreening::findOrFail($id)->delete();
			return response()->json(['state' => 'success'], 200);
		} else {
			return response()->json(['state' => 'failed'], 500);
		}
	}

	/**
	 * Export all companies evaluation results to Excel
	 */
	public function exportResults($id)
	{
		$user = auth()->user();
		if (!$user->hasRole('Admin')) {
			$orgIds = $user->organizations_ids;
			$step = \App\Step::with('cycle')->find($id);
			if (!$step || !$step->cycle || !in_array($step->cycle->organization_id, $orgIds)) {
				abort(403, 'Unauthorized access to this evaluation.');
			}
		}

		// Export mirrors the results page: every startup belonging to this final
		// step's evaluation regardless of current step.
		$metadata = ScreeningEvaluationResults::metadata($id, null, null, true);
		$_companies = $metadata['_companies']->values();
		$evaluation = $metadata['evaluation'];

		if ($_companies->isEmpty()) {
			return back()->with('message2', 'No companies found to export');
		}

		$evaluations_data = ScreeningEvaluationResults::results($id, $_companies->pluck('id')->toArray());

		// Get criteria structure from first company's data
		$firstCompanyData = $evaluations_data[$_companies->first()->id] ?? null;
		$criteria = $firstCompanyData ? $firstCompanyData['criteria'] : collect();

		if (empty($criteria) || (is_countable($criteria) && count($criteria) === 0)) {
			return back()->with('message2', 'No evaluation criteria found');
		}

		$export = new ScreeningEvaluationExport(
			$evaluations_data,
			$_companies,
			$criteria,
			'final',
			true // includeCompanyColumn
		);

		$stepName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $evaluation->step->title ?? 'final_screening');
		$filename = "final_screening_results_{$stepName}_" . now()->format('Y-m-d_His') . ".xlsx";

		return Excel::download($export, $filename);
	}

	/**
	 * Export single company evaluation results to Excel
	 */
	public function exportCompanyResults($id, $company_id)
	{
		$user = auth()->user();
		if (!$user->hasRole('Admin')) {
			$orgIds = $user->organizations_ids;
			$step = \App\Step::with('cycle')->find($id);
			if (!$step || !$step->cycle || !in_array($step->cycle->organization_id, $orgIds)) {
				abort(403, 'Unauthorized access to this evaluation.');
			}
		}

		$company = Company::find($company_id);

		if (!$company) {
			return back()->with('message2', 'Company not found');
		}

		$evaluations_data = ScreeningEvaluationResults::results($id, [$company_id]);
		$companyData = $evaluations_data[$company_id] ?? null;

		if (!$companyData) {
			return back()->with('message2', 'No evaluation data found for this company');
		}

		$export = new ScreeningEvaluationExport(
			[$company_id => $companyData],
			collect([$company]),
			$companyData['criteria'],
			'final',
			false // no company column for single export
		);

		$companyName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $company->name);
		$filename = "final_screening_{$companyName}_" . now()->format('Y-m-d_His') . ".xlsx";

		return Excel::download($export, $filename);
	}
}
