<?php

namespace App\Http\Controllers;

use App\Company;
use App\Events\ChangeStep;
use App\Events\JudgeAssigned;
use App\Exports\ScreeningEvaluationExport;
use App\Formdata;
use App\Formfield;
use App\Jobs\ExportResScreenData;
use App\Jobs\ExportScreeneData;
use App\Jobs\SendChangeCycleNotificationJob;
use App\Notifications\ChangeCycle;
use App\Notifications\TemplateEmail;
use App\Screening;
use App\ScreeningEvaluation;
use App\ScreeningEvaluationCriteria;
use App\ScreeningEvaluationCustomFields;
use App\ScreeningEvaluationFieldsValues;
use App\ScreeningEvaluationResults;
use App\Factories\OnlineMeetingFactory;
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
use Maatwebsite\Excel\Facades\Excel;
use stdClass;

class ScreeningController extends Controller
{
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
		$cycleIds  = $steps->pluck('cycle_id')->unique();

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

		$forms = Step::whereIn('cycle_id', $cycleIds)
				->where('stype', 1)
				->get()
				->keyBy(fn ($f) => "{$f->cycle_id}-{$f->arr}");

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

			/** Form */
			$formKey = "{$step->cycle_id}-" . ($step->arr - 1);
			$form    = $forms->get($formKey);

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
				'screening_type'=> $screeningType,
				'judges'        => $judgesForStep,
				'companies'     => $companiesForStep,
				'form'          => $form ? (object) ['id' => $form->id, 'title' => $form->title] : null,
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

