<?php

namespace App\Http\Controllers;

use App\Company;
use App\Finalscreening;
use App\Events\ChangeStep;
use App\Events\JudgeAssigned;
use App\Exports\ScreeningEvaluationExport;
use App\Formdata;
use App\Formfield;
use App\Jobs\ExportResScreenData;
use App\Jobs\ExportScreeneData;
use App\Jobs\SendChangeCycleNotificationJob;
use App\Notifications\ChangeCycle;
use App\Notifications\JudgeAssignedToScreeningNotification;
use App\Notifications\StartupMeetingTimeChangedNotification;
use App\Notifications\TemplateEmail;
use App\Screening;
use App\ScreeningEvaluation;
use App\ScreeningEvaluationCriteria;
use App\ScreeningEvaluationCustomFields;
use App\ScreeningEvaluationFieldsValues;
use App\ScreeningEvaluationResults;
use App\Factories\OnlineMeetingFactory;
use App\Services\JudgeScheduleConflictService;
use App\Services\PanelWindowService;
use App\Services\ScreeningSlotService;
use App\Services\WhatsAppService;
use App\Step;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use stdClass;

class ScreeningController extends Controller
{
    use \App\Traits\ManagesStepForm;

	protected $whatsAppService;
	public function __construct(WhatsAppService $whatsAppService)
	{
		$this->whatsAppService = $whatsAppService;
	}
	
