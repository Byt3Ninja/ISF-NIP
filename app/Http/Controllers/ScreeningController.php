<?php

namespace App\Http\Controllers;

use App\Step;
use App\User;
use App\Company;
use App\Formdata;
use App\Formfield;
use App\Screening;
use App\ScreeningEvaluation;
use App\ScreeningEvaluationCriteria;
use App\ScreeningEvaluationResults;
use App\ScreeningEvaluationCustomFields;
use App\ScreeningEvaluationFieldsValues;
use App\Events\ChangeStep;
use Illuminate\Http\Request;
use App\Jobs\ExportScreeneData;
use App\Jobs\ExportResScreenData;
use App\Services\WhatsAppService;
use App\Notifications\ChangeCycle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Notifications\TemplateEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use stdClass;

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
			$form = [];
		}
		$cstep->judgers = count(json_decode($cstep->list, true)??[]) ?? 0 ;
		$svgs = ['notebook', 'search', 'idea', 'comments', 'trophy'];

		$check = ScreeningEvaluation::where('step_id', $id)->first();
		if( empty( $check ) )
		{
			$evaluation = new ScreeningEvaluation();
			$evaluation->step_id = $id;
			$evaluation->save();
		}
		$evaluationForm = $this->getEvaluationData( $id );
		return view('admin.settings.screening.index', compact('cstep', 'judges', 'form', 'steps', 'id', 'svgs', 'evaluationForm'));
	}

	public function update(Request $request)
	{
		if (!$request->judges) {
			return Redirect::back()->with('message2', __('Please_Choose_Judges'));
		}
		if ($request->step) {
			$type = Step::where('id', $request->step)->pluck('data')->first();
			$list = $request->judges;
			$notifylist = User::whereIn('id', array_values($list))->get();
			Step::where('id', $request->step)->update(['list' => json_encode($list)]);
			Screening::where('step_id', $request->step)->delete();
			Formdata::where('form_id', $request->fid)->update(['step_id' => $request->step, 'screening' => '{}', 'comments' => '{}']);
			if ($list) {
				if ($type == 'grouptogroup') {
					foreach ($list as $key => $panel) {
						$cpanel = $key;
						foreach ($panel as $user) {
							$users = json_decode($request->{'panel' . $cpanel});					
							if ($users == "") {
								Screening::create(['step_id' => $request->step, 'user_id' => $user, 'panel' => $cpanel]);
							} else {
								Screening::create(['step_id' => $request->step, 'user_id' => $user, 'panel' => $cpanel, 'users' => json_encode($users)]);
							}
						}
					}
				} else {
					foreach ($list as $user) {
						Screening::updateOrCreate(['step_id' => $request->step, 'user_id' => $user, 'panel' => 0]);
					}
				}
				/// notify user via Mail if notify enabled
				if ($request->notify) {
					$cstep = Step::findOrFail($request->step);
					foreach ($notifylist as $rname) {
						$user = new User();
						$user->email = $rname->email;
						$user->id = $rname->id;
						$rfullname = $rname->first_name_en . " " . $rname->last_name_en;
						$MSG = new TemplateEmail;
						$MSG->Message = ["New judging - VI portal", "Dear " . $rfullname . ",", "Kindly note that we need your experiences to judge New Incubation Cycle registered users, the judging period starting from " . $cstep->from->format('d-m-Y') .
							" to " . $cstep->to->format('d-m-Y') . ", Check your account for more details.", "/dashboard"];
						if( !in_array(env('APP_ENV', 'local'), ['local']) )
						{
							try {
								$user->notify($MSG);
							} catch (\Throwable $th) {
								// throw $th;
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
				/// End of mail send
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
		$judges = User::join('screenings', 'user_id', 'users.id')->where('step_id', $id)->get();
		$form = null;
		if ($cstep) {
			$form = Step::where('cycle_id', $cstep->cycle_id)->where('stype', 1)->where('arr', $cstep->arr - 1)->first();
		}
		// dd( $form);
		$svgs = ['notebook', 'search', 'idea', 'comments', 'trophy'];
		return view('admin.settings.screening.results', compact('cstep', 'judges', 'form', 'steps', '_steps', 'id', 'svgs'));
	}

	public function changestep(Request $request)
	{
		$approved = $by = null;
		$company = Company::where('id', $request->company)->first();
		$data['last_step'] = Step::findOrFail($company->step)->title;
		if ($request->step == 0) {
			$by = Auth::id();
		} else {
			$cstep = Step::findOrFail($request->step);
			if ($cstep->arr > 1) {
				$approved = 1;
				$by = Auth::id();
			}
			$data['new_step'] = $cstep->title;
		}
		
		$update = $company->update(['step' => $request->step, 'approved' => $approved, 'approved_by' => $by]);
		if ($request->step != 0) {
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
            if( !in_array(env('APP_ENV', 'local'), ['local']) )
            {
                try {
					foreach( $recipients as $recipient ) {
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
		return self::storeEvaluationCriteria( $request, 'draft' );
	}

	public function publish_evaluation(Request $request)
	{
		return self::storeEvaluationCriteria( $request, 'publish' );
	}

	private function storeEvaluationCriteria( $request, $status )
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
		if( !$evaluation )
		{
			$evaluation = new ScreeningEvaluation;
			$evaluation->step_id = $id;
			$evaluation->save();
			
		}
		$evaluationForm = $this->getEvaluationData( $id );
		$current_status = strtolower( $evaluationForm->status );
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
			foreach ($request->criteria as $reqCriterion)
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
			
						// --- SUBCRITERIA HANDLING ---
						$savedSubById = collect($savedCriterion->subCriteria)->keyBy('id');
						foreach ($reqCriterion['subCriteria'] as $reqSub)
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
			foreach( $toCreate['subCriteria'] as $sub )
			{
				$subCriteria = new ScreeningEvaluationCriteria;
				$subCriteria->title = $sub['name'];
				$subCriteria->weight = $sub['weight'] * 100;
				$subCriteria->parent_id = $sub['parent_id'];
				$subCriteria->evaluation_id = null;
				$subCriteria->save();
			}
			foreach( $toUpdate['criteria'] as $value )
			{
				$criteria = ScreeningEvaluationCriteria::find ( $value['id'] );
				$criteria->title = $value['name'];
				$criteria->weight = $value['weight'] * 100;
				$criteria->save();
			}
			foreach( $toUpdate['subCriteria'] as $sub )
			{
				$subCriteria = ScreeningEvaluationCriteria::find ( $sub['id'] );
				$subCriteria->title = $sub['name'];
				$subCriteria->weight = $sub['weight'] * 100;
				$subCriteria->save();
			}

			foreach( $toRemove['subCriteria'] as $sub )
			{
				$subCriteria = ScreeningEvaluationCriteria::find ( $sub->id );
				if( $subCriteria ) { $subCriteria->delete(); }
			}

			foreach( $toRemove['criteria'] as $value )
			{
				$criteria = ScreeningEvaluationCriteria::find ( $value->id );
				if( $criteria ) { $criteria->delete(); }
			}

			if( $status == 'publish' )
			{
				$evaluation->status = true;
				$evaluation->save();
			}

			$evaluationForm = $this->getEvaluationData( $id );
			return response()->json([
				'status' => true,
				'title' => 'saved',
				'message' => 'Evaluation saved successfully',
				'data' => $evaluationForm->categories
			]);

		}
	}

	private function getEvaluationData( $id )
	{
		$evaluationForm_q = "SELECT
				se.id AS evaluation_id,
				se.step_id,
				se.status,
				sec.id AS criteria_id,
				sec.title AS criteria_name,
				sec.weight AS criteria_weight,
				sec.parent_id AS criteria_parent_id,
				sec_child.id AS criteria_child_id,
				sec_child.title AS criteria_child_name,
				sec_child.weight AS criteria_child_weight
			FROM
				screening_evaluation se
			LEFT JOIN
				screening_evaluation_criteria sec ON se.id = sec.evaluation_id AND sec.parent_id IS NULL
			LEFT JOIN
				screening_evaluation_criteria sec_child ON sec.id = sec_child.parent_id AND sec_child.evaluation_id IS NULL
			WHERE
				se.step_id = ?
		";
		$evaluationForm = collect(DB::select($evaluationForm_q, [$id]));

		$evaluationForm = $evaluationForm->groupBy('evaluation_id')->map(function ( $e ) {
			$_e = $e->first();
			$evaluation = new stdClass;
			$evaluation->status = $_e->status == 0 ? 'Draft' : 'Published';
			$categories = $e->whereNull('criteria_parent_id')->groupBy('criteria_id')->map(function ( $criteria ) {
				$c = $criteria->first();
				$category = new stdClass;
				$category->id = $c->criteria_id;
				$category->name = $c->criteria_name;
				$category->weight = $c->criteria_weight / 100;
				$category->subCriteria = $criteria->map( function ( $subCriteria ) {
					$sub_category = new stdClass;
					$sub_category->id = $subCriteria->criteria_child_id;
					$sub_category->name = $subCriteria->criteria_child_name;
					$sub_category->weight = $subCriteria->criteria_child_weight / 100;
					return $sub_category;
				})->filter( function ( $subCategory ) { return !is_null( $subCategory->id ); })->values();
				return $category;
			})->filter(function( $category ) { return !is_null( $category->id ); })->values();
			$evaluation->categories = $categories;
			return $evaluation;
		})->values()->first();

		return $evaluationForm;
	}
}
