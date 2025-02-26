<?php

namespace App\Http\Controllers;

use App\Step;
use App\User;
use App\Company;
use App\Formdata;
use App\Formfield;
use App\Screening;
use App\Events\ChangeStep;
use Illuminate\Http\Request;
use App\Jobs\ExportScreeneData;
use App\Jobs\ExportResScreenData;
use App\Notifications\ChangeCycle;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use App\Notifications\TemplateEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;


class ScreeningController extends Controller
{
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
		return view('admin.settings.screening.index', compact('cstep', 'judges', 'form', 'steps', 'id', 'svgs'));
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
		return Redirect::route('admin')->with('msg', 'Saved');
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
}
