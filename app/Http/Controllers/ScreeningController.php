<?php

namespace App\Http\Controllers;

use App\Step;
use App\User;
use stdClass;
use App\Company;
use App\Formdata;
use App\Formfield;
use App\Screening;
use App\Events\ChangeStep;
use Illuminate\Support\Arr;
use App\ScreeningEvaluation;
use Illuminate\Http\Request;
use App\Events\JudgeAssigned;
use App\Jobs\ExportScreeneData;
use App\Jobs\ExportResScreenData;
use App\Services\WhatsAppService;
use App\Notifications\ChangeCycle;
use Illuminate\Support\Facades\DB;
use App\ScreeningEvaluationResults;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Notifications\TemplateEmail;
use App\ScreeningEvaluationCriteria;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use App\ScreeningEvaluationCustomFields;
use App\ScreeningEvaluationFieldsValues;
use Illuminate\Support\Facades\Redirect;
use App\Jobs\SendChangeCycleNotificationJob;

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
			$stepsQuery = Step::with(['cycle.organization'])
					->when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
							$q->whereHas('cycle', fn ($c) =>
									$c->whereIn('organization_id', $orgIds)
							);
					})
					->where('stype', 2);

			// Apply filters
			if ($request->filled('search')) {
					$search = $request->search;
					$stepsQuery->where(function ($q) use ($search) {
							$q->where('title', 'like', "%{$search}%")
									->orWhereHas('cycle', function ($c) use ($search) {
											$c->where('title', 'like', "%{$search}%");
									})
					->orWhereHas('cycle.organization', function ($o) use ($search) {
							$o->where('name_en', 'like', "%{$search}%")
									->orWhere('name_ar', 'like', "%{$search}%");
					});
					});
			}

			if ($request->filled('screening_type')) {
					$screeningType = $request->screening_type;
					$stepsQuery->where('data', $screeningType);
			}

			if ($request->filled('status')) {
					$today = now()->toDateString();
					switch ($request->status) {
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

			if ($request->filled('program')) {
					$stepsQuery->whereHas('cycle', function ($c) use ($request) {
							$c->where('id', $request->program);
					});
			}

			if ($request->filled('judge')) {
					$stepsQuery->where(function ($q) use ($request) {
							// Check if judge is in the JSON list field
							$q->whereRaw("JSON_CONTAINS(list, ?)", [json_encode($request->judge)])
									->orWhereRaw("JSON_CONTAINS(list, ?)", [json_encode([$request->judge])])
									->orWhereRaw("JSON_CONTAINS(list, ?)", [$request->judge]);
					});
			}

			if ($request->filled('startup')) {
					$stepsQuery->whereHas('companies', function ($c) use ($request) {
							$c->where('id', $request->startup);
					});
			}

			$steps = $stepsQuery->orderBy('from')->get();

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
			$screeningSteps = $steps->map(function ($step) use (
					$judges,
					$screenings,
					$companies,
					$forms
			) {
					$judgersList = collect(json_decode($step->list, true) ?? [])
							->map(fn ($j) => is_array($j) ? array_map('intval', $j) : [(int) $j]);

					/** Collect judges for step */
					$judgeIds = $judgersList->flatten()->unique();

					$judgesForStep = $judgeIds
							->map(fn ($id) => $judges->get($id))
							->filter()
							->map(fn ($j) => (object) [
									'id'   => $j->id,
									'name' => "{$j->first_name_en} {$j->last_name_en}",
							])
							->values();

					/** Companies */
					$companiesForStep = ($companies[$step->id] ?? collect())
							->map(fn ($c) => (object) [
									'id'   => $c->id,
									'name' => $c->name_en,
							])
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
							'program'       => $step->cycle
									? (object) ['id' => $step->cycle->id, 'title' => $step->cycle->title]
									: null,
							'organization'  => $step->cycle?->organization
									? (object) [
											'id'   => $step->cycle->organization->id,
											'name' => $step->cycle->organization->name,
									]
									: null,
							'start_date'    => optional($step->from)->format('Y-m-d'),
							'end_date'      => optional($step->to)->format('Y-m-d'),
							'screening_type'=> $screeningType,
							'judges'        => $judgesForStep,
							'companies'     => $companiesForStep,
							'form'          => $form
									? (object) ['id' => $form->id, 'title' => $form->title]
									: null,
					];
			});

			return view('admin.settings.screening.index', compact('screeningSteps'));
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
		if ($cstep) {
			$form = Step::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
				return $q->whereHas('cycle', function ($query) use ($orgIds) {
					$query->whereIn('organization_id', $orgIds);
				});
			})->where('cycle_id', $cstep->cycle_id)->where('stype', 1)->where('arr', $cstep->arr - 1)->first();
		} else {
			$form = collect([]);
		}
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
		if( $cstep ) { $companies = Company::whereIn('step', [$form?->id ?? 0, $cstep?->id ?? 0])->get(); } else { $cstep = []; }
		return view('admin.settings.screening.show', compact('cstep', 'judgers_list', 'judgers_count', 'companies', 'judges', 'form', 'steps', 'id', 'svgs', 'evaluationForm'));
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
		$l = app()->getLocale();
		$id = $request->step;
		$company = intval( $request->company );
		$metadata = ScreeningEvaluationResults::metadata( $id );
		$evaluationForm = $metadata['evaluation'];
		$results = $metadata['results']->where('company_id', $company)->values();
		$judges = $metadata['judges']->map(function( $item ) use ( $company ) {
			$companies = $item->companies->where('id', $company)->values();
			$item->companies = $companies;
			return $item;
		});
		$criteria = $evaluationForm ? $evaluationForm->categories : collect();
		$criteria = $criteria->map(function( $item ) use ( $results ) {
			$subs = $item->subCriteria;
			$subs = $subs->map(function( $sub ) use ( $results ) {
				$weight = $sub->weight;
				$scoped_results = $results->where('criteria_id', $sub->id)->values()->map(function( $oneResult ) use ( $weight ) {
					$output = new stdClass;
					$output->id = $oneResult->id;
					$output->judger_id = $oneResult->user_id;
					$output->judger = new stdClass;
					$output->judger->id = $oneResult->user_id;
					$output->judger->name = $oneResult->user->name;
					$output->given_score = intval( $oneResult->score );
					$output->max = 5;
					$score = ( $oneResult->score / $output->max );
					$output->score = $score * 100;
					$output->comment = $oneResult->comment;
					$output->status = $oneResult->status == true ? 'Published' : 'Draft';
					$output->weight = $weight;
					$output->weighted_score = ( $score * $weight ) * 100;
					return $output;
				});
				$sub->results = $scoped_results;
				$sub->total = $sub->results->sum('given_score');
				$sub->max = $sub->results->sum('max');
				$sub->count = $sub->results->count();
				$sub->average = $sub->max > 0 ? ( $sub->total / $sub->max ) : 0;
				$sub->weighted_total = ( round( $sub->average * 100 ) / 100 ) * $sub->weight;
				return $sub;
			});
			$item->weight = $item->weight;
			$item->total = $subs->sum('total');
			$item->max = $subs->sum('max');
			$item->weighted_total = round( $subs->sum('weighted_total') * 100 ) / 100;
			$item->value = ( round( $item->weighted_total * 100 ) / 100 ) * $item->weight;
			$item->subCriteria = $subs;
			return $item;
		});
		$_criteria = $criteria->map(function( $item ) {
			$output = new stdClass;
			$output->id = $item->id;
			$output->name = $item->name;
			$output->weight = $item->weight;
			$output->details = $item->subCriteria->map(function( $sub ) {
				$output = new stdClass;
				$output->id = $sub->id;
				$output->name = $sub->name;
				$output->weight = $sub->weight;
				$output->total = $sub->total;
				$output->max = $sub->max;
				$output->count = $sub->count;
				$output->average = $sub->average;
				$output->value = round( $sub->weighted_total * 100 ) / 100;
				$output->details = $sub->results->map(function( $result ) {
					$output = new stdClass;
					$output->judger = $result->judger;
					$output->given_score = $result->given_score;
					$output->max = $result->max;
					$output->score = $result->score / 100;
					$output->comment = $result->comment;
					$output->status = $result->status;
					$output->weight = $result->weight;
					$output->value = $result->weighted_score / 100;
					return $output;
				});
				return $output;
			});
			$output->score = ( round( $output->details->sum('value') * 100 ) / 100 );
			$output->value = round( $output->score * $output->weight * 100 ) / 100;
			return $output;
		});

		$customFields = collect(DB::select("SELECT
				cfv.id AS id,
				cf.name AS field,
				cfv.value AS value,
				cfv.is_approved AS is_approved,
				cfv.user_id AS judger_id,
				u.first_name_en AS judger_first_name_en,
				u.last_name_en AS judger_last_name_en,
				u.first_name_ar AS judger_first_name_ar,
				u.last_name_ar AS judger_last_name_ar
			FROM
				screening_evaluation_fields_values cfv
			LEFT JOIN
				screening_evaluation_custom_fields cf ON cf.id = cfv.custom_field_id
			LEFT JOIN
				users u ON u.id = cfv.user_id
			WHERE
				cfv.company_id = ?
				AND cf.evaluation_id = ?
		", [
			$company,
			$evaluationForm?->id
		]))->map(function( $item ) use ( $l ) {
			$output = new stdClass;
			$output->id = $item->id;
			$output->field = $item->field;
      		$output->value = $item->value;
			$output->judger_id = $item->judger_id;
			$output->approval = intval( $item->is_approved ) === 1 ? 'approved' : ( $item->is_approved === null || intval( $item->is_approved ) === 2 ? 'pending' : 'disapproved' );
	  		$output->name = trim( str_replace(['   ', '  '], ' ', $item->{'judger_first_name_' . $l} . ' ' . $item->{'judger_last_name_' . $l}) );
      		return $output;
		});
		$judges = $judges->map(function( $item ) use ( $customFields, $criteria ) {
			$judger_id = $item->id;
			$output = new stdClass;
			$output->id = $item->id;
			$output->name = $item->name;
			$output->phone = $item->phone;
			$output->email = $item->email;
			$output->image_url = $item->image_url;
			$companies = $item->companies->map(function( $company ) use ( $customFields, $judger_id, $criteria ) {
				$comment = $customFields->where('judger_id', $judger_id)->where('field', 'final_comment')->first();
				$company->comment = !empty( $comment ) ? $comment->value : '';
				$company->approval = !empty( $comment ) ? $comment->approval : 'pending';
				$details = $criteria->map(function( $item ) use ( $judger_id ) {
					$details = $item->subCriteria->filter(function( $sub ) use ( $judger_id ) {
						$results = $sub->results->where('judger_id', $judger_id)->values()->first();
						return !empty( $results );
					})->values()->map(function( $sub ) use ( $judger_id ) {
						$results = $sub->results->where('judger_id', $judger_id)->values()->first();
						$output = new stdClass;
						$output->id = $sub->id;
  						$output->name = $sub->name;
  						$output->weight = $sub->weight;
						$output->score = $results->given_score;
						$output->max = $results->max;
						$output->comment = $results->comment;
						$output->status = $results->status;
						$output->value = ( $results->given_score / $results->max ) * $sub->weight;
						return $output;
					});

					$output = new stdClass;
					$output->id = $item->id;
					$output->name = $item->name;
					$output->weight = $item->weight;
					$output->score = round( $details->sum('value') * 100 ) / 100;
					$output->value = ( round( ( $output->score * $output->weight ) * 100 ) / 100 );
					$output->details = $details;
					return $output;
				});
				$output = new stdClass;
				$output->id = $company->id;
				$output->status = $company->status;
				$output->value = ( round( $details->sum('value') * 100 ) / 100 );
				$output->comment = $company->comment;
				$output->approval = $company->approval;
				$output->details = $details;
				return $output;
			})->first();
			$output->evaluation = $companies;
			return $output;
		})->filter(function( $item ) {
			return !empty( $item->evaluation );
		})->values();
		$return = [
			'total' => ( ( round( $_criteria->sum('value') * 100 ) ) / 100),
			'criteria' => $_criteria,
			'judges' => $judges
		];
		return $return;
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
			'screening_guidelines' => 'nullable|string|max:5000',
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
					'message' => 'Screening guidelines saved successfully'
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