		// Determine the selected form: use form_id if set, else fall back to position-based lookup
		if ($cstep->form_id) {
			$form = \App\BuilderForm::find($cstep->form_id);
		} else {
			$formStep = Step::where('cycle_id', $cstep->cycle_id)->where('stype', 1)->where('arr', $cstep->arr - 1)->first();
			$form = $formStep && $formStep->data ? \App\BuilderForm::find($formStep->data) : null;
		}

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
			// Use the preceding step's ID (not BuilderForm ID) to also include companies still at the prior step
			$precedingStep = Step::where('cycle_id', $cstep->cycle_id)->where('arr', $cstep->arr - 1)->first();
			$companies = Company::whereIn('step', array_filter([$precedingStep?->id, $cstep->id]))->get();
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
					'session' => []
				];
				if ($screening && $screening->session_date) {
					$panelEntry['session'] = [
						'date' => $screening->session_date,
						'start_time' => $screening->start_time ? substr($screening->start_time, 0, 5) : '',
						'end_time' => $screening->end_time ? substr($screening->end_time, 0, 5) : '',
						'meeting_link' => $screening->meeting_link,
						'meeting_password' => $screening->meeting_password,
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

		return view('admin.settings.screening.show', compact('cstep', 'judgers_list', 'judgers_count', 'companies', 'judges', 'form', 'forms', 'steps', 'id', 'svgs', 'evaluationForm', 'judgingPanels', 'assignedJudgeIds', 'assignedCompanyIds'));
	}

	public function updateStepForm(Request $request)
	{
		$request->validate([
			'step_id' => 'required|exists:steps,id',
			'form_id' => 'nullable|exists:builder_forms,id',
		]);

		Step::where('id', $request->step_id)->update(['form_id' => $request->form_id]);

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

	public function addJudgingPanels(Request $request)
	{
		$request->validate([
			'step' => 'required|exists:steps,id',
			'panels' => 'nullable|array',
			'panels.*.judges' => 'required|array',
			'panels.*.companies' => 'required|array',
			'panels.*.session_date' => 'nullable|date',
			'panels.*.start_time' => 'nullable',
			'panels.*.end_time' => 'nullable',
		]);

		$stepId = $request->step;
		$panels = $request->panels ?? [];
		$step = Step::with('cycle.organization')->find($stepId);

		// Update Step list (judges mapping)
		$stepList = [];
		foreach ($panels as $index => $panel) {
			$stepList[$index + 1] = array_map('intval', $panel['judges']);
		}
		Step::where('id', $stepId)->update(['list' => $panels ? json_encode($stepList) : null]);

		// Clear existing screenings for this step
		Screening::where('step_id', $stepId)->delete();

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

			if (!empty($panel['session_date']) && !empty($panel['start_time'])) {
				try {
					$startDateTime = $panel['session_date'] . ' ' . $panel['start_time'];
					$duration = 60;
					if (!empty($panel['end_time'])) {
						$start = \Carbon\Carbon::parse($startDateTime);
						$end = \Carbon\Carbon::parse($panel['session_date'] . ' ' . $panel['end_time']);
						$duration = max($start->diffInMinutes($end), 30);
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
					'end_time' => $panel['end_time'] ?? null,
				]);
			}

			// Add meeting data to panels response
			$panels[$index]['meeting_link'] = $meetingLink;
			$panels[$index]['meeting_password'] = $meetingPassword;

			// Sync to Finalscreening table if step type is 5 (Final Screening)
			if ($step && $step->stype == 5) {
				foreach ($companyIds as $companyId) {
					// The Finalscreening model casts `judges` to array, so it json_encodes on save.
					// Pass the array directly — passing a pre-encoded string causes double-encoding.
					\App\Finalscreening::updateOrCreate(
						['step_id' => $stepId, 'company_id' => $companyId],
						['judges' => $judgeIds]
					);
				}
			}
		}

		return response()->json([
			'success' => true,
			'message' => __('admin.Saved_successfully'),
			'data' => $panels
		]);
	}

    public function deleteJudgingPanel(Request $request)
    {
        $request->validate([
            'step' => 'required|exists:steps,id',
            'panel' => 'required|integer'
        ]);

        $stepId = $request->step;
        $panelIdx = (int)$request->panel; // 1-based index from UI usually, or 0-based. Let's assume 1-based to match 'panel' column.
        
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
		foreach (Company::whereIn('step', [$fid, $sid])->get() as $comp) {
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
		$metadata = ScreeningEvaluationResults::metadata( $id );
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
		$companies = is_array($request->company) ? $request->company : [$request->company];
		$successCount = 0;
		$totalCount = count($companies);

		foreach ($companies as $companyId) {
			$approved = $by = null;
			$data = [];
			$company = Company::where('id', $companyId)->first();

			// can't chage step if company not have step before
			if (empty($company->step)) {
				continue;
			}

			if (!$company) {
				continue; // Skip if company not found
			}

			if ($request->step == "0") {
				$by = Auth::id();
				$data['new_step'] = 'Disapproved';
			} else {
				$cstep = Step::findOrFail($request->step);
				$approved = 1;
				$by = Auth::id();
				$data['new_step'] = $cstep?->title;
			}

			$data['last_step'] = null;
			if (!empty($company->step)) {
				$lastStep = Step::find($company->step);
				$data['last_step'] = $lastStep ? $lastStep->title : null;
			}

			if ($request->step == "0") {
				$update = $company->update(['cycle' => null, 'step' => null, 'approved' => null, 'approved_by' => $by]);
			} else {
				$update = $company->update(['step' => $request->step, 'approved' => $approved, 'approved_by' => $by]);
			}

			if ($update) {
				$successCount++;
				/// notify Company Owner via Mail
				SendChangeCycleNotificationJob::dispatch($companyId, $request->step, $data['last_step'], $data['new_step']);
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

		$metadata = ScreeningEvaluationResults::metadata($id);
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
		$request->validate([
			'action' => "required|in:{$status}",
			'stepId' => 'required|integer|exists:steps,id',
			'criteria' => 'required_if:action,publish|array',
			'criteria.*.id' => 'nullable|sometimes|integer|exists:screening_evaluation_criteria,id',
			'criteria.*.name' => 'required_if:action,publish|nullable|string|max:255',
			'criteria.*.weight' => 'required_if:action,publish|numeric|between:0,1',
			'criteria.*.subCriteria' => 'required_if:action,publish|array|min:1',
			'criteria.*.subCriteria.*.id' => 'nullable|sometimes|integer|exists:screening_evaluation_criteria,id',
			'criteria.*.subCriteria.*.name' => 'required_if:action,publish|nullable|string|max:255',
			'criteria.*.subCriteria.*.weight' => 'required_if:action,publish|numeric|between:0,1',
			'screening_guidelines' => 'nullable|string',
		]);

		$id = $request->stepId;
		$criteria = collect( $request->criteria );
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
}
