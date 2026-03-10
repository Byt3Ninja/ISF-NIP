<?php

namespace App\Http\Controllers;

use App\{Cycle, User, Step, Company, Formfield, Formdata, TeamFormation};
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Events\StartupJoining;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Notifications\StartupJoiningNotification;
use URL, Str, stdClass, Hash;

class ExternalCyclesController extends Controller
{
	public function publicJoin( $org_slug, $cycle_slug )
	{
		$step_q = "SELECT
				s.id AS step_id,
				s.public_slug AS step_slug,
				c.id AS cycle_id
			FROM
				steps s
			INNER JOIN
				cycles c ON s.cycle_id = c.id
			INNER JOIN
				organizations o ON c.organization_id = o.id
			WHERE
				o.short_code = ?
				AND s.public_slug = ?
				AND s.is_public = 1
				AND s.stype = 1
		";
		$step = collect( DB::select($step_q, [$org_slug, $cycle_slug]) )->first();
		if( !empty( $step ) )
		{
			if( auth()->check() )
			{
				$c = $step->cycle_id;
				$user = auth()->user();
				if( $user->company?->cycle && $user->company?->cycle != $c )
				{
					return abort(404);
				}
				else
				{
					return redirect()->to("dashboard/cycles/view/$c");
				}
			}
			else
			{
				//
			}
			dd( $step );
			// dd( auth()->check() );
			dd($org_slug, $cycle_slug);
		} else { abort(404); }
	}

	public function publicProgramJoin( $org_slug, $program_slug )
	{
		$program = $this->getProgram( $org_slug, $program_slug );
		if( empty( $program ) ) { abort(404); }
		else
		{
			if( $program->is_public == 1 )
			{
				$pages = DB::table('cycle_custom_pages')
					->where('cycle_id', $program->id)
					->where('is_active', 1)
					->where('visibility', 'public')
					->orderBy('row_no')
					->get();

				$pages = $pages->map(function( $page ) {
					$page->code = Str::random(5);
					return $page;
				});
				$winnersData = collect([]);
        		if( $program->announced && $program->visibility == 'public' )
				{
					$teams = TeamFormation::teams( $program->id );
					$winnersData = TeamFormation::winnersMapping( $teams );
				}

				$currentHackathon = ['announcement' => $program->announced == 1 ? true : false];
				return view('admin.settings.cycles.public-program-details', compact('program', 'pages', 'org_slug', 'program_slug', 'winnersData', 'currentHackathon'));
			} else { return abort(403, '<a href="/login">Login</a> To show this page'); }
		}
	}

	public function publicProgramApply( $org_slug, $program_slug )
	{
		$program = $this->getProgram( $org_slug, $program_slug );
		if( empty( $program ) ) { abort(404); }
		else
		{
			if( auth()->check() )
			{
				$c = $program->id;
				return redirect()->to("dashboard/cycles/view/$c");
			}
			else
			{
				return view('admin.settings.cycles.public-program-apply', compact('program', 'org_slug', 'program_slug'));
			}
		}
	}

	public function validateEmail( $org_slug, $program_slug )
	{
		$request = request();
		$program = $this->getProgram( $org_slug, $program_slug );
		if( empty( $program ) ) { return response()->json(['status' => false, 'message' => 'Program not found']); }
		else
		{
			if( $program->is_public == 1 )
			{
				$request->validate([
					'email' => 'required|email:dns',
				]);
				$checkForUser = User::where('email', $request->email)->first();
				$_step = $checkForUser ? 'login' : 'register';
				if( $_step == 'register' )
				{
					$step = Step::where('cycle_id', $program->id)->orderBy('arr')->first();
					$formFields = Formfield::where('step_id', $step->id)->get();
					$fields_ids = $formFields->pluck('id')->toArray();
					$fields = $step->fields()->orderBy('form_fields.id', 'asc')->get();
					$builderFields = DB::table('builder_fields')->whereIn('builder_form_id', $formFields->pluck('builder_forms_id')->unique()->toArray())->get();
					$fields = $fields->map(function( $field, $loop ) use ( $builderFields ) {
						$options = $field->pivot->options ? json_decode($field->pivot->options) : null;
						$label = trim( strtolower( $options?->label ?? '' ) );
						$check = $builderFields->filter(function( $item ) use ( $label ) {
							$_options = is_string( $item->options ) ? json_decode( $item->options ) : $item->options;
							$_label = trim( strtolower( $_options?->label ?? '' ) );
							return $label == $_label;
						})->where('field_id', $field->pivot->field_id)->where('builder_form_id', $field->pivot->builder_forms_id)->first();
						if( !empty( $check ) )
						{
							if( is_string( $check->options ) ) { $options = json_decode( $check->options ); }
						}
						$output = new stdClass;
						$output->field_name = 'field_' . $field->pivot->id;
						$output->type = $field->field_type;
						$output->options = $options;
						$output->id_for = 'input-fld-'. $loop;
						return $output;
					});
				} else { $fields = null; $fields_ids = null; }
				return response()->json(['status' => true, 'message' => 'Email checked successfully', 'step' => $_step, 'fields' => $fields, 'fields_ids' => $fields_ids]);
			} else { return response()->json(['status' => false, 'message' => 'Program not longer public']); }
		}
	}