	public function index(Request $request)
	{
		$user = auth()->user();
		$orgIds = $user->organizations_ids;

		/** -------------------------
		 * Base steps query with filters
		 * ------------------------- */
		$stepsQuery = Step::with(['cycle', 'cycle.organization'])->when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
			$q->whereHas('cycle', fn ($c) => $c->whereIn('organization_id', $orgIds) );
		})->where('stype', 2);

		$programs_in_screening = $stepsQuery->get();

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

		if( $request->filled('screening_type') )
		{
			$screeningType = $request->screening_type;
			$stepsQuery->where('data', $screeningType);
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
			$stepsQuery->where('cycle_id', $request->program);
		}

		if ($request->filled('judge') && $request->input('judge', 'all') != 'all') {
			$judgeId = $request->judge;
			$stepsQuery->whereHas('screenings', function ($q) use ($judgeId) {
				$q->where('user_id', $judgeId);
			});
		}	

		if ($request->filled('startup') && $request->input('startup', 'all') != 'all')
		{
			$stepsQuery->whereHas('companies', function ($c) use ($request) { $c->where('id', $request->startup); });
		}

		$steps = $stepsQuery->orderBy('from', 'DESC')->get();

		/** -------------------------
		 * Preload all related data
		 * ------------------------- */
		$stepIds   = $steps->pluck('id');

		$judges = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
				->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
				->where('roles.default_for', 'judger')
				->select('users.id', 'users.first_name_en', 'users.last_name_en')
				->get()
				->keyBy('id');

		$screenings = Screening::whereIn('step_id', $stepIds)
				->get()
				->groupBy('step_id');

		$companies = Company::whereIn('step', $stepIds)
				->get()
				->groupBy('step');

		// Resolve each step's form from its OWN pointer, mirroring show():
		// form_id is the source of truth, data is a legacy fallback. Preload the
		// referenced BuilderForms keyed by id to avoid an N+1 in the map below.
		// The previous key-by-"cycle_id-stype-form_id" collided across steps and
		// then ran ->where()->first() on a loaded model, which queried the whole
		// builder_forms table and returned the org's first form regardless of the
		// step's actual form (GV form mismatch on the screening list).
		$formIds = $steps->map(fn ($s) => $s->form_id ?: (is_numeric($s->data) ? $s->data : null))
				->filter()
				->unique();
		$forms = \App\BuilderForm::whereIn('id', $formIds)->get()->keyBy('id');

		/** -------------------------
		 * Build screeningSteps
		 * ------------------------- */
		$screeningSteps = $steps->map(function ($step) use ($judges, $screenings, $companies, $forms ) {
			$judgersList = collect(json_decode($step->list, true) ?? [])->map(fn ($j) => is_array($j) ? array_map('intval', $j) : [(int) $j]);
			/** Collect judges for step */
			$judgeIds = $judgersList->flatten()->unique();
			$judgesForStep = $judgeIds
				->map(fn ($id) => $judges->get($id))
				->filter()
				->map(fn ($j) => (object) ['id'   => $j->id, 'name' => "{$j->first_name_en} {$j->last_name_en}",])
				->values();

			/** Companies */
			$companiesForStep = ($companies[$step->id] ?? collect())
				->map(fn ($c) => (object) ['id'   => $c->id, 'name' => $c->name_en,])
				->values();

			/** Form — read straight from the step's own pointer, same as show().
			 * Only surface the form when it belongs to the cycle's organization:
			 * the manage-page dropdown is org-scoped, so a cross-org form there
			 * shows "Select Form" (nothing selected). Guarding here keeps the list
			 * consistent with the manage page instead of showing a form the manage
			 * page can't select. */
			$formId      = $step->form_id ?: (is_numeric($step->data) ? $step->data : null);
			$formBuilder = $formId ? $forms->get($formId) : null;
			if ($formBuilder && $formBuilder->organization_id != $step->cycle?->organization_id) {
				$formBuilder = null;
			}

			/** Screening type */
			$screeningType = match ($step->data) {
				'onetoall'     => 'one-to-all',
				'grouptoall'   => 'group-to-all',
				'grouptogroup' => 'group-to-group',
				default        => 'one-to-all',
			};

			return (object) [
				'id'            => $step->id,
				'title'         => $step->title,
				'description'   => $step->description ?? '',
				'program'       => $step->cycle ? (object) ['id' => $step->cycle->id, 'title' => $step->cycle->title] : null,
				'organization'  => $step->cycle?->organization ? (object) ['id'   => $step->cycle->organization->id, 'name' => $step->cycle->organization->name ] : null,
				'start_date'    => optional($step->from)->format('Y-m-d'),
				'end_date'      => optional($step->to)->format('Y-m-d'),
				'screening_type'       => $screeningType,
				'screening_type_value' => $step->data,
				'judges'        => $judgesForStep,
				'companies'     => $companiesForStep,
				'form'          => $formBuilder ? (object) ['id' => $formBuilder->id, 'title' => $formBuilder?->form_name ?? '-'] : null,
			];
		});

		$currentPage = LengthAwarePaginator::resolveCurrentPage();
		$perPage = 9;

		$currentItems = $screeningSteps->slice(($currentPage - 1) * $perPage, $perPage)->values();

		$paginatedSteps = new LengthAwarePaginator(
			$currentItems,
			$screeningSteps->count(),
			$perPage,
			$currentPage,
			[
				'path' => request()->url(),
				'query' => request()->query(),
			]
		);

		return view('admin.settings.screening.index', compact('paginatedSteps', 'steps', 'screeningSteps', 'programs_in_screening'));
	}

	public function show($id = 0)
	{
		$user = auth()->user();
		$orgIds = $user->organizations_ids;
		$steps = Step::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
			return $q->whereHas('cycle', function ($query) use ($orgIds) {
				$query->whereIn('organization_id', $orgIds);
			});
		})->where('stype', 2)->orderBy('from')->get();
		if( $id == 0 || $id == 'results' ) { $id = $steps->first()?->id ?? 0; }
		// $judges = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 7)->get();
		$judges = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
			->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
			->where('roles.default_for', 'judger')
			->select('users.*')
			->get();

		if ($id == 0) {
			$id = $steps->pluck('id')->first();
			$cstep = Step::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->whereHas('cycle', function ($query) use ($orgIds) {
					$query->whereIn('organization_id', $orgIds);
				});
			})->where('stype', 2)->where('id', $id)->first();
		} else {
			$cstep = Step::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->whereHas('cycle', function ($query) use ($orgIds) {
					$query->whereIn('organization_id', $orgIds);
				});
			})->where('stype', 2)->where('id', $id)->first();
		}
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
	
		$screenings = collect([]);
		if( $cstep ) { $screenings = Screening::where('step_id', $cstep->id)->get(); }

		foreach( $judgers_list as $panel_id => $judgers_ids )
		{
			$users_list = $screenings->where('panel', $panel_id)->map(function($screening) {
				if( is_array( $screening->users ) ) { return $screening->users; }
				else { return collect(json_decode($screening->users, true) ?? []); }
			})->flatten()->unique()->values()->all();

			$judgers_list[$panel_id] = ['judgers' => $judgers_ids, 'companies' => $users_list ];
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

		if( $cstep ) {
			// Only startups that have actually progressed to THIS screening step
			// belong here. Including the preceding (Custom Form) step's companies
			// made startups still sitting in the Custom Form step appear in the
			// Screening phase before they were advanced (GV-3237). A startup must
			// be moved to the screening step (via "change step") before it can be
			// assigned to judges.
			$companies = Company::where('step', $cstep->id)->get();
		} else {
			$cstep = [];
		}

		/** Prepare Judging Panels for View */
		$judgingPanels = [];
		if ($cstep && isset($judgers_list) && count($judgers_list) > 0) {
			foreach ($judgers_list as $panelId => $panelData) {
				$screening = \App\Screening::where('step_id', $cstep->id)->where('panel', $panelId)->first();
				$panelEntry = [
					'judges' => (array) ($panelData['judgers'] ?? []),
					'companies' => (array) ($panelData['companies'] ?? []),
					'session' => [],
					// A panel whose meeting has finished becomes History: read-only,
					// preserved as a record of what actually took place. Derived from
					// the saved row (not request input), so it cannot be spoofed.
					'is_history' => PanelWindowService::isPast(
						PanelWindowService::fromScreening($screening),
						auth()->user()
					),
					// The panel number this record is STORED under. The view's loop
					// index is not the same thing once panels have been deleted, so
					// the real number is carried through rather than recomputed.
					'panel_number' => (int) $panelId,
				];
				// GV-8365: gated on session_date alone, a panel whose meeting was
				// created without a date still holds a passcode/link that must be
				// rendered — otherwise a reload looks like the passcode was cleared.
				if ($screening && ($screening->session_date || $screening->meeting_link || $screening->meeting_password)) {
					$panelEntry['session'] = [
						'checked' => !is_null($screening->session_date) && !is_null($screening->meeting_link),
						'date' => $screening->session_date,
						'start_time' => $screening->start_time ? substr($screening->start_time, 0, 5) : '',
						'end_time' => $screening->end_time ? substr($screening->end_time, 0, 5) : '',
						'meeting_link' => $screening->meeting_link,
						'meeting_password' => $screening->meeting_password,
						// Interval mode must survive a reload: without these the
						// blade always rendered the switch OFF (GV-3295).
						'interval' => $screening->interval,
						'interval_checked' => !empty($screening->interval),
					];
				}
				$judgingPanels[] = $panelEntry;
			}
		}

		if (empty($judgingPanels)) {
			$judgingPanels = [['judges' => [], 'companies' => [], 'session' => []]];
		}

		/** Collect assigned judge/company IDs from panels only */
		$assignedJudgeIds = collect($judgingPanels)->pluck('judges')->flatten()->unique()->filter()->values();
		$assignedCompanyIds = collect($judgingPanels)->pluck('companies')->flatten()->unique()->filter()->values();

		// Off-step references: startups already saved into this step's panels that
		// have since moved to another step. Shown as read-only reference chips in
		// the panel (not addable/removable until they return to this step).
		$currentStepCompanyIds = $companies->pluck('id')->map(fn ($i) => (int) $i)->all();
		$offStepCompanyIds = collect($judgingPanels)->pluck('companies')->flatten()
			->map(fn ($i) => (int) $i)->filter()->unique()
			->reject(fn ($i) => in_array($i, $currentStepCompanyIds, true))->values();
		$offStepCompanies = $offStepCompanyIds->isNotEmpty()
			? Company::whereIn('id', $offStepCompanyIds)->get()
			: collect();

		return view('admin.settings.screening.show', compact('cstep', 'judgers_list', 'judgers_count', 'companies', 'judges', 'form', 'forms', 'steps', 'id', 'svgs', 'evaluationForm', 'judgingPanels', 'assignedJudgeIds', 'assignedCompanyIds', 'offStepCompanies'));
	}

	public function updateStepForm(Request $request)
	{
		$request->validate([
			'step_id' => 'required|exists:steps,id',
			'form_id' => 'nullable|exists:builder_forms,id',
			'confirm' => 'nullable|boolean',
		]);

		$step = Step::findOrFail($request->step_id);
		$result = $this->applyFormToStep($step, $request->form_id, (bool) $request->boolean('confirm'));

		if ($result['status'] === 'needs_confirmation') {
			return response()->json([
				'success'            => false,
				'needs_confirmation' => true,
				'counts'             => $result['counts'],
				'message'            => __('admin.step_form_change_has_submissions'),
			]);
		}

		return response()->json([
			'success' => true,
			'message' => __('admin.form_updated_successfully'),
		]);
	}

	public function update(Request $request)
	{
		$judgesInput = $request->input('judges', []);

		// Decode judges input if it's a JSON string
		if( is_string( $judgesInput ) ) { $judgesInput = json_decode($judgesInput, true) ?? []; }

		$judges = Arr::flatten($judgesInput);
		$judges = array_values( array_map('intval', array_unique($judges)) );

		if( empty( $judges ) ) { return Redirect::back()->with('message2', __('admin.Please_Choose_Judges')); }

		if( $request->step )
		{
			$type = Step::where('id', $request->step)->pluck('data')->first();
			$notifylist = User::whereIn('id', $judges)->get();

			$judgesInput = collect($judgesInput)->map(function($panel) {
				if( is_array( $panel ) ) { $panel = array_values( array_map('intval', array_unique($panel)) ); } else { $panel = intval($panel); }
				return $panel;
			})->toArray();

			$list = ($type === 'grouptogroup') ? $judgesInput : $judges;

			// if (is_string($list)) { $list = json_decode($list, true) ?? []; }

			Screening::where('step_id', $request->step)->delete();
			Formdata::where('form_id', $request->fid)->update(['step_id' => $request->step, 'screening' => '{}', 'comments' => '{}']);

			if( $list )
			{
				if ($type === 'grouptogroup')
				{
					$users = collect( $request->users )->map(function($user) {
						$user = array_values( array_map('intval', array_unique($user)) );
						return $user;
					})->toArray();

					$_users = [];
					$_list = [];
					foreach ($users as $panel_id => $user)
					{
						if( count( $user ) && array_key_exists( $panel_id, $list ) )
						{
							$_users[$panel_id] = $user;
							$_list[$panel_id] = $list[$panel_id];
						}
					}
					$users = $_users;
					$list = $_list;

					$index = 1;
					$toCreate = [];
					$updatedList = [];
					foreach( $list as $panel_id => $panel )
					{
						foreach( (array) $panel as $user )
						{
							$users_list = $users[$panel_id] ?? [];
							$oneUser = ['step_id' => intval($request->step), 'user_id' => intval($user), 'panel' => $index];
							if( count( $users_list ) ) { $oneUser['users'] = json_encode( $users_list ); }
							$toCreate[] = $oneUser;
						}
						$updatedList[$index] = $panel;
						$index++;
					}
					Screening::insert( $toCreate );
					Step::where('id', $request->step)->update(['list' => json_encode($updatedList)]);
				}
				else
				{
					foreach( (array) $judges as $user ) { Screening::updateOrCreate(['step_id' => $request->step, 'user_id' => $user, 'panel' => 0]); }
					Step::where('id', $request->step)->update(['list' => json_encode($list)]);
				}

				// Notify users via Mail if notify is enabled
				if( $request->notify )
				{
					$cstep = Step::findOrFail($request->step);
					foreach( $notifylist as $rname )
					{
						$user = new User();
						$user->email = $rname->email;
						$user->id = $rname->id;
						$rfullname = $rname->first_name_en . " " . $rname->last_name_en;
						$message = [
						"New judging - VI portal", 
						"Dear " . $rfullname . ",", 
						"Kindly note that we need your experiences to judge New Incubation Cycle registered users, the judging period starting from " 
						. $cstep?->from->format('d-m-Y') . " to " . $cstep?->to->format('d-m-Y') . ", Check your account for more details.",
						"/dashboard", 
						"You have a New Judging Request." 
						];
						$MSG = new TemplateEmail($message);
						broadcast(new JudgeAssigned(
							$rname->id,
							"Kindly note that we need your experiences to judge New Incubation Cycle registered users, the judging period is from " . $cstep?->from->format('d-m-Y') . " to " . $cstep?->to->format('d-m-Y') . ".",
							"/dashboard"
						));
						if (!in_array(env('APP_ENV', 'local'), ['local'])) {
							try {
								$user->notify($MSG);
							} catch (\Throwable $th) {
								// Log error
							}

							$phoneNumber = $rname->country_code_w_a . $rname->whatsapp;
							$Massage = "Dear " . $rfullname . ",\n";
							$Massage .= "we need your experiences to judge New Incubation Cycle registered users, the judging period starting from " . $cstep?->from->format('d-m-Y') . "\n";
							$Massage .= " to " . $cstep?->to->format('d-m-Y') . ", Check your account for more details, \n";
							$Massage .= "Thanks from " . env('APP_NAME');
							$result = $this->whatsAppService->sendMessage($phoneNumber, $Massage);
							Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
						}
					}
				}
			}
			return Redirect::back()->with('message', 'Saved');
		} else { return Redirect::back()->with('message2', 'error'); }
	}

	public function view($fid, $cid)
	{

		// Insure answers belong to Judger first
		$answers = Formdata::where('form_id', $fid)->where('company_id', $cid)->orderBy('field_id', 'ASC')->get();
		if (count($answers) == 0) {
			return redirect()->back()->with('message2', 'startup not complete the form');
		}
		$company = Company::where('id', $answers->pluck(['company_id'])->first())->first();
		return view('admin.settings.screening.form', compact('answers', 'company'));
	}

	/**
	 * Overwrite any finished (History) panel's submitted values with the stored
	 * ones, and report which array indexes those are.
	 *
	 * Read-only rendering is presentation, not a security boundary: the request
	 * can still carry anything, and disabled inputs are omitted entirely. Both
	 * problems have the same answer — trust storage, never the payload. Without
	 * this the rebuild in addJudgingPanels() (which deletes every screening for
	 * the step) would ERASE the very records History is meant to preserve.
	 *
	 * @return array the panels array with history entries replaced
	 */
	private function restoreHistoryPanels(int $stepId, array $panels): array
	{
		if (!$stepId || empty($panels)) {
			return $panels;
		}

		foreach ($panels as $index => $panel) {
			// ONLY panels the form marked as history are restored. `history_panel`
			// is emitted by blade exclusively for panels rendered read-only, so its
			// absence means the admin is editing a live panel — leave it alone.
			//
			// Falling back to ($index + 1) here was a data-loss bug: a NEW panel
			// carries no marker, so position 5 matched stored panel 5 and silently
			// overwrote the admin's entry with that finished panel's date, judges
			// and startups (reported: saved 2026-08-28, got back 2026-08-23 locked).
			// Position is not identity — only the marker is.
			if (!isset($panel['history_panel'])) {
				continue;
			}

			$panelNumber = (int) $panel['history_panel'];
			$saved = Screening::where('step_id', $stepId)->where('panel', $panelNumber)->first();
			if (!$saved) {
				continue;
			}

			if (!PanelWindowService::isPast(PanelWindowService::fromScreening($saved), auth()->user())) {
				continue;
			}

			$savedJudges = Screening::where('step_id', $stepId)->where('panel', $panelNumber)
				->pluck('user_id')->map(fn ($i) => (int) $i)->unique()->values()->all();

			$panels[$index] = [
				'judges'       => $savedJudges,
				'companies'    => is_array($saved->users) ? $saved->users : (json_decode((string) $saved->users, true) ?: []),
				'session_date' => $saved->session_date instanceof \DateTimeInterface
					? $saved->session_date->format('Y-m-d')
					: $saved->session_date,
				'start_time'   => $saved->start_time ? substr($saved->start_time, 0, 5) : null,
				'end_time'     => $saved->end_time ? substr($saved->end_time, 0, 5) : null,
				'interval'     => $saved->interval,
				// Never re-notify for a meeting that already happened.
				'notify'       => false,
				'history_panel' => $panelNumber,
			];
		}

		return $panels;
	}

	public function addJudgingPanels(Request $request)
	{
		// History panels are restored FIRST, before validation.
		//
		// A finished panel renders read-only, so the browser omits its disabled
		// inputs entirely — the request carries no judges/startups for it. Running
		// the validator first therefore rejected the save with "assign at least one
		// judge to panel #N", naming a locked panel the admin cannot edit. Filling
		// the stored values in up front means validation sees the real data, and
		// everything downstream operates on trusted state rather than input.
		$request->merge(['panels' => $this->restoreHistoryPanels(
			(int) $request->input('step'),
			$request->input('panels', []) ?? []
		)]);

		$panelsInput = $request->input('panels', []) ?? [];

		$messages = [];
		foreach ($panelsInput as $index => $_panel) {
			$panelNumber = $index + 1;
			foreach (['required', 'array', 'min'] as $rule) {
				$messages["panels.{$index}.judges.{$rule}"] = __('admin.screening.panel_judges_required', ['panel' => $panelNumber]);
				$messages["panels.{$index}.companies.{$rule}"] = __('admin.screening.panel_companies_required', ['panel' => $panelNumber]);
			}
		}

		$validator = Validator::make($request->all(), [
			'step' => 'required|exists:steps,id',
			'panels' => 'nullable|array',
			'panels.*.judges' => 'required|array|min:1',
			'panels.*.companies' => 'required|array|min:1',
			'panels.*.session_date' => 'nullable|date',
			'panels.*.start_time' => 'nullable',
			'panels.*.end_time' => 'nullable',
			'panels.*.interval' => 'nullable|integer|min:1|max:99',
			'panels.*.notify' => 'nullable|boolean',
		], $messages);

		if ($validator->fails()) {
			return response()->json([
				'success' => false,
				'message' => $validator->errors()->first(),
				'errors' => $validator->errors(),
			], 422);
		}

		$stepId = $request->step;
		$panels = $request->panels ?? [];
		$step = Step::with('cycle.organization')->find($stepId);

		// Past-schedule gate (GV-9618).
		//
		// `minDate: 'today'` on the date picker was the only guard, and it was
		// cosmetic: client-side, and blind to the START TIME, which sat in a
		// separate picker with no floor. Today at 13:00 therefore saved happily at
		// 14:38. This is the authoritative check — the browser guards are an
		// affordance, this is the rule.
		//
		// Two carve-outs keep ordinary editing working:
		//
		// 1. History panels are skipped. Their meetings have already happened and
		//    their values are restored from the database above, not typed — so
		//    they are past BY DEFINITION and would block every save on a step with
		//    a finished panel.
		//
		// 2. A live panel is checked only when its date or start time CHANGED
		//    against what is stored. An admin editing a past panel's roster or
		//    meeting link must not be blocked by a time they never touched; only
		//    an attempt to SCHEDULE something in the past is rejected.
		foreach ($panels as $index => $panel) {
			if (isset($panel['history_panel'])) {
				continue;
			}

			$window = PanelWindowService::fromInput($panel);
			if (!$window || !PanelWindowService::startsInPast($window, auth()->user())) {
				continue;
			}

			// Unchanged against the stored row → the admin is editing something
			// else on an already-past panel. Let it through.
			$saved = Screening::where('step_id', $stepId)->where('panel', $index + 1)->first();
			if ($saved) {
				$savedDate = $saved->session_date instanceof \DateTimeInterface
					? $saved->session_date->format('Y-m-d')
					: $saved->session_date;
				$savedStart = $saved->start_time ? substr($saved->start_time, 0, 5) : null;

				$sameDate  = (string) $savedDate === (string) ($panel['session_date'] ?? '');
				$sameStart = (string) $savedStart === substr((string) ($panel['start_time'] ?? ''), 0, 5);

				if ($sameDate && $sameStart) {
					continue;
				}
			}

			return response()->json([
				'success' => false,
				'message' => __('admin.session_datetime_in_past', ['panel' => $index + 1]),
			], 422);
		}

		// Judge double-booking gate (GV-8300). A judge cannot sit on two panels
		// whose windows overlap, within this submission or against panels already
		// saved on other screening/final steps of the same cycle.
		//
		// This MUST run before the two mutations below (the step's judge mapping
		// and the wholesale delete of the step's screenings): a rejected save has
		// to leave the step exactly as it was.
		//
		// Conflicts are advisory, not fatal — the admin may knowingly proceed, so
		// the first response asks for confirmation and the retry carries the
		// acknowledgement flag.
		//
		// History panels are excluded. Their meetings already happened, so they
		// cannot be rescheduled and raise no NEW conflict — warning about them
		// would ask the admin to resolve a clash they have no way to act on
		// (and, with every panel finished, would block a save that changes
		// nothing about anyone's schedule).
		if (!$request->boolean('acknowledge_judge_conflicts')) {
			// array_filter (NOT array_values): the conflict service derives the
			// panel number a message names from the array index, so reindexing
			// would report the wrong panel — with panels 1-3 finished, a clash on
			// panel 4 would be announced as "panel #1".
			$schedulablePanels = array_filter(
				$panels,
				fn ($panel) => !isset($panel['history_panel'])
			);

			$judgeConflicts = JudgeScheduleConflictService::detect((int) $stepId, $schedulablePanels);

			if (!empty($judgeConflicts)) {
				return response()->json([
					'success' => false,
					'requires_confirmation' => true,
					'judge_conflicts' => $judgeConflicts,
					'message' => $judgeConflicts[0],
				], 422);
			}
		}

		// Map the restored history panels by array index, so the rebuild loop below
		// can reuse each one's ORIGINAL meeting instead of creating a new one.
		// restoreHistoryPanels() stamps `history_panel` on exactly those entries.
		$historyPanels = [];
		foreach ($panels as $index => $panel) {
			if (!isset($panel['history_panel'])) {
				continue;
			}
			$row = Screening::where('step_id', $stepId)->where('panel', (int) $panel['history_panel'])->first();
			if ($row) {
				$historyPanels[$index] = $row;
			}
		}

		// Update Step list (judges mapping)
		$stepList = [];
		foreach ($panels as $index => $panel) {
			$stepList[$index + 1] = array_map('intval', $panel['judges']);
		}
		Step::where('id', $stepId)->update(['list' => $panels ? json_encode($stepList) : null]);

		// Snapshot who was judging this step BEFORE the rebuild wipes it (GV-7317).
		//
		// Screening has no SoftDeletes, so the delete below is a hard delete and
		// the previous assignment is unrecoverable a line later. Taking it here —
		// after the conflict gate has passed — also means a save that gets
		// rejected for confirmation notifies nobody.
		//
		// Keyed by "panel:judge", not by judge alone: a panel is a distinct
		// assignment with its own startups, date and meeting, so a judge already
		// sitting on panel 1 who is now added to panel 2 IS newly assigned there
		// and must be told. Diffing on judge id alone silently swallowed that.
		//
		// Panel numbers are a safe key here because both sides of this comparison
		// come from the same step in one request. deleteJudgingPanel() does
		// renumber panels, but it shifts `steps.list` and the `screenings` rows
		// together in a separate request, so the stored numbering always matches
		// the numbering the form posts back.
		$judgesBefore = Screening::where('step_id', $stepId)
			->get(['panel', 'user_id'])
			->map(fn ($row) => ((int) $row->panel) . ':' . ((int) $row->user_id))
			->unique();

		// Snapshot every scheduled startup's meeting slot BEFORE the rebuild, for
		// the same reason as $judgesBefore: the delete below is a hard delete, so
		// a line later there is nothing left to diff against (GV-8892 / GV-8893).
		//
		// Taken after the conflict gate has passed, so a save that comes back
		// asking for confirmation notifies nobody.
		$slotsBefore = $this->snapshotCompanySlots($stepId);

		// Clear existing screenings for this step
		Screening::where('step_id', $stepId)->delete();

		// Accumulate the full set of judges assigned to each company across ALL
		// panels. A company may appear in more than one panel (e.g. its own panel
		// plus a shared "all startups" panel), so each company's Finalscreening
		// row must hold the UNION of every panel's judges. Writing per-panel with
		// updateOrCreate keyed on (step_id, company_id) overwrote earlier panels,
		// leaving only the last panel's judges (GV: final screening results showed
		// a single judge for every company). We build the map here and persist it
		// once, after the loop.
		$judgesByCompany = [];
		$meetingByCompany = [];

		// Create new screenings if panels exist
		foreach ($panels as $index => $panel) {
			$panelNumber = $index + 1;
			$judgeIds = array_unique(array_map('intval', $panel['judges']));
			$companyIds = array_unique(array_map('intval', $panel['companies']));

			// Auto-generate meeting link if session is scheduled
			$meetingLink = null;
			$meetingPassword = null;
			$zoomId = null;
			$startUrl = null;
			$startDateTime = null;
			$duration = null;

			// Interval scheduling (GV-3295). When the interval switch is on the
			// admin never types an end time -- the field is rendered disabled, and
			// browsers omit disabled inputs from the submission, so `end_time`
			// arrives empty. Derive it here instead of storing NULL.
			//
			// The panel holds ONE meeting that the startups join in sequence, so
			// its end time must cover EVERY startup's slot: start + (count *
			// interval). Individual per-startup windows are derived at read time
			// by ScreeningSlotService from (start_time, interval, position).
			$interval = isset($panel['interval']) && $panel['interval'] !== '' ? (int) $panel['interval'] : null;
			if ($interval !== null && $interval <= 0) {
				$interval = null;
			}
			$endTime = $panel['end_time'] ?? null;

			if ($interval !== null && !empty($panel['start_time'])) {
				$endTime = \Carbon\Carbon::parse($panel['session_date'] . ' ' . $panel['start_time'])
					->addMinutes(count($companyIds) * $interval)
					->format('H:i');
			}

			// A history panel keeps the meeting it actually held. Re-running
			// createMeeting() here would mint a NEW Zoom link for a session that
			// already took place, discarding the link/passcode participants used.
			if (isset($historyPanels[$index])) {
				$saved = $historyPanels[$index];
				$meetingLink = $saved->meeting_link;
				$meetingPassword = $saved->meeting_password;
				$zoomId = $saved->zoom_id;
				$startUrl = $saved->start_url;
				$startDateTime = $saved->session_date && $saved->start_time
					? (($saved->session_date instanceof \DateTimeInterface
						? $saved->session_date->format('Y-m-d')
						: $saved->session_date) . ' ' . $saved->start_time)
					: null;
			} elseif (!empty($panel['session_date']) && !empty($panel['start_time'])) {
				try {
					$startDateTime = $panel['session_date'] . ' ' . $panel['start_time'];
					$duration = 60;
					if (!empty($endTime)) {
						$start = \Carbon\Carbon::parse($startDateTime);
						$end = \Carbon\Carbon::parse($panel['session_date'] . ' ' . $endTime);
						// GV-7381: store the window the admin actually entered. This
						// used to floor the value at 30, so a 22:00-22:15 session was
						// booked with Zoom -- and shown to the founder -- as 30 minutes.
						// Sub-30 windows are routine (5/10/20-minute intervals are in
						// active use), so the floor only ever corrupted the duration.
						$duration = max($start->diffInMinutes($end), 1);
					}

					$meetingService = OnlineMeetingFactory::create();
					$meetingResult = $meetingService->createMeeting([
						'topic' => 'Screening Panel #' . $panelNumber . ' - ' . ($step->title ?? 'Screening'),
						'start_time' => $startDateTime,
						'duration' => $duration,
						'host_video' => 1,
						'participant_video' => 1,
					]);

					if (!empty($meetingResult['success']) && !empty($meetingResult['data'])) {
						$meetingLink = $meetingResult['data']['join_url'] ?? null;
						$meetingPassword = $meetingResult['data']['passcode'] ?? null;
						$zoomId = $meetingResult['data']['id'] ?? null;
						$startUrl = $meetingResult['data']['start_url'] ?? null;
					}
				} catch (\Exception $e) {
					Log::warning("Failed to auto-generate meeting for panel {$panelNumber}, step {$stepId}: " . $e->getMessage());
				}
			}

			foreach ($judgeIds as $judgeId) {
				Screening::create([
					'step_id' => $stepId,
					'user_id' => $judgeId,
					'panel' => $panelNumber,
					'users' => $companyIds,
					'session_date' => $panel['session_date'] ?? null,
					'meeting_link' => $meetingLink,
					'meeting_password' => $meetingPassword,
					'zoom_id' => $zoomId,
					'start_url' => $startUrl,
					'start_time' => $panel['start_time'] ?? null,
					'end_time' => $endTime ?: null,
					'interval' => $interval,
				]);
			}

			// Add meeting data to panels response
			$panels[$index]['meeting_link'] = $meetingLink;
			$panels[$index]['meeting_password'] = $meetingPassword;

			// Accumulate this panel's judges into each of its companies' judge set
			// (Final Screening only). Persisted once after the loop so companies in
			// multiple panels keep the union of all their panels' judges.
			if ($step && $step->stype == 5) {
				foreach (array_values($companyIds) as $position => $companyId) {
					$judgesByCompany[$companyId] = array_merge($judgesByCompany[$companyId] ?? [], $judgeIds);

					// finalscreenings rows are PER COMPANY, so each must carry that
					// company's own slot -- not the panel-wide window (GV-3295).
					// With the interval off this collapses to the panel window,
					// preserving the previous behaviour.
					$companyDatetime = $startDateTime;
					$companyDuration = $duration;

					if ($interval !== null && !empty($panel['session_date']) && !empty($panel['start_time'])) {
						$slot = ScreeningSlotService::slotFor(
							$panel['session_date'],
							$panel['start_time'],
							$interval,
							$position
						);
						if (!empty($slot['start'])) {
							$companyDatetime = $slot['start']->format('Y-m-d H:i:s');
							$companyDuration = $interval;
						}
					}

					$meetingByCompany[$companyId] = [
						'meeting_link' => $meetingLink,
						'datetime' => $companyDatetime,
						'duration' => $companyDuration
					];
				}
			}
		}

		// Persist the Final Screening judge assignments. Mirror the screenings
		// rebuild above: clear the step's existing rows, then write one row per
		// company holding the merged (unique) judge list. The clear removes stale
		// rows for companies/panels no longer present in this submission.
		if ($step && $step->stype == 5) {
			\App\Finalscreening::where('step_id', $stepId)->delete();
			foreach ($judgesByCompany as $companyId => $judgeIds) {
				// The Finalscreening model casts `judges` to array, so it json_encodes on save.
				// Pass the array directly — passing a pre-encoded string causes double-encoding.
				\App\Finalscreening::create([
					'step_id' => $stepId,
					'company_id' => $companyId,
					'judges' => array_values(array_unique(array_map('intval', $judgeIds))),
					'join_url' => array_key_exists($companyId, $meetingByCompany) && $meetingByCompany[$companyId]['meeting_link'] ? ( $meetingByCompany[$companyId]['meeting_link'] ?? null ) : null,
					'datetime' => array_key_exists($companyId, $meetingByCompany) && $meetingByCompany[$companyId]['datetime'] ? ( $meetingByCompany[$companyId]['datetime'] ?? null ) : null,
					'duration' => array_key_exists($companyId, $meetingByCompany) && $meetingByCompany[$companyId]['duration'] ? ( $meetingByCompany[$companyId]['duration'] ?? null ) : null,
				]);
			}
		}

		$this->notifyNewlyAssignedJudges($stepId, $panels, $judgesBefore);
		$this->notifyAffectedStartups($stepId, $panels, $slotsBefore);

		return response()->json([
			'success' => true,
			'message' => __('admin.Saved_successfully'),
			'data' => $panels
		]);
	}

	/**
	 * Email judges who were NEWLY added to a panel whose "Notify now" switch is on.
	 *
	 * Covers both initial screening (stype 2) and final screening (stype 5) —
	 * both are saved through addJudgingPanels(), so one call site serves both,
	 * and every role that can assign (super admin, org manager, sub admin) hits
	 * the same endpoint (GV-7317 / GV-7237 / GV-7290 / GV-7291).
	 *
	 * @param  \Illuminate\Support\Collection  $judgesBefore  "panel:judge" pairs assigned before this save
	 */
	private function notifyNewlyAssignedJudges(int $stepId, array $panels, $judgesBefore): void
	{
		$toNotify = [];

		foreach ($panels as $index => $panel) {
			// A finished meeting must not re-notify. The blade hides the switch on
			// a history panel, but the payload is not a boundary — re-check here.
			if (isset($panel['history_panel'])) {
				continue;
			}

			if (!filter_var($panel['notify'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
				continue;
			}

			// Panel numbers are 1-based, matching how $stepList and the Screening
			// rows are numbered from the same $panels array in addJudgingPanels().
			$panelNumber = $index + 1;

			foreach ((array) ($panel['judges'] ?? []) as $judgeId) {
				$judgeId = (int) $judgeId;

				if (!$judgeId) {
					continue;
				}

				// Retained judges are silent: a judge already on THIS panel before
				// the save is not newly assigned to it. A judge new to this panel
				// is notified even if they already sit on another panel of the
				// same step — that is a separate assignment with its own meeting.
				if ($judgesBefore->contains($panelNumber . ':' . $judgeId)) {
					continue;
				}

				// Keyed by judge id so one save sends at most one email per judge,
				// even when they are newly added to several panels at once.
				$toNotify[$judgeId] = $judgeId;
			}
		}

		if (empty($toNotify)) {
			return;
		}

		foreach (User::whereIn('id', array_values($toNotify))->get() as $judge) {
			try {
				$judge->notify(new JudgeAssignedToScreeningNotification($stepId));
			} catch (\Throwable $th) {
				// A mail failure must never fail the assignment: the judges are
				// already saved, and throwing here would report a save error for
				// work that succeeded.
				Log::error('Failed to notify newly assigned judge', [
					'judge_id' => $judge->id,
					'step_id'  => $stepId,
					'error'    => $th->getMessage(),
				]);
			}
		}
	}

	/**
	 * Snapshot the scheduled meeting slot of every startup on a step.
	 *
	 * Keyed "companyId:panel" => ['company' => int, 'start' => 'Y-m-d H:i'|null,
	 * 'panel' => int]. A company sitting in several panels holds ONE ENTRY PER
	 * PANEL, because each panel is a separate meeting with its own time.
	 *
	 * Collapsing a company to a single (earliest) slot silently hid every change
	 * to its other panels (GV-8892 follow-up): editing panel 2's interval, date
	 * or roster left the company's earliest slot — on panel 3 — untouched, so the
	 * diff saw no change and nobody was emailed.
	 *
	 * `screenings` holds one row PER JUDGE PER PANEL, so the rows are collapsed
	 * to one entry per panel first — otherwise a panel with three judges would
	 * compute every one of its companies' slots three times.
	 *
	 * @return array<string, array{company: int, start: ?string, panel: int}>
	 */
	private function snapshotCompanySlots(int $stepId): array
	{
		$slots = [];

		$panelRows = Screening::where('step_id', $stepId)->get()->unique('panel');

		foreach ($panelRows as $row) {
			$companyIds = is_array($row->users) ? $row->users : (json_decode((string) $row->users, true) ?: []);

			foreach (array_values($companyIds) as $position => $companyId) {
				$companyId = (int) $companyId;

				if (!$companyId) {
					continue;
				}

				$slot = ScreeningSlotService::slotFor(
					$row->session_date instanceof \DateTimeInterface
						? $row->session_date->format('Y-m-d')
						: $row->session_date,
					$row->start_time,
					isset($row->interval) ? (int) $row->interval : null,
					$position,
					$row->end_time
				);

				$start = !empty($slot['start']) ? $slot['start']->format('Y-m-d H:i') : null;

				// One entry per (company, panel): each panel is its own meeting, so
				// a company in several panels must be diffed panel by panel.
				$panelNumber = (int) $row->panel;

				$slots[$companyId . ':' . $panelNumber] = [
					'company' => $companyId,
					'start'   => $start,
					'panel'   => $panelNumber,
				];
			}
		}

		return $slots;
	}

	/**
	 * Which startups and which panel numbers the "Notify now" switch covers.
	 *
	 * Returns [companyId => true, [panelNumber, ...]]. The company map gates
	 * startups still on a panel; the panel-number list gates startups REMOVED
	 * from one, which by definition no longer appear in any panel's list.
	 *
	 * Panel numbers are 1-based here, matching how the Screening rows and
	 * $stepList are numbered from this same $panels array.
	 *
	 * @return array{0: array<int, true>, 1: array<int, int>}
	 */
	private function notifyingPanelScope(array $panels): array
	{
		$companies = [];
		$panelNumbers = [];

		foreach ($panels as $index => $panel) {
			// A finished meeting must not re-notify. Blade hides the switch on a
			// history panel, but the payload is not a boundary — re-check here.
			if (isset($panel['history_panel'])) {
				continue;
			}

			if (!filter_var($panel['notify'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
				continue;
			}

			$panelNumbers[] = $index + 1;

			foreach ((array) ($panel['companies'] ?? []) as $companyId) {
				$companyId = (int) $companyId;

				if ($companyId) {
					$companies[$companyId] = true;
				}
			}
		}

		return [$companies, $panelNumbers];
	}

	/**
	 * Email startups whose scheduled meeting time actually moved (GV-8892 /
	 * GV-8893), and those whose meeting was cancelled by removing them from a
	 * scheduled panel.
	 *
	 * Runs for both initial screening (stype 2) and final screening (stype 5) —
	 * both save through addJudgingPanels(), and every role that can assign hits
	 * the same endpoint, so one call site covers all of them.
	 *
	 * Only genuine changes notify. A startup whose slot is unmoved gets nothing,
	 * which is what "the affected startup" means: with interval scheduling,
	 * adding, removing or REORDERING startups reflows the positions after the
	 * edit, so the set of affected startups is far wider than the ones the admin
	 * actually touched — and narrower than the whole panel.
	 *
	 * Gated on the panel's "Notify now" switch, exactly like the judge emails:
	 * the switch's own hint promises to email "the judges and startups in this
	 * panel", and saving must never contact anyone unless asked. An admin
	 * reordering startups with the switch off is making a quiet correction, and
	 * the confirmation dialog is suppressed to match.
	 *
	 * @param  array<string, array{company: int, start: ?string, panel: int}>  $slotsBefore
	 */
	private function notifyAffectedStartups(int $stepId, array $panels, array $slotsBefore): void
	{
		[$notifiableCompanies, $notifyingPanelNumbers] = $this->notifyingPanelScope($panels);

		if (empty($notifiableCompanies) && empty($notifyingPanelNumbers)) {
			return;
		}

		$slotsAfter = $this->snapshotCompanySlots($stepId);

		$scheduled = [];
		$rescheduled = [];
		$cancelled = [];

		// Compared per (company, panel): a company in several panels holds one
		// meeting per panel, and a change to any of them must reach it.
		foreach ($slotsAfter as $key => $after) {
			$companyId = $after['company'];
			$before = $slotsBefore[$key] ?? null;

			// Newly scheduled, or moved to a different time. A company that had no
			// slot before and still has none is not a change.
			if ($after['start'] === null) {
				continue;
			}

			// Only startups sitting on a panel whose switch is on.
			if (!isset($notifiableCompanies[$companyId])) {
				continue;
			}

			// The panel this slot belongs to must itself be notifying, otherwise a
			// quiet edit to panel 2 would ride out on panel 3's switch.
			if (!in_array($after['panel'], $notifyingPanelNumbers, true)) {
				continue;
			}

			if ($before !== null && $before['start'] === $after['start']) {
				continue;
			}

			// "Rescheduled" is only true of a meeting the startup ALREADY had at a
			// different time. Two cases are a first booking, not a move:
			//
			//  - the startup had no meeting on this step at all;
			//  - it holds this slot on a panel it was not on before. A panel is
			//    ONE meeting, so a second panel is an ADDITIONAL meeting (another
			//    round) and any earlier one stays exactly where it was. Calling
			//    that a reschedule reads as the first meeting having moved, and
			//    the founder stops attending it.
			//
			// Only a slot on a panel the startup was already sitting on is a
			// genuine move: that panel's times reflowed underneath it.
			$bucket = ($before === null) ? 'scheduled' : 'rescheduled';

			// Earliest changed slot wins when several of a company's panels moved,
			// so it receives one mail naming its next meeting.
			if ($bucket === 'scheduled') {
				if (!isset($scheduled[$companyId]) || $after['start'] < $scheduled[$companyId]) {
					$scheduled[$companyId] = $after['start'];
				}
			} elseif (!isset($rescheduled[$companyId]) || $after['start'] < $rescheduled[$companyId]) {
				$rescheduled[$companyId] = $after['start'];
			}
		}

		// Removal is decided by MEMBERSHIP, never by the slot: positionOf()
		// returns 0 for a company it cannot find, so a removed startup and one
		// moved into the first slot produce identical slot data. Only the
		// company's absence from the rebuilt step distinguishes them.
		foreach ($slotsBefore as $key => $before) {
			$companyId = $before['company'];

			if ($before['start'] === null) {
				continue;
			}

			if (array_key_exists($key, $slotsAfter)) {
				continue;
			}

			// A removed startup is on no panel any more, so it cannot be looked
			// up in $notifiableCompanies. Gate it on the panel it was removed
			// FROM instead — that panel's switch is the consent that covers it.
			if (!in_array((int) $before['panel'], $notifyingPanelNumbers, true)) {
				continue;
			}

			// Losing THIS panel's meeting is a cancellation even when the company
			// still sits on another panel: the two are separate meetings, and the
			// one in hand is genuinely gone.
			//
			// A reschedule already queued for this company wins, though: the same
			// save moved another of its meetings, and one mail naming the new time
			// says more than a bare cancellation.
			if (isset($rescheduled[$companyId]) || isset($scheduled[$companyId])) {
				continue;
			}

			$cancelled[$companyId] = $before['start'];
		}

		// A move reported for the same company wins over a first booking: one mail
		// per save, naming the change to the meeting it already knew about.
		$scheduled = array_diff_key($scheduled, $rescheduled);

		if (empty($scheduled) && empty($rescheduled) && empty($cancelled)) {
			return;
		}

		$this->sendStartupMeetingNotifications($stepId, $scheduled, 'scheduled');
		$this->sendStartupMeetingNotifications($stepId, $rescheduled, 'rescheduled');
		$this->sendStartupMeetingNotifications($stepId, $cancelled, 'cancelled');
	}

	/**
	 * Deliver one batch of startup meeting notifications.
	 *
	 * The notifiable is the company OWNER — a Company is not itself notifiable.
	 * Ownership lives on `users.company_id` (there is no `companies.user_id`),
	 * so the owners are resolved from the users side in a single query.
	 *
	 * @param  array<int, string>  $companies  company id => slot start
	 * @param  string  $variant  'scheduled', 'rescheduled' or 'cancelled'
	 */
	private function sendStartupMeetingNotifications(int $stepId, array $companies, string $variant): void
	{
		if (empty($companies)) {
			return;
		}

		$owners = User::whereIn('company_id', array_keys($companies))
			->get()
			->keyBy('company_id');

		foreach ($companies as $companyId => $slotStart) {
			$owner = $owners->get($companyId);

			if (!$owner) {
				continue;
			}

			try {
				$owner->notify(new StartupMeetingTimeChangedNotification(
					$stepId,
					(int) $companyId,
					$slotStart,
					$variant
				));
			} catch (\Throwable $th) {
				// A mail failure must never fail the save: the schedule is already
				// persisted, and throwing here would report an error for work that
				// succeeded.
				Log::error('Failed to notify startup of screening meeting change', [
					'company_id' => $companyId,
					'step_id'    => $stepId,
					'variant'    => $variant,
					'error'      => $th->getMessage(),
				]);
			}
		}
	}

    public function deleteJudgingPanel(Request $request)
    {
        $request->validate([
            'step' => 'required|exists:steps,id',
            'panel' => 'required|integer'
        ]);

        $stepId = $request->step;
        $panelIdx = (int)$request->panel; // 1-based index from UI usually, or 0-based. Let's assume 1-based to match 'panel' column.

        // A finished panel is a record of a screening that took place; it cannot be
        // deleted. The button is hidden client-side, but that is presentation only
        // — this endpoint takes a panel number straight from the request, so the
        // rule has to be enforced here too.
        $existing = Screening::where('step_id', $stepId)->where('panel', $panelIdx)->first();
        if ($existing && PanelWindowService::isPast(PanelWindowService::fromScreening($existing), auth()->user())) {
            return response()->json([
                'success' => false,
                'message' => __('admin.screening.panel_history_cannot_delete'),
            ], 422);
        }

        $step = Step::findOrFail($stepId);
        $list = json_decode($step->list, true) ?? [];
        
        // Remove the panel from the list
        if (isset($list[$panelIdx])) {
            unset($list[$panelIdx]);
            
            // Re-index remaining panels to keep sequence 1, 2, 3...
            $newList = [];
            $i = 1;
            foreach ($list as $oldIdx => $data) {
                $newList[$i++] = $data;
            }
            $step->update(['list' => !empty($newList) ? json_encode($newList) : null]);
            
            // Delete screenings for the target panel
            Screening::where('step_id', $stepId)->where('panel', $panelIdx)->delete();
            
            // Shift subsequent screenings down if needed to maintain panel alignment
            $screeningsToShift = Screening::where('step_id', $stepId)->where('panel', '>', $panelIdx)->get();
            foreach ($screeningsToShift as $sc) {
                $sc->update(['panel' => $sc->panel - 1]);
            }

            return response()->json([
                'success' => true, 
                'message' => __('admin.Deleted_successfully')
            ]);
        }

        return response()->json([
            'success' => false, 
            'message' => __('admin.panel_not_found')
        ], 404);
    }

	public function save(Request $request)
	{
		$answers = Formdata::where('form_id', $request->form_id)->where('company_id', $request->company_id)->orderBy('field_id', 'ASC')->get(['field_id']);

		$user = Auth::id();

		foreach ($answers as $answer) {

			Formdata::where('field_id', $answer->field_id)->where('company_id', $request->company_id)
				->update(['screening->' . $user => $request->{'w' . $answer->field_id}, 'comments->' . $user => $request->{'c' . $answer->field_id}]);
		}
		return Redirect::route('core.admin_access')->with('msg', 'Saved');
	}

	public function add(Request $request)
	{
		$list = json_encode($request->panelusers);
		if ($list) {
			Screening::where('step_id', $request->sid)->where('panel', $request->pid)->update(['users' => $list]);
			return Redirect::back()->with('message', 'Saved');
		} else {
			return Redirect::back()->with('message2', 'Error');
		}
	}

	public function getusers($fid, $sid, $pid)
	{
		$list = Screening::where('step_id', $sid)->where('panel', $pid)->first(['users']);
		$outlist = Screening::where('step_id', $sid)->where('panel', '<>', $pid)->pluck('users');
		$outlist ? $outlist = ('[' . str_replace(['[', ']'], '', json_encode($outlist)) . ']') : $outlist = [];
		$menu = $selected = '';
		$l = session()->get('locale') ?? 'ar';
		// Only offer startups that have progressed to THIS screening step ($sid).
		// $fid is a BuilderForm id (not a step id), so the previous
		// whereIn('step', [$fid, $sid]) only ever matched Custom Form step
		// companies by an accidental id collision, pulling startups that had not
		// yet advanced to the screening phase into the assignment list (GV-3237).
		foreach (Company::where('step', $sid)->get() as $comp) {
			if (in_array($comp->id,  $list['users'] ?? [])) {
				$selected = " selected ";
			}
			if (in_array($comp->id, json_decode($outlist) ?? [])) {
				$selected = " disabled='disabled' ";
			}
			$menu = $menu . '<option value="' . $comp->id . '"' . $selected . '>' . $comp->{'name_' . $l} . '</option>';
			$selected = '';
		}
		return $menu;
	}

	public function results($id = 0)
	{
		$l = app()->getLocale();
		$user = auth()->user();
		$orgIds = $user->organizations_ids;

		$steps = Step::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
			return $q->whereHas('cycle', function ($query) use ($orgIds) {
				$query->whereIn('organization_id', $orgIds);
			});
		})->where('stype', 2)->orderBy('from')->get();

		if ($id == 0) {
			$id = $steps->pluck('id')->first();
			$cstep = Step::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->whereHas('cycle', function ($query) use ($orgIds) {
					$query->whereIn('organization_id', $orgIds);
				});
			})->where('stype', 2)->where('id', $id)->first();
		} else {
			$cstep = Step::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->whereHas('cycle', function ($query) use ($orgIds) {
					$query->whereIn('organization_id', $orgIds);
				});
			})->where('stype', 2)->where('id', $id)->first();
		}
		// Results page is informative: show every startup that belongs to this
		// step's evaluation regardless of its current step (so results stay
		// visible after a startup is advanced). The assignment page (show) stays
		// limited to the current step.
		$metadata = ScreeningEvaluationResults::metadata( $id, null, null, true );
		$companies = $metadata['companies'];
		$_steps = $metadata['_steps'];
		$judges = $metadata['judges'];
		$evaluation = $metadata['evaluation'];
		$results = $metadata['results'];
		$_companies = $metadata['_companies'];
		$form = null;
		if ($cstep) {
			$form = Step::where('cycle_id', $cstep->cycle_id)->where('stype', 1)->where('arr', $cstep->arr - 1)->first();
		}
		$svgs = ['notebook', 'search', 'idea', 'comments', 'trophy'];

		$_companies = $companies;
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
	
		return view('admin.settings.screening.results', compact('cstep', 'judges', 'form', 'steps', '_steps', 'id', 'svgs', '_companies', 'evaluation', 'results'));
	}

	public function changestep(Request $request)
	{
		$by = Auth::id();
		$approved = null;
		$successCount = 0;
		$companies = is_array($request->company) ? $request->company : [$request->company];
		$totalCount = count($companies);

		$status = $request->step != "0" ? 'in' : 'out';
		if( $request->step == "0" || $request->step == 0 ) { $currentStep = 'Disapproved'; }
		else
		{
			$approved = 1;
			$currentStep = Step::with(['cycle'])->findOrFail($request->step);
			if( !empty( $currentStep ) )
			{
				$currentStep = $currentStep?->title ?? '';
			} else { $currentStep = ''; }
		}

		$getCompanies = Company::whereIn('id', $companies)->with(['stepdata', 'stepdata.cycle', 'cycledata'])->get();
		foreach ($companies as $companyId)
		{
			$data = [];
			// $company = Company::where('id', $companyId)->first();
			$company = $getCompanies->where('id', $companyId)->first();

			// now can change step even if company not have step before
			if (empty($company->step)) {
				$data['last_step'] = '-';
			}

			if (!$company) {
				continue; // Skip if company not found
			}

			$data['new_step'] = $currentStep;
			/* $data['new_step'] = '-';
			if ($request->step == "0") {
				$data['new_step'] = 'Disapproved';
			} else {
				// $cstep = Step::findOrFail($request->step);
				
				$data['new_step'] = $currentStep?->title;
			} */

			$data['last_step'] = '-';
			if (!empty($company->step)) {
				// $lastStep = Step::find($company->step);
				$lastStep = $company->stepdata;
				$data['last_step'] = $lastStep ? $lastStep->title : null;
			}
			$data['status'] = $status;
			$data['program'] = $company?->stepdata?->cycle ?? null;
			if ($request->step == "0") {
				$update = $company->update(['cycle' => null, 'step' => null, 'approved' => null, 'approved_by' => $by]);
			} else {
				$update = $company->update(['step' => $request->step, 'approved' => $approved, 'approved_by' => $by]);
			}

			if ($update) {
				$successCount++;
				/// notify Company Owner via Mail
				SendChangeCycleNotificationJob::dispatch($companyId, $request->step, $data['last_step'], $data['new_step'], $data['program']);
			}
		}

		/// End of mail send
		if ($successCount === $totalCount) {
			return response()->json(['state' => 'success'], 200);
		} elseif ($successCount > 0) {
			return response()->json(['state' => 'partial', 'message' => "Updated {$successCount} out of {$totalCount} companies"], 200);
		} else {
			return response()->json(['state' => 'failed'], 500);
		}
	}

	public function exportresultScreen(Request $request)
	{
		// تحقق من أن البريد الإلكتروني موجود

		// This route has no name, so the route-permission middleware never sees it:
		// the export must at least be limited to the caller's own programs.
		$this->authorizeStepOrganization($request->input('step'));

		$data1 = [
			'step' => $request->input('step'),
			'formid' => $request->input('formid'),
			'exportcstep' => $request->input('exportcstep'),
			'userEmail' => auth()->user()->email
		];

		// إرسال وظيفة (Job) لتحميل البيانات وإرسال البريد
		ExportResScreenData::dispatch($data1);

		return response()->json(['message' => __('cycles.export_success')]);
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

		// Export mirrors the results page: include every startup belonging to the
		// step's evaluation regardless of current step.
		$metadata = ScreeningEvaluationResults::metadata($id, null, null, true);
		$_companies = $metadata['_companies'];
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
			'screening',
			true // includeCompanyColumn
		);

		$stepName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $evaluation->step->title ?? 'screening');
		$filename = "screening_results_{$stepName}_" . now()->format('Y-m-d_His') . ".xlsx";

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
			'screening',
			false // no company column for single export
		);

		$companyName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $company->name);
		$filename = "screening_{$companyName}_" . now()->format('Y-m-d_His') . ".xlsx";

		return Excel::download($export, $filename);
	}

	public function save_evaluation_draft(Request $request)
	{
		return self::storeEvaluationCriteria( $request, 'draft' );
	}

	public function publish_evaluation(Request $request)
	{
		return self::storeEvaluationCriteria( $request, 'publish' );
	}

	public function get_results(Request $request)
	{
		$request->validate([
			'step' => 'required|integer|exists:steps,id',
			'company' => 'required|integer|exists:companies,id',
		]);

		$step_id = $request->step;
		$company_id = intval( $request->company );

		$results = ScreeningEvaluationResults::results( $step_id, $company_id );

		return $results[$company_id] ?? [
			'total' => 0,
			'criteria' => collect(),
			'judges' => collect()
		];
	}

	private function storeEvaluationCriteria( $request, $status )
	{
		// Coarse structural guard only. Per-field, name-pointing errors for the criteria
		// tree are produced on publish by firstNamedCriterionError() below, so we keep
		// the wildcard field rules out of here to avoid short-circuiting with a generic message.
		$request->validate([
			'action' => "required|in:{$status}",
			'stepId' => 'required|integer|exists:steps,id',
			'criteria' => 'required_if:action,publish|array',
			'criteria.*.id' => 'nullable|sometimes|integer|exists:screening_evaluation_criteria,id',
			'criteria.*.subCriteria.*.id' => 'nullable|sometimes|integer|exists:screening_evaluation_criteria,id',
			'screening_guidelines' => 'nullable|string',
		], [
			'action.required' => __('cycles.evaluation_action_invalid'),
			'action.in' => __('cycles.evaluation_action_invalid'),
			'stepId.required' => __('cycles.evaluation_step_invalid'),
			'stepId.integer' => __('cycles.evaluation_step_invalid'),
			'stepId.exists' => __('cycles.evaluation_step_invalid'),
			'criteria.required_if' => __('cycles.evaluation_criteria_required'),
			'criteria.array' => __('cycles.evaluation_criteria_required'),
		]);

		$id = $request->stepId;
		$criteria = collect( $request->criteria );

		// Precise, named field-level check: reports the exact criterion / sub-criterion at fault.
		// Runs for BOTH actions so a bad weight can never reach the double(5,2) column and
		// overflow it; drafts are only checked for range/format, publish also for completeness.
		if( $request->has('criteria') && ($namedError = self::firstNamedCriterionError( $request->criteria, $status == 'publish' )) )
		{
			return response()->json(['status' => false, 'message' => $namedError], 200);
		}

		if( $status == 'publish' )
		{
			if( $criteria->sum('weight') != 1 )
			{
				if( $criteria->sum('weight') > 1 ) { return response()->json(['status' => false, 'message' => __('admin.total_evaluation_weight_not_exceed_100')], 200); }
				else { return response()->json(['status' => false, 'message' => __('admin.total_evaluation_weight_be_100')], 200); }
			}
			else
			{
				foreach( $criteria as $criteriaItem )
				{
					if( array_key_exists('subCriteria', $criteriaItem) )
					{
						$subCriteria = collect( $criteriaItem['subCriteria'] );
						$total = $subCriteria->sum('weight');
						if( $total != 1 )
						{
							$name = $criteriaItem['name'];
							if( $criteria->sum('weight') > 1 ) { return response()->json(['status' => false, 'message' => __('admin.category_weight_not_exceed_100', ['category' => $name])], 200); }
							else { return response()->json(['status' => false, 'message' => __('admin.category_weight_be_100', ['category' => $name])], 200); }
						}
					}
				}
			}
		}
		$evaluation = ScreeningEvaluation::where('step_id', $id)->first();
		if( !$evaluation )
		{
			$evaluation = new ScreeningEvaluation;
			$evaluation->step_id = $id;
			$evaluation->save();
			
		}

		$evaluationForm = ScreeningEvaluation::getEvaluationData( $id );
		$current_status = strtolower( $evaluationForm->status );

		// Allow saving screening_guidelines even when evaluation is published
		if ($request->has('screening_guidelines')) {
			$evaluation->screening_guidelines = $request->screening_guidelines;
			$evaluation->save();
			// If only screening_guidelines is being saved, return success
			if (!$request->has('criteria') || empty($request->criteria)) {
				return response()->json([
					'status' => true,
					'title' => 'saved',
					'message' => __('admin.Screening guidelines saved successfully')
				]);
			}
		}

		if( $current_status == 'published' ) { return response()->json(['status' => false, 'message' => __('You can not make any changes on a published evaluation')], 200); }
		else
		{
			$toCreate = ['criteria' => [], 'subCriteria' => []];
			$toUpdate = ['criteria' => [], 'subCriteria' => []];
			$toRemove = ['criteria' => [], 'subCriteria' => []];

			$savedData = $evaluationForm->categories;
			$savedCriteriaById = collect($savedData)->keyBy('id');

			$allSeenCriteriaIds = [];
			$allSeenSubCriteriaIds = [];
			// Compare request criteria
			$criteria = $request->criteria ?? [];
			foreach ($criteria as $reqCriterion)
			{
				$reqId = $reqCriterion['id'];
				if (!$reqId)
				{
					// New main criterion to be created
					$toCreate['criteria'][] = $reqCriterion;
				}
				else
				{
					$allSeenCriteriaIds[] = $reqId;
					$savedCriterion = $savedCriteriaById[$reqId] ?? null;

					if( $savedCriterion )
					{
						// Check for updates in name or weight
						if (
							$savedCriterion->name !== $reqCriterion['name'] ||
							(float)$savedCriterion->weight !== (float)$reqCriterion['weight']
						) {
							$toUpdate['criteria'][] = $reqCriterion;
						}
						// --- SUB CRITERIA HANDLING ---
						$savedSubById = collect($savedCriterion->subCriteria)->keyBy('id');
						if( array_key_exists('subCriteria', $reqCriterion) )
						{
							foreach( $reqCriterion['subCriteria'] as $reqSub )
							{
								$subId = $reqSub['id'];
								if (!$subId) {
									$toCreate['subCriteria'][] = $reqSub + ['parent_id' => $reqId];
								} else {
									$allSeenSubCriteriaIds[] = $subId;
									$savedSub = $savedSubById[$subId] ?? null;
									if ($savedSub) {
										if (
											$savedSub->name !== $reqSub['name'] ||
											(float)$savedSub->weight !== (float)$reqSub['weight']
										) {
											$toUpdate['subCriteria'][] = $reqSub;
										}
									}
								}
							}
						}

						// Detect removed subCriteria
						foreach ($savedSubById as $savedSubId => $savedSub) {
							if (!in_array($savedSubId, array_column($reqCriterion['subCriteria'], 'id'))) {
								$toRemove['subCriteria'][] = $savedSub;
							}
						}
					}
				}
			}
			
			// Detect removed criteria
			foreach ($savedCriteriaById as $savedCriterionId => $savedCriterion) {
				if (!in_array($savedCriterionId, $allSeenCriteriaIds)) {
					$toRemove['criteria'][] = $savedCriterion;
				}
			}
			foreach( $toCreate['criteria'] as $value )
			{
				$criteria = new ScreeningEvaluationCriteria;
				$criteria->title = $value['name'];
				$criteria->weight = $value['weight'] * 100;
				$criteria->evaluation_id = $evaluation->id;
				$criteria->save();
				if( array_key_exists('subCriteria', $value) )
				{
					foreach( $value['subCriteria'] as $sub )
					{
						$subCriteria = new ScreeningEvaluationCriteria;
						$subCriteria->title = $sub['name'];
						$subCriteria->weight = $sub['weight'] * 100;
						$subCriteria->parent_id = $criteria->id;
						$subCriteria->evaluation_id = null;
						$subCriteria->save();
					}
				}
			}
			if( array_key_exists('subCriteria', $toCreate) )
			{
				foreach( $toCreate['subCriteria'] as $sub )
				{
					$subCriteria = new ScreeningEvaluationCriteria;
					$subCriteria->title = $sub['name'];
					$subCriteria->weight = $sub['weight'] * 100;
					$subCriteria->parent_id = $sub['parent_id'];
					$subCriteria->evaluation_id = null;
					$subCriteria->save();
				}
			}
			if( array_key_exists('criteria', $toUpdate) )
			{
				foreach( $toUpdate['criteria'] as $value )
				{
					$criteria = ScreeningEvaluationCriteria::find ( $value['id'] );
					$criteria->title = $value['name'];
					$criteria->weight = $value['weight'] * 100;
					$criteria->save();
				}
			}
			if( array_key_exists('subCriteria', $toUpdate) )
			{
				foreach( $toUpdate['subCriteria'] as $sub )
				{
					$subCriteria = ScreeningEvaluationCriteria::find ( $sub['id'] );
					$subCriteria->title = $sub['name'];
					$subCriteria->weight = $sub['weight'] * 100;
					$subCriteria->save();
				}
			}

			if( array_key_exists('subCriteria', $toRemove) )
			{
				foreach( $toRemove['subCriteria'] as $sub )
				{
					$subCriteria = ScreeningEvaluationCriteria::find ( $sub->id );
					if( $subCriteria ) { $subCriteria->delete(); }
				}
			}

			if( array_key_exists('criteria', $toRemove) )
			{
				foreach( $toRemove['criteria'] as $value )
				{
					$criteria = ScreeningEvaluationCriteria::find ( $value->id );
					if( $criteria ) { $criteria->delete(); }
				}
			}

			if( $status == 'publish' )
			{
				$evaluation->status = true;
				$evaluation->save();
			}

			$evaluationForm = ScreeningEvaluation::getEvaluationData( $id );
			return response()->json([
				'status' => true,
				'title' => 'saved',
				'message' => 'Evaluation saved successfully',
				'data' => $evaluationForm->categories
			]);
		}
	}

	/**
	 * Walk the submitted criteria/sub-criteria and return the first human-readable,
	 * name-pointing validation error (or null if everything is fine).
	 *
	 * Weights arrive as 0..1 fractions (the UI converts the percentages the user
	 * types) and are stored as fraction*100 into a double(5,2) column, so any value
	 * outside 0..1 both breaks the weighting maths and overflows the column. That
	 * range/format guard runs for BOTH draft and publish.
	 *
	 * $requireComplete distinguishes the two actions: on publish every field must be
	 * filled in; a draft may be saved half-complete, so we only validate the values
	 * that are actually present (a blank name or missing weight is allowed).
	 *
	 * When the failing field is the name itself, we fall back to the 1-based position.
	 */
	private static function firstNamedCriterionError( $criteria, $requireComplete = true )
	{
		foreach( (array) $criteria as $i => $criterion )
		{
			$cName = isset($criterion['name']) ? trim((string) $criterion['name']) : '';
			$cLabel = $cName !== ''
				? __('cycles.evaluation_named_criterion', ['name' => Str::limit($cName, 40)])
				: __('cycles.evaluation_named_criterion_position', ['position' => $i + 1]);
			$cWeightPresent = isset($criterion['weight']) && $criterion['weight'] !== '' && $criterion['weight'] !== null;

			if( $cName === '' )
			{
				if( $requireComplete ) { return __('cycles.evaluation_criterion_name_required_named', ['criterion' => $cLabel]); }
			}
			elseif( mb_strlen($cName) > ScreeningEvaluation::CRITERION_NAME_MAX_LENGTH )
			{
				return __('cycles.evaluation_criterion_name_too_long_named', ['criterion' => $cLabel, 'max' => ScreeningEvaluation::CRITERION_NAME_MAX_LENGTH]);
			}
			if( !$cWeightPresent )
			{
				if( $requireComplete ) { return __('cycles.evaluation_criterion_weight_required_named', ['criterion' => $cLabel]); }
			}
			elseif( !is_numeric($criterion['weight']) || $criterion['weight'] < 0 || $criterion['weight'] > 1 )
			{
				return __('cycles.evaluation_criterion_weight_range_named', ['criterion' => $cLabel]);
			}

			$subCriteria = isset($criterion['subCriteria']) && is_array($criterion['subCriteria']) ? $criterion['subCriteria'] : [];
			if( $requireComplete && count($subCriteria) < 1 )
			{
				return __('cycles.evaluation_subcriteria_required_named', ['criterion' => $cLabel]);
			}

			foreach( $subCriteria as $j => $sub )
			{
				$sName = isset($sub['name']) ? trim((string) $sub['name']) : '';
				$sLabel = $sName !== ''
					? __('cycles.evaluation_named_subcriterion', ['name' => Str::limit($sName, 40)])
					: __('cycles.evaluation_named_subcriterion_position', ['position' => $j + 1]);
				$sWeightPresent = isset($sub['weight']) && $sub['weight'] !== '' && $sub['weight'] !== null;

				if( $sName === '' )
				{
					if( $requireComplete ) { return __('cycles.evaluation_subcriterion_name_required_named', ['subcriterion' => $sLabel, 'criterion' => $cLabel]); }
				}
				elseif( mb_strlen($sName) > ScreeningEvaluation::CRITERION_NAME_MAX_LENGTH )
				{
					return __('cycles.evaluation_subcriterion_name_too_long_named', ['subcriterion' => $sLabel, 'criterion' => $cLabel, 'max' => ScreeningEvaluation::CRITERION_NAME_MAX_LENGTH]);
				}
				if( !$sWeightPresent )
				{
					if( $requireComplete ) { return __('cycles.evaluation_subcriterion_weight_required_named', ['subcriterion' => $sLabel, 'criterion' => $cLabel]); }
				}
				elseif( !is_numeric($sub['weight']) || $sub['weight'] < 0 || $sub['weight'] > 1 )
				{
					return __('cycles.evaluation_subcriterion_weight_range_named', ['subcriterion' => $sLabel, 'criterion' => $cLabel]);
				}
			}
		}

		return null;
	}

	public function get($formId, $stepId, $panel)
	{
		try {
			$judges = User::whereHas('roles', function ($query) {
				$query->where('name', 'Judger');
			})->get();

			$options = '';
			foreach ($judges as $judge) {
				$options .= "<option value=\"{$judge->id}\">{$judge->first_name_en} {$judge->last_name_en}</option>";
			}

			$screening = Screening::where('step_id', $stepId)
				->where('panel', $panel)
				->first();

			$selected = $screening && $screening->users ? json_decode($screening->users, true) : [];

			$selected = array_map('strval', (array)$selected);

			return response()->json([
				'options' => $options,
				'selected' => $selected
			]);
		} catch (\Exception $e) {
			Log::error('Error in screening get:', [
				'form_id' => $formId,
				'step_id' => $stepId,
				'panel' => $panel,
				'error' => $e->getMessage()
			]);
			return response()->json(['error' => 'Failed to fetch users'], 500);
		}
	}

	/**
	 * Get the results report for screening evaluation
	 *
	 * This function will return the metadata for the results report
	 * 
	 * @return \Illuminate\Http\Response
	 */
	public function resultsReport()
	{
		$l = app()->getLocale();
		$user = auth()->user();
		$orgIds = $user->organizations_ids;

		$steps = Step::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
			return $q->whereHas('cycle', function ($query) use ($orgIds) {
				$query->whereIn('organization_id', $orgIds);
			});
		})->where('stype', 2)->orderBy('from')->get();

			$id = $steps->pluck('id')->first();
			$cstep = Step::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->whereHas('cycle', function ($query) use ($orgIds) {
					$query->whereIn('organization_id', $orgIds);
				});
			})->where('stype', 2)->where('id', $id)->first();
		
		$metadata = ScreeningEvaluationResults::metadataReport($steps->pluck('id')->toArray());

		$allCompanies = collect();
		$_allSteps = collect();
		$allJudges = collect();
		$allResults = collect();
		$_allCompanies = collect();
		foreach ($metadata as $stepId => $data) {
			$allCompanies = $allCompanies->concat($data['companies']);
			$_allSteps = $_allSteps->concat($data['_steps']);
			$allJudges = $allJudges->concat($data['judges']);
			$allResults = $allResults->concat($data['results']);
			$_allCompanies = $_allCompanies->concat($data['_companies']);
		}
		$allCompanies = $allCompanies->unique('id')->values();
		$allJudges = $allJudges->unique('id')->values();
		$_allCompanies = $_allCompanies->unique('id')->values();

		$companies = $allCompanies;
		$_steps = $_allSteps;
		$judges = $allJudges;
		$_companies = $_allCompanies;
		$evaluation = $allResults;
		$results = $allResults;
		
		$form = null;
		if ($cstep) {
			$form = Step::where('cycle_id', $cstep->cycle_id)->where('stype', 1)->where('arr', $cstep->arr - 1)->first();
		}
		$svgs = ['notebook', 'search', 'idea', 'comments', 'trophy'];

		$_companies = $companies;
		return view('admin.settings.screening.resultsReport', compact('cstep', 'judges', 'form', 'steps', '_steps', 'id', 'svgs', '_companies', 'evaluation', 'results'));
	}

	/**
	 * Abort unless the step belongs to one of the current user's organizations
	 * (platform admins pass). A permission allows the action, not on whose data.
	 */
	private function authorizeStepOrganization($stepId): void
	{
		$user = auth()->user();
		if ($user->hasRole('admin')) {
			return;
		}

		$step = Step::with('cycle')->find($stepId);
		abort_unless($step && $step->cycle && $user->ownsOrganization($step->cycle->organization_id), 403);
	}
}
