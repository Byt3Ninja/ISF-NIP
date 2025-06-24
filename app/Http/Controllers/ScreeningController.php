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

class ScreeningController extends Controller
{
	protected $whatsAppService;
	public function __construct(WhatsAppService $whatsAppService)
	{
		$this->whatsAppService = $whatsAppService;
	}
	public function index($id = 0)
	{
		$organization_id = auth()->user()->organization_id;
		$steps = Step::when(!is_null($organization_id), function ($q) use ($organization_id) {
			return $q->whereHas('cycle', function ($query) use ($organization_id) {
				$query->where('organization_id', $organization_id);
			});
		})->where('stype', 2)->orderBy('from')->get();
		$judges = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 7)->get();
		if ($id == 0) {
			$id = $steps->pluck('id')->first();
			$cstep = Step::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->whereHas('cycle', function ($query) use ($organization_id) {
					$query->where('organization_id', $organization_id);
				});
			})->where('stype', 2)->where('id', $id)->first();
		} else {
			$cstep = Step::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->whereHas('cycle', function ($query) use ($organization_id) {
					$query->where('organization_id', $organization_id);
				});
			})->where('stype', 2)->where('id', $id)->first();
		}
		if ($cstep) {
			$form = Step::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->whereHas('cycle', function ($query) use ($organization_id) {
					$query->where('organization_id', $organization_id);
				});
			})->where('cycle_id', $cstep->cycle_id)->where('stype', 1)->where('arr', $cstep->arr - 1)->first();
		} else {
			$form = collect([]);
		}
		$judgers_count = 0;
		$cList = collect(json_decode($cstep->list, true) ?? []);
		if (in_array($cstep->data, ['onetoall', 'grouptoall'])) {
			$judgers_count = $cList->count();
		} else {
			$judgers_count = $cList->flatten()->unique()->count();
		}
		$svgs = ['notebook', 'search', 'idea', 'comments', 'trophy'];

		$judgers_list = collect(json_decode($cstep->list))->map(function ($user) {
			if (is_array($user)) {
				$user = array_map('intval', $user);
			} else {
				$user = intval($user);
			}
			return $user;
		});

		$screenings = Screening::where('step_id', $cstep->id)->get();
		foreach ($judgers_list as $panel_id => $judgers_ids) {
			$users_list = $screenings->where('panel', $panel_id)->map(function ($screening) {
				if (is_array($screening->users)) {
					return $screening->users;
				} else {
					return collect(json_decode($screening->users, true) ?? []);
				}
			})->flatten()->unique()->values()->all();

			$judgers_list[$panel_id] = ['judgers' => $judgers_ids, 'companies' => $users_list];
		}
		$check = ScreeningEvaluation::where('step_id', $id)->first();
		if (empty($check)) {
			$evaluation = new ScreeningEvaluation();
			$evaluation->step_id = $id;
			$evaluation->save();
		}
		$evaluationForm = ScreeningEvaluation::getEvaluationData($id);
		$companies = Company::whereIn('step', [$form?->id ?? 0, $cstep?->id ?? 0])->get();
		return view('admin.settings.screening.index', compact('cstep', 'judgers_list', 'judgers_count', 'companies', 'judges', 'form', 'steps', 'id', 'svgs', 'evaluationForm'));
	}

	public function update(Request $request)
	{
		$judgesInput = $request->input('judges', []);

		// Decode judges input if it's a JSON string
		if (is_string($judgesInput)) {
			$judgesInput = json_decode($judgesInput, true) ?? [];
		}

		$judges = Arr::flatten($judgesInput);
		$judges = array_values(array_map('intval', array_unique($judges)));

		if (empty($judges)) {
			return Redirect::back()->with('message2', __('admin.Please_Choose_Judges'));
		}

		if ($request->step) {
			$type = Step::where('id', $request->step)->pluck('data')->first();
			$notifylist = User::whereIn('id', $judges)->get();

			$judgesInput = collect($judgesInput)->map(function ($panel) {
				if (is_array($panel)) {
					$panel = array_values(array_map('intval', array_unique($panel)));
				} else {
					$panel = intval($panel);
				}
				return $panel;
			})->toArray();

			$list = ($type === 'grouptogroup') ? $judgesInput : $judges;

			// if (is_string($list)) { $list = json_decode($list, true) ?? []; }

			Screening::where('step_id', $request->step)->delete();
			Formdata::where('form_id', $request->fid)->update(['step_id' => $request->step, 'screening' => '{}', 'comments' => '{}']);

			if ($list) {
				if ($type === 'grouptogroup') {
					$users = collect($request->users)->map(function ($user) {
						$user = array_values(array_map('intval', array_unique($user)));
						return $user;
					})->toArray();

					$_users = [];
					$_list = [];
					foreach ($users as $panel_id => $user) {
						if (count($user) && array_key_exists($panel_id, $list)) {
							$_users[$panel_id] = $user;
							$_list[$panel_id] = $list[$panel_id];
						}
					}
					$users = $_users;
					$list = $_list;

					$index = 1;
					$toCreate = [];
					$updatedList = [];
					foreach ($list as $panel_id => $panel) {
						foreach ((array) $panel as $user) {
							$users_list = $users[$panel_id] ?? [];
							$oneUser = ['step_id' => intval($request->step), 'user_id' => intval($user), 'panel' => $index];
							if (count($users_list)) {
								$oneUser['users'] = json_encode($users_list);
							}
							$toCreate[] = $oneUser;
						}
						$updatedList[$index] = $panel;
						$index++;
					}
					Screening::insert($toCreate);
					Step::where('id', $request->step)->update(['list' => json_encode($updatedList)]);
				} else {
					foreach ((array) $judges as $user) {
						Screening::updateOrCreate(['step_id' => $request->step, 'user_id' => $user, 'panel' => 0]);
					}
					Step::where('id', $request->step)->update(['list' => json_encode($list)]);
				}

				// Notify users via Mail if notify is enabled
				if ($request->notify) {
					$cstep = Step::findOrFail($request->step);
					foreach ($notifylist as $rname) {
						$user = new User();
						$user->email = $rname->email;
						$user->id = $rname->id;
						$rfullname = $rname->first_name_en . " " . $rname->last_name_en;
						$message = [
							"New judging - VI portal",
							"Dear " . $rfullname . ",",
							"Kindly note that we need your experiences to judge New Incubation Cycle registered users, the judging period starting from "
								. $cstep->from->format('d-m-Y') . " to " . $cstep->to->format('d-m-Y') . ", Check your account for more details.",
							"/dashboard",
							"You have a New Judging Request."
						];
						$MSG = new TemplateEmail($message);
						broadcast(new JudgeAssigned(
							$rname->id,
							"Kindly note that we need your experiences to judge New Incubation Cycle registered users, the judging period is from " . $cstep->from->format('d-m-Y') . " to " . $cstep->to->format('d-m-Y') . ".",
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
							$Massage .= "we need your experiences to judge New Incubation Cycle registered users, the judging period starting from " . $cstep->from->format('d-m-Y') . "\n";
							$Massage .= " to " . $cstep->to->format('d-m-Y') . ", Check your account for more details, \n";
							$Massage .= "Thanks from " . env('APP_NAME');
							$result = $this->whatsAppService->sendMessage($phoneNumber, $Massage);
							Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
						}
					}
				}
			}
			return Redirect::back()->with('message', 'Saved');
		} else {
			return Redirect::back()->with('message2', 'error');
		}
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
		$organization_id = auth()->user()->organization_id;

		$steps = Step::when(!is_null($organization_id), function ($q) use ($organization_id) {
			return $q->whereHas('cycle', function ($query) use ($organization_id) {
				$query->where('organization_id', $organization_id);
			});
		})->where('stype', 2)->orderBy('from')->get();
		if ($id == 0) {
			$id = $steps->pluck('id')->first();
			$cstep = Step::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->whereHas('cycle', function ($query) use ($organization_id) {
					$query->where('organization_id', $organization_id);
				});
			})->where('stype', 2)->where('id', $id)->first();
		} else {
			$cstep = Step::when(!is_null($organization_id), function ($q) use ($organization_id) {
				return $q->whereHas('cycle', function ($query) use ($organization_id) {
					$query->where('organization_id', $organization_id);
				});
			})->where('stype', 2)->where('id', $id)->first();
		}
		$getStep = Step::find($id);
		$companies = Company::where('cycle', $getStep->cycle_id)->get();
		$uniqueCycleIds = $companies->pluck('cycle')->unique();
		$_steps = Step::whereIn('cycle_id', $uniqueCycleIds)->orderBy('arr')->get();
		// $judges = User::join('screenings', 'user_id', 'users.id')->where('step_id', $id)->get();
		$l = app()->getLocale();
		$getUsers = Screening::where('step_id', $id)->with(['user'])->get();

		$judges = $getUsers->map(function ($item) use ($l) {
			$user = $item->user;
			$judger = new stdClass;
			$judger->id = $user->id;
			$judger->name = $user->{'first_name_' . $l} . ' ' . $user->{'last_name_' . $l};
			$judger->phone = $user->phone;
			$judger->email = $user->email;
			$judger->image_url = $user->image_url;
			return $judger;
		})->unique('id')->values();

		$judges = $judges->map(function ($item) use ($getUsers) {
			$startups = $getUsers->where('user_id', $item->id)->map(function ($i) {
				return $i->users;
			})->flatten()->unique()->filter(function ($i) {
				return !is_null($i);
			})->values();
			$item->startups = $startups;
			return $item;
		});

		if ($cstep->data != 'grouptogroup') {
			foreach ($judges as $index => $judge) {
				$judges[$index]->startups = $companies->pluck('id');
			}
		}

		$form = null;
		if ($cstep) {
			$form = Step::where('cycle_id', $cstep->cycle_id)->where('stype', 1)->where('arr', $cstep->arr - 1)->first();
		}
		// dd( $form);
		$svgs = ['notebook', 'search', 'idea', 'comments', 'trophy'];

		$evaluation = ScreeningEvaluation::getEvaluationData($cstep->id);
		$results = ScreeningEvaluationResults::where('evaluation_id', $evaluation->id)->get();
		foreach ($judges as $index => $judge) {
			$judges[$index]->companies = [];
			foreach ($judge->startups as $company_id) {
				$_company = new stdClass;
				$_company->id = $company_id;
				$_results = $results->where('company_id', $company_id)->where('user_id', $judge->id)->values();
				if ($_results->count() > 0) {
					$_company->status = $_results->where('status', 0)->count() > 0 ? 'Draft' : 'Published';
				} else {
					$_company->status = 'Not Evaluated';
				}
				$_company->results = $_results;
				$judges[$index]->companies[] = $_company;
			}
		}
		foreach ($evaluation->categories as $criteria) {
			$subCriteria = $criteria->subCriteria;
			foreach ($judges as $judges_key => $judge) {
				foreach ($judge->companies as $companies_key => $company) {
					foreach ($company->results as $results_key => $result) {
						if (!isset($result->weighted_score)) {
							$result->weighted_score = 0;
						}
						if (!isset($result->parent_criteria)) {
							$result->parent_criteria = $result->criteria->parent_id;
						}
						$getResult = $subCriteria->where('id', $result->criteria_id)->first();
						if (!empty($getResult)) {
							$weight = $getResult->weight;
							$weighted_score = (intval($result->score) / 5) * $weight;
							$result->weighted_score = $weighted_score;
						}
						$company->results[$results_key] = $result;
					}
					$judge->companies[$companies_key] = $company;
				}
			}
			$judges[$judges_key] = $judge;
		}
		return view('admin.settings.screening.results', compact('cstep', 'judges', 'form', 'steps', '_steps', 'id', 'svgs', 'companies', 'evaluation', 'results'));
	}

	public function changestep(Request $request)
	{
		$approved = $by = null;
		$company = Company::where('id', $request->company)->first();
		if ($request->step == "0") {
			$by = Auth::id();
		} else {
			$cstep = Step::findOrFail($request->step);
			if ($cstep->arr > 1) {
				$approved = 1;
				$by = Auth::id();
			}
			$data['new_step'] = $cstep->title;
		}
		if (!empty($company->step)) {
			$lastStep = Step::find($company->step);
			$data['last_step'] = $lastStep ? $lastStep->title : null;
		} else {
			$data['last_step'] = null;
		}
		if ($request->step == "0") {
			$update = $company->update(['cycle' => null, 'step' => null, 'approved' => null, 'approved_by' => $by]);
		}
		if ($request->step != "0") {
			$update = $company->update(['step' => $request->step, 'approved' => $approved, 'approved_by' => $by]);
			/// notify Company Owner via Mail
			$company_owner = $company->user;
			$data['owner_name_en'] = $company_owner->first_name_en . " " . $company_owner->last_name_en;
			$data['owner_name_ar'] = $company_owner->first_name_ar . " " . $company_owner->last_name_ar;
			$data['company_name_ar'] = $company_owner->company->name_ar;
			$data['company_name_en'] = $company_owner->company->name_en;
			$data['organization_name_ar'] = $cstep->cycle->organization->name_ar;
			$data['organization_name_en'] = $cstep->cycle->organization->name_en;
			$data['emailCompany'] = $company_owner->email;

			// Broadcast Event //
			broadcast(new ChangeStep($data));

			$recipients = User::role("Registered")->where('email', $company_owner->email)->get();

			// Notify all recipients
			if (!in_array(env('APP_ENV', 'local'), ['local'])) {
				try {
					foreach ($recipients as $recipient) {
						$recipient->notify(new ChangeCycle($data));
					}
				} catch (\Throwable $th) {
					// throw $th;
				}
			}
		}
		/// End of mail send
		if ($update) {
			return response()->json(['state' => 'success'], 200);
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
		return self::storeEvaluationCriteria($request, 'draft');
	}

	public function publish_evaluation(Request $request)
	{
		return self::storeEvaluationCriteria($request, 'publish');
	}

	private function storeEvaluationCriteria($request, $status)
	{
		$request->validate([
			'action' => "required|in:{$status}",
			'stepId' => 'required|integer|exists:steps,id',
			'criteria' => 'required|array',
			'criteria.*.id' => 'nullable|sometimes|integer|exists:screening_evaluation_criteria,id',
			'criteria.*.name' => 'required|string|max:255',
			'criteria.*.weight' => 'required|numeric|between:0,1',
			'criteria.*.subCriteria' => 'required|array|min:1',
			'criteria.*.subCriteria.*.id' => 'nullable|sometimes|integer|exists:screening_evaluation_criteria,id',
			'criteria.*.subCriteria.*.name' => 'required|string|max:255',
			'criteria.*.subCriteria.*.weight' => 'required|numeric|between:0,1',
		]);

		$id = $request->stepId;
		$evaluation = ScreeningEvaluation::where('step_id', $id)->first();
		if (!$evaluation) {
			$evaluation = new ScreeningEvaluation;
			$evaluation->step_id = $id;
			$evaluation->save();
		}
		$evaluationForm = ScreeningEvaluation::getEvaluationData($id);
		$current_status = strtolower($evaluationForm->status);
		if ($current_status == 'published') {
			return response()->json(['status' => false, 'message' => __('You can not make any changes on a published evaluation')], 200);
		} else {
			$toCreate = ['criteria' => [], 'subCriteria' => []];
			$toUpdate = ['criteria' => [], 'subCriteria' => []];
			$toRemove = ['criteria' => [], 'subCriteria' => []];

			$savedData = $evaluationForm->categories;
			$savedCriteriaById = collect($savedData)->keyBy('id');

			$allSeenCriteriaIds = [];
			$allSeenSubCriteriaIds = [];
			// Compare request criteria
			foreach ($request->criteria as $reqCriterion) {
				$reqId = $reqCriterion['id'];
				if (!$reqId) {
					// New main criterion to be created
					$toCreate['criteria'][] = $reqCriterion;
				} else {
					$allSeenCriteriaIds[] = $reqId;
					$savedCriterion = $savedCriteriaById[$reqId] ?? null;

					if ($savedCriterion) {
						// Check for updates in name or weight
						if (
							$savedCriterion->name !== $reqCriterion['name'] ||
							(float)$savedCriterion->weight !== (float)$reqCriterion['weight']
						) {
							$toUpdate['criteria'][] = $reqCriterion;
						}

						// --- SUBCRITERIA HANDLING ---
						$savedSubById = collect($savedCriterion->subCriteria)->keyBy('id');
						foreach ($reqCriterion['subCriteria'] as $reqSub) {
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
			foreach ($toCreate['criteria'] as $value) {
				$criteria = new ScreeningEvaluationCriteria;
				$criteria->title = $value['name'];
				$criteria->weight = $value['weight'] * 100;
				$criteria->evaluation_id = $evaluation->id;
				$criteria->save();
				foreach ($value['subCriteria'] as $sub) {
					$subCriteria = new ScreeningEvaluationCriteria;
					$subCriteria->title = $sub['name'];
					$subCriteria->weight = $sub['weight'] * 100;
					$subCriteria->parent_id = $criteria->id;
					$subCriteria->evaluation_id = null;
					$subCriteria->save();
				}
			}
			foreach ($toCreate['subCriteria'] as $sub) {
				$subCriteria = new ScreeningEvaluationCriteria;
				$subCriteria->title = $sub['name'];
				$subCriteria->weight = $sub['weight'] * 100;
				$subCriteria->parent_id = $sub['parent_id'];
				$subCriteria->evaluation_id = null;
				$subCriteria->save();
			}
			foreach ($toUpdate['criteria'] as $value) {
				$criteria = ScreeningEvaluationCriteria::find($value['id']);
				$criteria->title = $value['name'];
				$criteria->weight = $value['weight'] * 100;
				$criteria->save();
			}
			foreach ($toUpdate['subCriteria'] as $sub) {
				$subCriteria = ScreeningEvaluationCriteria::find($sub['id']);
				$subCriteria->title = $sub['name'];
				$subCriteria->weight = $sub['weight'] * 100;
				$subCriteria->save();
			}

			foreach ($toRemove['subCriteria'] as $sub) {
				$subCriteria = ScreeningEvaluationCriteria::find($sub->id);
				if ($subCriteria) {
					$subCriteria->delete();
				}
			}

			foreach ($toRemove['criteria'] as $value) {
				$criteria = ScreeningEvaluationCriteria::find($value->id);
				if ($criteria) {
					$criteria->delete();
				}
			}

			if ($status == 'publish') {
				$evaluation->status = true;
				$evaluation->save();
			}

			$evaluationForm = ScreeningEvaluation::getEvaluationData($id);
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
}
