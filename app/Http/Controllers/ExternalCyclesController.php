<?php

namespace App\Http\Controllers;

use App\{Cycle, User, Step, Company, Formfield, Formdata, TeamFormation, Settings};
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Events\StartupJoining;
use App\Services\WhatsAppService;
use App\Services\FormSectionStructurer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use App\Notifications\StartupJoiningNotification;
use App\Notifications\WelcomeUserNotification;
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

		$firstFormStep = DB::table('steps')
			->where('cycle_id', $program->id)
			->where('stype', 1)
			->orderBy('arr', 'ASC')
			->first();

		if ($firstFormStep) {
			$stepStart = $firstFormStep->from ? \Carbon\Carbon::parse($firstFormStep->from) : null;
			$stepEnd = $firstFormStep->to ? \Carbon\Carbon::parse($firstFormStep->to) : null;
			$now = now();

			$isOpen = ($stepStart === null || $now->gte($stepStart)) && ($stepEnd === null || $now->lte($stepEnd));
			if (!$isOpen) {
				return redirect()->to("/o/" . ($program->org_slug ?? $org_slug))->with('error', app()->getLocale() == 'ar' ? 'التقديم مغلق حالياً.' : 'Applications are currently closed.');
			}
		} else {
			return redirect()->to("/o/" . ($program->org_slug ?? $org_slug))->with('error', app()->getLocale() == 'ar' ? 'التقديم مغلق حالياً.' : 'Applications are currently closed.');
		}

		if( auth()->check() )
		{
			$user = auth()->user();
			$c = $program->id;
			if( $user->hasRole('Registered') )
			{
				if( $user?->company?->stepdata?->stype == 1 )
				{
					return redirect()->to("dashboard/cycles/view/" . $user->company->step );
				}
			}
			return redirect()->to("dashboard/dashboard");
			// return redirect()->to("dashboard/cycles/view/$c");
		}
		else
		{
			$app_settings = Settings::find(1);
			$VFEmail = $app_settings?->VFEmail ?? 1;
			return view('admin.settings.cycles.public-program-apply', compact('program', 'org_slug', 'program_slug', 'VFEmail'));
		}
	}

	public function validateEmail( $org_slug, $program_slug )
	{
		$request = request();
		$program = $this->getProgram( $org_slug, $program_slug );
		if( empty( $program ) ) { return response()->json(['status' => false, 'message' => __('cycles.program_not_found') ]); }
		else
		{
			if( $program->is_public == 1 )
			{
				// Validate at THIS first step with the same strictness the final
				// register step applies (register-email => email:dns, see
				// registerUser). Previously this used a lenient `email` rule, so
				// an address with an unresolvable domain (e.g. "nano@niny.ts")
				// passed the Email step and only failed at the final Application
				// step — where the user could no longer correct it in context.
				$request->validate([
					'email' => 'required|email:dns',
				], [
					'email.email' => app()->getLocale() == 'ar' ? 'يرجى إدخال بريد إلكتروني صحيح.' : 'Please enter a valid email address.',
					'email.required' => app()->getLocale() == 'ar' ? 'يرجى إدخال بريد إلكتروني صحيح.' : 'Please enter a valid email address.',
				]);
				$checkForUser = User::where('email', $request->email)->first();
				$_step = $checkForUser ? 'login' : 'register';
				if( $_step == 'register' )
				{
					$step = Step::where('cycle_id', $program->id)->orderBy('arr')->first();
					$formFields = Formfield::where('step_id', $step->id)->orderBy('id')->get();
					$sections = FormSectionStructurer::structure($formFields);
				} else { $sections = null; }
				return response()->json(['status' => true, 'message' => __('cycles.email_checked_successfully'), 'step' => $_step, 'sections' => $sections]);
			} else { return response()->json(['status' => false, 'message' => __('cycles.program_not_public') ]); }
		}
	}

	public function authenticateUser( $org_slug, $program_slug )
	{
		$request = request();
		$program = $this->getProgram( $org_slug, $program_slug );
		if( empty( $program ) ) { return response()->json(['status' => false, 'message' => __('cycles.program_not_found') ]); }
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
					$user = auth()->user();
					$url = URL::to("dashboard/dashboard");
					if( $user->hasRole('Registered') )
					{
						if( $user?->company?->stepdata?->stype == 1 )
						{
							$url = URL::to("dashboard/cycles/view/" . $user->company->step );
						}
					}
					return response()->json(['status' => true, 'message' => __('cycles.login_success') , 'url' => $url]);
				} else { return response()->json(['status' => false, 'message' => __('cycles.invalid_credentials') ] ); }
			} else { return response()->json(['status' => false, 'message' => __('cycles.program_not_public') ]); }
		}
	}

	public function registerUser( $org_slug, $program_slug )
	{
		$request = request();
		$program = $this->getProgram( $org_slug, $program_slug );
		if( empty( $program ) ) { return response()->json(['status' => false, 'message' => __('cycles.program_not_found') ]); }
		else
		{
			if( $program->is_public == 1 )
			{
				$request->validate([
					'register-email' => 'required|email:dns',
					'register-first-name' => 'required',
					'register-last-name' => 'nullable|sometimes',
				]);

				// Validate the dynamic form fields (required/email/number rules) BEFORE
				// creating any records, so a rejected submission never leaves an
				// orphaned user/company.
				$step = Step::where('cycle_id', $program->id)->orderBy('arr')->first();
				$fields = Formfield::where('step_id', $step->id)->orderBy('id')->get();

				// Recompute the visited section path SERVER-SIDE from the submitted
				// answers (never trust a client-declared path) so that fields from
				// sections the wizard never showed are neither required nor saved,
				// even if a tampered request includes them.
				$sections = FormSectionStructurer::structure($fields);
				$visitedSectionIndexes = FormSectionStructurer::resolveVisitedPath(
					$sections,
					fn ($fieldName) => $request->input($fieldName)
				);
				$visitedFieldIds = collect(FormSectionStructurer::visitedFieldIds($sections, $visitedSectionIndexes));
				$fields = $fields->whereIn('id', $visitedFieldIds->all())->values();

				$l = app()->getLocale();
				// GV-3679: "positive number" is only accurate when the floor is 0
				// (the default). When an author sets a non-zero min, state the
				// actual bound instead. Mirrors numberMinMessage() on the client.
				$number_min_msg = $l == 'ar' ? 'يرجى إدخال رقم موجب صحيح.' : 'Please enter a valid positive number.';
				$number_min_msg_tpl = $l == 'ar' ? 'يرجى إدخال رقم لا يقل عن :min.' : 'Please enter a number no less than :min.';
				$number_max_msg_tpl = $l == 'ar' ? 'يرجى إدخال رقم لا يتجاوز :max.' : 'Please enter a number no greater than :max.';
				$numeric_msg = $l == 'ar' ? 'يرجى إدخال رقم صحيح.' : 'Please enter a valid number.';
				$email_regex_msg = $l == 'ar' ? 'يرجى إدخال بريد إلكتروني صحيح (مثال: name@example.com).' : 'Please enter a valid email address (e.g. name@example.com).';
				$dynamic_field_messages = [];

				$field_required_rules = collect( $fields->pluck('id')->toArray() )->mapWithKeys(function ( $id ) use ( $fields, &$dynamic_field_messages, $number_min_msg, $number_min_msg_tpl, $number_max_msg_tpl, $numeric_msg, $email_regex_msg ) {
					$field_options = $fields->where('id', $id)->first()->options;
					$field_name = 'field_' . $id;
					$required = isset($field_options->validation->required) && $field_options->validation->required == 1;

					$rules = [$required ? 'required' : 'nullable'];
					$type = $field_options->type ?? null;

					if ($type == 'email') {
						// email:rfc catches malformed addresses; the regex additionally
						// requires a dotted domain (e.g. rejects "user@example") without
						// a live DNS lookup, per GV-3832.
						$rules[] = 'email:rfc';
						$rules[] = 'regex:/.+@.+\..+/';
						$dynamic_field_messages[$field_name . '.regex'] = $email_regex_msg;
					} elseif ($type == 'number') {
						// Non-negative by default (GV-3831/GV-3453): when the field
						// author set no min, floor at 0. Respect an authored min/max
						// when present.
						$min = (isset($field_options->min) && $field_options->min !== '') ? $field_options->min : 0;
						$rules[] = 'numeric';
						$rules[] = 'min:' . $min;
						$dynamic_field_messages[$field_name . '.numeric'] = $numeric_msg;
						$dynamic_field_messages[$field_name . '.min'] = ((float) $min == 0.0)
							? $number_min_msg
							: str_replace(':min', $min, $number_min_msg_tpl);
						if (isset($field_options->max) && $field_options->max !== '') {
							$rules[] = 'max:' . $field_options->max;
							$dynamic_field_messages[$field_name . '.max'] = str_replace(':max', $field_options->max, $number_max_msg_tpl);
						}
					}

					return [
						$field_name => implode("|", $rules)
					];
				});

				// "Other" free-text is mandatory once picked, on both layers.
				// Detection keys off the SUBMITTED ANSWER still carrying the
				// "__OTHER__" marker — NOT the presence of the `{field}_other`
				// companion input. The client deletes `{field}_other` before POST
				// and only rewrites "__OTHER__"->typed-text when the box is
				// non-empty, so an empty "Other" leaves the raw "__OTHER__" in the
				// answer. Keying off the companion input (as before) meant the
				// rule was never added and the check was dead on this layer; this
				// mirrors Layer 1 (CyclesController@saveForm) and is bypass-safe.
				$other_required_msg = app()->getLocale() == 'ar'
					? 'هذا الحقل مطلوب عند اختيار "أخرى".'
					: 'This field is required when "Other" is selected.';
				$other_messages = [];
				foreach ( $fields as $field ) {
					$field_options = $field->options;
					$optionValues = $field_options->values ?? [];
					if (!is_array($optionValues)) {
						$optionValues = array_map('trim', explode(',', (string) $optionValues));
					}
					if (in_array('__OTHER__', $optionValues, true)) {
						$field_name = 'field_' . $field->id;
						$answer = $request->input($field_name);
						$answerHasOther = is_array($answer)
							? in_array('__OTHER__', $answer, true)
							: $answer === '__OTHER__';
						if ($answerHasOther) {
							// The answer still carries the marker => Other was
							// selected but the free-text was left empty. APPEND a
							// closure rule to the field's existing rule string
							// (don't overwrite its required/type rules) that fails
							// while the raw "__OTHER__" marker is still present.
							// A closure handles both scalar (radio/dropdown) and
							// array (checkbox) answers; `not_in` does not apply
							// element-wise to arrays.
							$existingRule = $field_required_rules->get($field_name, 'nullable');
							$rulesForField = is_array($existingRule) ? $existingRule : explode('|', $existingRule);
							$rulesForField[] = function ($attribute, $value, $fail) use ($other_required_msg) {
								$hasMarker = is_array($value)
									? in_array('__OTHER__', $value, true)
									: $value === '__OTHER__';
								if ($hasMarker) {
									$fail($other_required_msg);
								}
							};
							$field_required_rules->put($field_name, $rulesForField);
						}
					}
				}

				$dynamic_validator = Validator::make($request->all(), $field_required_rules->toArray(), array_merge([
					'required' => app()->getLocale() == 'ar' ? "هذا الحقل مطلوب." : "field can't be left blank",
					'email' => app()->getLocale() == 'ar' ? 'يرجى إدخال بريد إلكتروني صحيح.' : 'Please enter a valid email address.'
				], $other_messages, $dynamic_field_messages));
				if( $dynamic_validator->fails() )
				{
					return response()->json(['error' => $dynamic_validator->errors()->all()], 422);
				}

				DB::beginTransaction();
				try {
					$password = Str::random(6);
					$request_token = Str::random(48);
					$user = new User();
					$user->fill([
						'email' => $request->input('register-email'),
						'first_name_en' => $request->input('register-first-name'),
						'first_name_ar' => $request->input('register-first-name'),
						'last_name_en' => $request->input('register-last-name', '') ?? '',
						'last_name_ar' => $request->input('register-last-name', '') ?? '',
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
					$user->email_verified_at = now();
					$user->must_change_password = true;
					$user->accept_email = true;
					$user->save();
					$user->allotRole('registered');
					// $user->sendEmailVerificationNotification();
					if ($user->claimEmailSlot('user_welcome_sent_at')) {
						$user->notify(new WelcomeUserNotification($user, $password));
					}
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
                			// Store multi-value answers as JSON, matching
                			// CyclesController@saveForm. implode(',') was lossy and
                			// ambiguous: a checkbox answer came back as one opaque
                			// string, so the portal's read-only view could not tell
                			// the selected options apart from the "Other" free text
                			// (it showed everything as Other), and any free text
                			// containing a comma was silently truncated. The reader
                			// still accepts the legacy comma format for existing rows.
                			'answer' => is_array($request->{$field_name}) ? json_encode($request->{$field_name}) : $request->{$field_name},
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
			} else { return response()->json(['status' => false, 'message' => __('cycles.program_not_public') ]); }
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
				'p.start_color',
				'p.end_color',
				'p.main_color',
				'o.slug AS org_slug',
				'o.name_en AS en_org_name',
				'o.name_ar AS ar_org_name',
				'o.bio AS bio',
				'o.linkedin_company_url AS linkedin',
				'o.twitter_handle AS twitter',
				'o.facebook_page_url AS facebook',
				'o.instagram_profile_url AS instagram',
				'o.youtube_channel_url AS youtube',
				'o.tiktok_url AS tiktok',
			])
			->leftJoin('organizations AS o', 'p.organization_id', '=', 'o.id')
			->where('p.public_slug', $program_slug)
			->where(function($query) use ($org_slug) {
				$query->where('o.short_code', $org_slug)
					->orWhere('o.slug', $org_slug);
			})
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
			$program->registration_start = $start . ' 00:00:00';
			$program->registration_end = $end . ' 23:59:59';
			$program->org = $l == 'ar' ? $program->ar_org_name : $program->en_org_name;
			$program->ranges = date_ranges( $start, $end );

			$program->start_color = preg_replace('/^#+/', '#', $program->start_color ?: '#6366f114');
			$program->end_color = preg_replace('/^#+/', '#', $program->end_color ?: '#0891b214');
			$program->main_color = preg_replace('/^#+/', '#', $program->main_color ?: '#6366f1');
		}
		return $program;
	}
}