	public function authenticateUser( $org_slug, $program_slug )
	{
		$request = request();
		$program = $this->getProgram( $org_slug, $program_slug );
		if( empty( $program ) ) { return response()->json(['status' => false, 'message' => 'Program not found']); }
		else
		{
			if( $program->is_public == 1 )
			{
				$request->validate([
					'email' => 'required|email:dns',
					'password' => 'required',
				]);
				$credentials = ['email' => $request->email, 'password' => $request->password];
				if( Auth::attempt($credentials) )
				{
					Auth::login(Auth::user());
					$c = $program->id;
					$url = URL::to("dashboard/cycles/view/$c");
					return response()->json(['status' => true, 'message' => 'Logged in successfully', 'url' => $url]);
				} else { return response()->json(['status' => false, 'message' => 'Invalid credentials']); }
			} else { return response()->json(['status' => false, 'message' => 'Program not longer public']); }
		}
	}

	public function registerUser( $org_slug, $program_slug )
	{
		$request = request();
		$program = $this->getProgram( $org_slug, $program_slug );
		if( empty( $program ) ) { return response()->json(['status' => false, 'message' => 'Program not found']); }
		else
		{
			if( $program->is_public == 1 )
			{
				$request->validate([
					'register-email' => 'required|email:dns',
					'register-first-name' => 'required',
					'register-last-name' => 'required',
				]);
				DB::beginTransaction();
				try {
					$password = Str::random(6);
					$request_token = Str::random(48);
					$user = new User();
					$user->fill([
						'email' => $request->input('register-email'),
						'first_name_en' => $request->input('register-first-name'),
						'first_name_ar' => $request->input('register-first-name'),
						'last_name_en' => $request->input('register-last-name'),
						'last_name_ar' => $request->input('register-last-name'),
						'guardian' => null,
						'phone' => null,
						'age' => null,
						'gender' => '',
						'country_id' => 1,
						'city_id' => null,
						'state_id' => null,
						'company_id' => null,
						'HaveCompany' => false,
						'password' => Hash::make( $password ),
						'api_token' => $request_token
					]);
					$user->save();
					$user->allotRole('registered');
					$user->sendEmailVerificationNotification();
					$company = new Company();

                	$company->name_en = $user->first_name_en . " " . $user->last_name_en;
                	$company->name_ar = $user->first_name_ar . " " . $user->last_name_ar;
                	$company->address_en = $user->address ?? 'Not Set';
                	$company->address_ar = $user->address ?? 'Not Set';
					$company->save();

					$user->company_id = $company->id;
					$user->HaveCompany = true;
					$user->save();

					$user_id = $user->id;
					$company_id = $company->id;

					$step = Step::where('cycle_id', $program->id)->orderBy('arr')->first();
					$fields = Formfield::where('step_id', $step->id)->get();

					$field_required_rules = collect( $fields->pluck('id')->toArray() )->mapWithKeys(function ( $id ) use ( $fields ) {
						$field_options = $fields->where('id', $id)->first()->options;
						if( $field_options->validation->required == 1 )
						{
							$rules = ['required'];
							if( isset($field_options->type) && $field_options->type == "email" ) { $rules[] = 'email'; }
							return [
								'field_' . $id => implode("|", $rules)
							];
						} else { return ['field_' . $id => 'nullable']; }
					});
					$dynamic_validator = Validator::make($request->all(), $field_required_rules->toArray(), [
						'required' => "field can't be left blank",
						'email' => 'field must be a valid email address'
					]);
					if( $dynamic_validator->fails() )
					{
						return response()->json(['error' => $dynamic_validator->errors()->all()], 422);
					}

					$company->cycle = $program->id;
					$company->step = $step->id;
					$company->save();

					foreach( collect( $fields->pluck('id')->toArray() ) as $fid )
					{
						$field_options = $fields->where('id', $fid)->first()->options;
						$field_name = 'field_' . $fid;
						if( $request->hasFile($field_name) )
						{
							$file = $request->file($field_name);
							$fileName = $file->getClientOriginalName() . '.' . $file->getClientOriginalExtension();
             				$file->move(public_path('formsubmissions/'), $fileName);
			 				$request->{$field_name} = 'formsubmissions/' . $fileName;
						}
            			Formdata::where('field_id', $fid)->where('company_id', $company_id)->delete();
            			Formdata::updateOrInsert([
                			'field_id' => $fid,
                			'company_id' => $company_id,
                			'user_id' => $user_id,
                			'form_id' => $step->id,
                			'step_id' => $step->id,
                			'question' => $field_options->label,
                			'answer' => is_array($request->{$field_name}) ? implode(',', $request->{$field_name}) : $request->{$field_name},
                			'screening' => '{}',
                			'comments' => '{}',
                			'created_at' => now(),
                			'updated_at' => now()
            			]);
					}

					$data = [];
        			$name = $user->first_name_en . ' ' . $user->last_name_en;
					$data = [
            			'name' => $name,
            			'email' => $user->email,
            			'step' => $step->title,
            			'cycle' => $program->title,
        			];

					if( !in_array(env('APP_ENV', 'local'), ['local']) )
					{
            			try {
                			broadcast(new StartupJoining($data));
                			$user->notify(new StartupJoiningNotification($data));
                			log::info('Sent notification to ' . $user->email);
            			} catch (\Throwable $th) {
							DB::rollBack();
                			throw $th;
            			}

            			$phoneNumber = $user->country_code_w_a . $user->whatsapp;
            			$Massage = "Dear " . $user->name . ",
            				Congratulations! You have successfully joined the" . $data['cycle'] . ", 
            				We are excited to have you on board and look forward to supporting your journey.
            				Thanks from " . env('APP_NAME');
						
						if( $phoneNumber )
						{
							$whatsAppService = new WhatsAppService();
							$result = $whatsAppService->sendMessage($phoneNumber, $Massage);
							Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
						}
        			}
					DB::commit();
					Auth::login( $user );
					$url = URL::to("dashboard/");
					return response()->json(['status' => true, 'message' => 'You have successfully joined the program', 'url' => $url]);
				}
				catch (\Exception $e) {
					DB::rollBack();
					Log::info('Exception: ', [$e] );
					return response()->json(['status' => false, 'message' => 'Exception: occur']);
				}
				catch (\Throwable $e) {
					DB::rollBack();
					Log::info('Throwable: ', [$e] );
					return response()->json(['status' => false, 'message' => 'Throwable: occur']);
				}
			} else { return response()->json(['status' => false, 'message' => 'Program not longer public']); }
		}
	}

	private function getProgram( $org_slug, $program_slug )
	{
		$program = DB::table('cycles AS p')
			->select([
				'p.id AS id',
				'p.title',
				'p.description',
				'p.start',
				'p.end',
				'p.is_public',
				'p.winners_announcement AS announced',
				'p.winners_visibility AS visibility',
				'o.name_en AS en_org_name',
				'o.name_ar AS ar_org_name',
			])
			->leftJoin('organizations AS o', 'p.organization_id', '=', 'o.id')
			->where('p.public_slug', $program_slug)
			->where('o.short_code', $org_slug)
			->first();

		if( !empty( $program ) )
		{
			$l = app()->getLocale();
			$start = $program->start;
			$end = $program->end;
			$form_step = DB::table('steps')->where('cycle_id', $program->id)->orderBy('arr', 'ASC')->where('stype', 1)->first();
			if( !empty( $form_step ) )
			{
				if( !empty( $form_step->from ) ) { $start = $form_step->from; }
				if( !empty( $form_step->to ) ) { $end = $form_step->to; }
			}
			$program->org = $l == 'ar' ? $program->ar_org_name : $program->en_org_name;
			$program->ranges = date_ranges( $start, $end );
		}
		return $program;
	}
}
