<?php

namespace App\Http\Controllers;
use App\Activity;
use App\Almuni;
use App\City;
use App\Country;
use App\Currency;
use App\Cycle;
use App\CyclesMentors;
use App\serviceProviders;
use App\Expertise;
use App\Http\Requests\UpdateClientProfilePasswordRequest;
use App\Http\Requests\UpdateClientProfileRequest;
use App\InviteMentor;
use App\MentorshipRequest;
use App\MentorshipTask;
use App\Notifications\ConfirmEmailUpdateNotification;
use App\Otp;
use App\Schedule;
use App\Services\MentorshipAvailabilityService;
use App\Services\NotificationSettingsService;
use App\Services\WhatsAppService;
use App\Settings;
use App\State;
use App\StaticCategory;
use App\User;
use App\UsersBio;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\In;
use Intervention\Image\ImageManagerStatic as Image;
use PragmaRX\Google2FA\Google2FA;
use Storage, Hash, Exception, Session, URL, File;

class ClientProfileController extends Controller
{
    /** How long a staged email-change confirmation link stays valid. */
    const EMAIL_CONFIRMATION_TTL_HOURS = 24;

    protected $whatsAppService;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(WhatsAppService $whatsAppService, NotificationSettingsService $notificationSettingsService)
    {
        // confirmEmailUpdate is reached from a mailed link, which may be opened on a
        // device with no session; it is protected by a signed URL instead.
        $this->middleware('auth')->except('confirmEmailUpdate');
        $this->whatsAppService = $whatsAppService;
        $this->notificationSettingsService = $notificationSettingsService;
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\User  $Type
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        try {
            $client = Auth::user();
            $countries = Country::all();
            $expertises = Expertise::where('parent_id', null)->get();
            $industries = StaticCategory::get();
            $Currencies = Currency::where('enabled', 1)->get();
            $json = File::get(base_path('database/dbFiles/countries.json'));
            $_countries = json_decode($json, true);
            $_countries = collect($_countries)->map(function ($country) {
                $country['flag'] = URL::to('app-assets/images/flags/' . strtolower($country['iso2']) . '.svg');
                return $country;
            });
            // $countries = $countries->map(function ($country) use ($_countries) {
            //     $getCountry = $_countries->where('iso3', $country->iso3)->first();
            //     $flag = null;
            //     $emoji = null;
            //     if (!empty($getCountry)) {
            //         $flag = $getCountry['flag'];
            //         $emoji = $getCountry['emoji'];
            //     }
            //     $country->flag = $flag;
            //     $country->emoji = $emoji;
            //     return $country;
            // });

            $countries = Country::all();
            
            // Populate states and cities for Service Provider if data exists
            if ($client->hasRole('serviceProvider') && $client->service_provider) {
                $states = $client->service_provider->country_id 
                    ? State::where('country_id', $client->service_provider->country_id)->get() 
                    : collect();
                $cities = $client->service_provider->state_id 
                    ? City::where('state_id', $client->service_provider->state_id)->get() 
                    : collect();
            } else {
                $states = collect();
                $cities = collect();
            }


            $google2fa_url = "";
            $secret_key = "";

            if ($client->exists()) {
                $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());
                $google2fa_url = $google2fa->getQRCodeInline(
                    env('APP_NAME'),
                    $client->email,
                    $client->google2fa_secret
                );
                $secret_key = $client->google2fa_secret;
            }

            $data = array(
                'user' => $client,
                'secret' => $secret_key,
                'google2fa_url' => $google2fa_url
            );

            // In your ProfileController or wherever you return the edit view

            $countriesIso3Map = $countries->pluck('iso3', 'id')
                ->map(fn($iso3) => strtolower($iso3))
                ->toArray();

            // Fetch activities
            $activities = Activity::where('user_id', $client->id)->orderBy('activity_time', 'desc')->get();
            return view('admin/clients/profile/editProfile', compact('client', 'Currencies', 'countries', 'states', 'cities', 'expertises', 'data','activities', 'industries', 'countriesIso3Map'));
        } catch (Exception $e) {
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }

    public function showClientProfile( $id )
    {
        return $this->show( $id );
    }

    public function show( $id = null )
    {
        $client = is_null( $id ) ? Auth::user() : User::find($id);

        if (!$client) {
            abort(404);
        }

        $mentors = CyclesMentors::where('user_id', $client->id ?? 0)->get();

        $globalRoles = ['Admin', 'Organization Manager', 'subadmin'];

        if ($client->hasAnyRole($globalRoles)) {
            $mentorshipTasks = MentorshipTask::query()->orderByDesc('created_at')->get();
        } elseif ($client->hasRole('Mentor')) {
             $mentorshipTasks = MentorshipTask::where('mentor_id', $client->id)->orderByDesc('created_at')->get();
        } elseif ($client->hasRole('Registered')) {
            $companyId = optional($client->company)->id;
            $mentorshipTasks = $companyId ? MentorshipTask::where('company_id', $companyId)->where('user_id', $client->id)->orderByDesc('created_at')->get() : collect();
        } else {
            $mentorshipTasks = collect();
        }
        $mentrequests = MentorshipRequest::where('mentor_id', $client->id);
        $mentrequests_count = $mentrequests->count();
        $mentrequests_period = $mentrequests->sum('period') / 60;
        $average_rating = MentorshipRequest::where('mentor_id', $client->id)
            ->where('approved', 'Yes')
            ->whereNotNull('rating') // تأكد أن عمود التقييم يحتوي على قيم
            ->avg('rating');

        // $almuni = Almuni::where('user_id', $client->id)->get();

        if ($client->hasRole('serviceProvider')) {
            $provider = $client->service_provider;
            if (!$provider) {
                $provider = new serviceProviders;
				$provider->company_name = $client->name;
				$provider->contact_name = $client->name;
				$provider->email = $client->email;
				$provider->phone = $client->phone;
				$provider->status = true;
				$provider->user_id = $client->id;
				$provider->save();
            }
            if ($provider) {
                return redirect()->route('service_provider.profile', $provider->id);
            }
        }
        
        return view('admin/clients/profile', compact('client', 'mentors', 'mentorshipTasks', 'mentrequests_count', 'mentrequests_period', 'average_rating'));
    }

    public function Updatebio(Request $request)
    {
       
        $user = Auth::user();
        // Mentor Bio section
        if ($request->mentor_section == 1) {
            
            $data = [
                'bio_ar' => $request->bio_ar,
                'bio_en' => $request->bio_en,
                'linkedin' => $request->linkedin,
                'entrepreneurial_experience' => $request->entrepreneurial_experience,
                'profile_privacy' => $request->profile_privacy,
                'industries' => $request->industries,
                'mentorship_approach' => json_encode($request->mentorship_approach),
            ];
            if ($request->entrepreneurial_experience == 1) {
                $data['entrepreneurial_experience_brief'] = $request->entrepreneurial_experience_brief ?? 0;
            } else {
                $data['entrepreneurial_experience_brief'] = 0;
            }
            $user->bio()->updateOrCreate(['user_id' => $user->id], $data);
            $user->mentorExpertises()->sync($request->expertises);
        }
        return redirect()->route('user.profile_view')->with(['msg' => 'StoredSuccessfully', 'class' => 'success']);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Type  $Type
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateClientProfileRequest $request)
    {
        
        try {
            $first_name_ar = substr($request->first_name_en, 0, 250);
            $last_name_ar = substr($request->last_name_en, 0, 250);
            $user = Auth::user();

            // // Mentor Bio section
            // if ($request->mentor_section){
            //     $data =[
            //         'bio_ar'=>$request->bio_ar,
            //         'bio_en'=>$request->bio_en,
            //         'linkedIn'=>$request->linkedin,
            //         'entrepreneurial_experience'=>$request->entrepreneurial_experience,
            //         'profile_privacy'=>$request->profile_privacy,
            //         'mentorship_approach'=>json_encode($request->mentorship_approach),
            //     ];
            //     if ($request->entrepreneurial_experience){
            //         $data['entrepreneurial_experience_brief']=$request->entrepreneurial_experience_brief;
            //     }
            //     $user->bio()->updateOrCreate(['user_id'=>$user->id],$data);
            //     $user->mentorExpertises()->sync($request->expertises);
            // }

            $updateArray = [];
            $excludeArray = ['img_url', 'password', 'email', 'first_name_ar', 'last_name_ar'];
            if( $request->hasFile('img_url') )
            {
                $img_url = $request->file('img_url');
                $img_url = $img_url->store('users', ['disk' => 'public_uploads']);
                Image::make(Storage::disk('public_uploads')->path($img_url))->resize(null, 200, function ($con) { $con->aspectRatio(); })->save();
            }
            else
            {
                $img_url = $user->img_url;
                if( $request->has('should_remove_image') && $request->should_remove_image == 1 ) { $img_url = null; }
            }
            $updateArray = $request->except($excludeArray) + ['img_url' => $img_url];

            $user->update($updateArray);
            $user->first_name_ar = $first_name_ar;
            $user->last_name_ar = $last_name_ar;

            // The email column is never written directly (it is in $excludeArray above).
            // A change is staged on new_email and only applied once the owner of the new
            // address confirms it via the signed link we mail them.
            $emailChangeRequested = false;
            $newEmail = strtolower(trim((string) $request->email));

            if ($newEmail !== strtolower((string) $user->email)) {
                $user->new_email = $newEmail;
                $user->new_email_requested_at = now();
                $emailChangeRequested = true;
            }

            $user->save();

            if ($emailChangeRequested) {
                $this->sendEmailUpdateConfirmation($user);

                return redirect()->route('user.profile_view')->with([
                    'msg'   => 'UpdateEmailSent',
                    'class' => 'success',
                    'pending_email' => $user->new_email,
                ]);
            }

            return redirect()->route('user.profile_view')->with(['msg' => 'ProfileUpdatedSuccessfully', 'class' => 'success']);
        } catch (Exception $e) {
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }

    /**
     * Mail the signed confirmation link for a staged email change.
     */
    private function sendEmailUpdateConfirmation($user)
    {
        $verificationUrl = URL::temporarySignedRoute(
            'user.profile_email_confirm',
            now()->addHours(self::EMAIL_CONFIRMATION_TTL_HOURS),
            ['user' => $user->id, 'token' => sha1($user->new_email)]
        );

        // Claim this exact request (new_email_requested_at is its version) so a
        // retry/duplicate call for it can't send twice, while a fresh request or
        // an explicit resend -- which bumps new_email_requested_at -- still sends.
        $version = $user->new_email_requested_at;
        $claimed = User::where('id', $user->id)
            ->where(function ($q) use ($version) {
                $q->whereNull('email_change_notified_at')
                  ->orWhere('email_change_notified_at', '!=', $version);
            })
            ->update(['email_change_notified_at' => $version]);

        if (!$claimed) {
            return;
        }

        Notification::route('mail', $user->new_email)
            ->notify(new ConfirmEmailUpdateNotification($verificationUrl, $user));
    }

    /**
     * Apply a staged email change.
     *
     * Public + signed route: the link must work from whatever device the founder reads
     * their new mailbox on, so it cannot sit behind auth/verified/2fa. The signature
     * carries the expiry; the token binds the link to the specific staged address so a
     * link becomes void as soon as the pending change is altered or cancelled.
     */
    public function confirmEmailUpdate($userId, $token)
    {
        $user = User::find($userId);

        if (!$user || !$user->new_email || !hash_equals(sha1($user->new_email), (string) $token)) {
            return redirect()->route('login')->with(['msg' => 'WrongLink', 'class' => 'danger']);
        }

        if ($user->new_email_requested_at
            && $user->new_email_requested_at->addHours(self::EMAIL_CONFIRMATION_TTL_HOURS)->isPast()) {
            $this->clearPendingEmail($user);

            return redirect()->route('login')->with(['msg' => 'EmailConfirmationExpired', 'class' => 'danger']);
        }

        // The address may have been claimed by another account between staging and
        // confirming, so re-check uniqueness before committing.
        $taken = User::where('id', '!=', $user->id)
            ->where(function ($q) use ($user) {
                $q->where('email', $user->new_email)->orWhere('new_email', $user->new_email);
            })
            ->exists();

        if ($taken) {
            $this->clearPendingEmail($user);

            return redirect()->route('login')->with(['msg' => 'EmailAlreadyTaken', 'class' => 'danger']);
        }

        $user->email = $user->new_email;
        $user->new_email = null;
        $user->new_email_requested_at = null;
        // The address has just proven it is reachable and owned by this user.
        $user->email_verified_at = now();
        $user->save();

        $route = auth()->check() && auth()->id() === $user->id ? 'user.profile_view' : 'login';

        return redirect()->route($route)->with(['msg' => 'EmailUpdatedSuccessfully', 'class' => 'success']);
    }

    /**
     * Re-send the confirmation mail for the currently staged email change.
     */
    public function resendEmailConfirmation()
    {
        $user = Auth::user();

        if (!$user->new_email) {
            return redirect()->route('user.profile_view')->with(['msg' => 'NoPendingEmailChange', 'class' => 'danger']);
        }

        $user->new_email_requested_at = now();
        $user->save();

        $this->sendEmailUpdateConfirmation($user);

        return redirect()->route('user.profile_view')->with([
            'msg'   => 'UpdateEmailSent',
            'class' => 'success',
            'pending_email' => $user->new_email,
        ]);
    }

    /**
     * Discard a staged email change.
     */
    public function cancelEmailUpdate()
    {
        $user = Auth::user();

        if (!$user->new_email) {
            return redirect()->route('user.profile_view')->with(['msg' => 'NoPendingEmailChange', 'class' => 'danger']);
        }

        $this->clearPendingEmail($user);

        return redirect()->route('user.profile_view')->with(['msg' => 'EmailUpdateCancelled', 'class' => 'success']);
    }

    private function clearPendingEmail($user)
    {
        $user->new_email = null;
        $user->new_email_requested_at = null;
        $user->save();
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Type  $Type
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(UpdateClientProfilePasswordRequest $request)
    {
        try {
            $user = Auth::user();
            $password = Hash::make($request->password);
            $result = $user->where('id', $user->id)
                ->update([
                    'password' => $password,
                    'must_change_password' => false,
                    'magic_login_token' => null,
                ]);
            if ($result)
                return redirect()->route('user.profile_view')->with(['msg' => 'StoredSuccessfully', 'class' => 'success']);
            else
                return back()->withInput()->with(['msg' => 'FailedStore', 'class' => 'danger']);
        } catch (Exception $e) {
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);
        }
    }

    public function showMentorProfile($id)
    {
        $client = User::find($id);
        if (!empty($client) && $client->hasRole('Mentor') ) {
            // $exp = Expertise::where('parent_id',null)->get();
            // $expertises = Expertise::where('parent_id',null)->get();
            $mentrequests = MentorshipRequest::where('mentor_id', $id);

            $mentrequests_count = $mentrequests->count();
            $mentrequests_period = $mentrequests->sum('period') / 60;


            $average_rating = MentorshipRequest::where('mentor_id', $id)
                ->where('approved', 'Yes')
                ->whereNotNull('rating') // تأكد أن عمود التقييم يحتوي على قيم
                ->avg('rating');



            $mentors = User::find($id);
            if ($mentors) {
                $expertises = $mentors->mentorExpertises; // جلب جميع الخبرات المرتبطة بهذا الموجه
            } else {
                // الموجه غير موجود
                $expertises = [];
            }
            $mentors = CyclesMentors::where('user_id', $id ?? 0)->get();



            $order = CyclesMentors::where('cycle_id', Auth::user()->company->cycle ?? 0)->where('user_id', $id ?? 0)->first();


            $mentorshipTasks = MentorshipTask::where('mentor_id', $id)->where('status', 'pending')->get();


            return view('admin/clients/UserProfile', compact(
                'client',
                'expertises',
                'mentors',
                'order',
                'mentorshipTasks',
                'mentrequests_count',
                'mentrequests_period',
                'average_rating'
            ));

        } else {
            return redirect()->route('clients.profile_view', $id);
        }
    }

    public function showMentors(Request $request)
    {
        $settings = Settings::where('id', 1)->first();
        $search = $request->input('search');
        $rateSearch = $request->input('rateSearch');
        $expertisesSearch = $request->input('expertises', []);
        $linkedin = $request->input('linkedin');
        $programFilter = $request->input('programFilter');
        // Default to the user's enrolled cycle when no filter is provided
        if (empty($programFilter)) {
            $defaultUser = Auth::user();
            if (!$defaultUser->hasRole('Admin')) {
                if ($defaultUser->hasRole('Registered') && $defaultUser->company && !empty($defaultUser->company->cycle)) {
                    // Registered user: use their company's specific cycle
                    $programFilter = $defaultUser->company->cycle;
                } else {
                    // OrgManager/subadmin/Mentor: use latest cycle from their org
                    $defaultOrgIds = $defaultUser->organizations_ids;
                    if (!empty($defaultOrgIds)) {
                        $programFilter = Cycle::whereIn('organization_id', $defaultOrgIds)
                            ->orderBy('start', 'DESC')
                            ->value('id');
                    }
                }
            }
        }
        $scopeFilter = $request->input('scopeFilter');
        $enrolledInProgramFilter = $request->input('enrolledInProgramFilter');
        if ($settings->mentor_list == 1) {
            if (Auth::user()->hasAnyRole('Mentor|Registered|Admin|OrganizationManager|subadmin')) {
                $l = app()->getLocale();
                $firstNameColumn = $l == 'ar' ? 'first_name_en' : 'first_name_en';
                $lastNameColumn = $l == 'ar' ? 'first_name_en' : 'last_name_en';
                $mentors = User::role('mentor')
                    ->with(['bio', 'mentorExpertises', 'mentorCycles', 'schedule' => function ($q) {
                        $q->where(function ($query) {
                            $query->whereNull('date')
                                ->orWhere('date', '>', date('Y-m-d'))
                                ->orWhere(function ($q2) {
                                    $q2->where('date', date('Y-m-d'))
                                       ->where('tto', '>', date('H:i:s'));
                                });
                        });
                    }])
                    ->withAvg(['mentorshipRequests as avg_rating' => function ($q) {
                        $q->where('approved', 'Yes');
                    }], 'rating')
                    ->whereRaw("CONCAT($firstNameColumn, ' ', $lastNameColumn) LIKE ?", ["%{$search}%"]);
                if ($rateSearch > 0) {
                    $mentors = $mentors->whereHas('mentorshipRequests', function ($q) use ($rateSearch) {
                        $q->where(function ($q2) {
                            $q2->where('approved', 'Yes')
                                ->orWhereNull('approved');
                        })->havingRaw('AVG(rating) >= ?', [$rateSearch]);
                    });
                }
                if (!empty($expertisesSearch) && is_array($expertisesSearch)) {
                    $mentors = $mentors->whereHas('mentorExpertises', function ($query) use ($expertisesSearch) {
                        $query->whereIn('expertise_id', $expertisesSearch);
                    });
                }
                // LinkedIn filter - All/Yes/No options
                if ($linkedin !== null && $linkedin !== '') {
                    if ($linkedin == '1') {
                        // Yes - has LinkedIn profile
                        $mentors = $mentors->whereHas('bio', function ($query) {
                            $query->whereNotNull('linkedin')->where('linkedin', '!=', '');
                        });
                    } elseif ($linkedin == '0') {
                        // No - doesn't have LinkedIn profile
                        $mentors = $mentors->whereDoesntHave('bio', function ($query) {
                            $query->whereNotNull('linkedin')->where('linkedin', '!=', '');
                        });
                    }
                    // If linkedin is empty string (''), show all (no filter applied)
                }
                /** @var \App\User $user */
                $user = Auth::user();
                $orgIds = $user->organizations_ids;

                // Program filter: apply only when explicitly sent by user, or for Registered users (auto-set to their company cycle)
                $explicitProgramFilter = $request->input('programFilter');
                $filterCycleId = $explicitProgramFilter
                    ?: ($user->hasRole('Registered') ? $programFilter : null);
                if (!empty($filterCycleId)) {
                    $mentors = $mentors->whereHas('mentorCycles', function ($query) use ($filterCycleId) {
                        $query->where('cycle_id', $filterCycleId);
                    });
                }
                // Enrolled in program filter - All/Yes/No options
                if ($enrolledInProgramFilter !== null && $enrolledInProgramFilter !== '') {
                    if ($enrolledInProgramFilter == '1') {
                        // Yes - enrolled in at least one program
                        $mentors = $mentors->has('mentorCycles');
                    } elseif ($enrolledInProgramFilter == '0') {
                        // No - not enrolled in any program
                        $mentors = $mentors->doesntHave('mentorCycles');
                    }
                    // If enrolledInProgramFilter is empty string (''), show all (no filter applied)
                }

                if (!empty($scopeFilter) && $scopeFilter !== 'all') {
                    if ($scopeFilter === 'public') {
                        // Public: no invitations OR has an accepted Public-scope invitation
                        $mentors = $mentors->where(function ($q) {
                            $q->whereDoesntHave('inviteMentors')
                              ->orWhereHas('inviteMentors', function ($q2) {
                                  $q2->where('scope', 'Public')->where('status', 'accepted');
                              });
                        });
                    } else {
                        // Specific org: must have an accepted invitation with this org in organization_ids
                        $mentors = $mentors->whereHas('inviteMentors', function ($query) use ($scopeFilter) {
                            $query->where('status', 'accepted')
                                  ->whereJsonContains('organization_ids', (string) $scopeFilter);
                        });
                    }
                } else {
                    // scopeFilter = all (or empty)
                    // Non-admin with org: show org-cycle mentors + public mentors
                    if (!$user->hasRole('Admin') && !empty($orgIds)) {
                        $mentors = $mentors->where(function ($q) use ($orgIds) {
                            // Public: no invitations at all
                            $q->whereDoesntHave('inviteMentors')
                            // Public: has an accepted Public-scope invitation
                            ->orWhereHas('inviteMentors', function ($q2) {
                                $q2->where('scope', 'Public')->where('status', 'accepted');
                            })
                            // Belongs to this org: has an accepted Organization-scope invitation for THIS org
                            ->orWhereHas('inviteMentors', function ($q2) use ($orgIds) {
                                $q2->where('scope', 'Organization')->where('status', 'accepted')
                                   ->where(function ($q3) use ($orgIds) {
                                       foreach ($orgIds as $orgId) {
                                           $q3->orWhereJsonContains('organization_ids', (string) $orgId);
                                       }
                                   });
                            });
                        });
                    }
                    // Admin: no restriction — show all mentors
                }

                $mentors = $mentors->with('mentorExpertises');
                $mentors = $mentors->get();

              
              
                if (!$user->hasRole('Admin') && !empty($orgIds)) {
                    $cycles = Cycle::whereIn('organization_id', $orgIds)->get();
                    // Dead post-collection filter code commented out (filters run at DB level above)
                    // if (!empty($scopeFilter) && $scopeFilter !== 'all') {
                    //     if ($scopeFilter === 'public') {
                    //         $mentors->where(function ($q) {
                    //             $q->whereDoesntHave('inviteMentors')
                    //             ->orWhere(function ($q2) {
                    //                 $q2->whereHas('inviteMentors', function ($q3) {
                    //                     $q3->where('scope', 'Public')
                    //                         ->where('status', 'accepted');
                    //                 });
                    //             });
                    //         });
                    //     } else {
                    //         $mentors->filter(function ($mentor) use ($request) {
                    //             return $mentor->inviteMentors->filter(function ($invite) use ($request) {
                    //                 if ($invite->status !== 'accepted') {
                    //                     return false;
                    //                 }
                    //                 if ($request->scopeFilter) {
                    //                     $orgs = json_decode($invite->organization_ids, true) ?? [];
                    //                     return in_array((string) $request->scopeFilter, $orgs);
                    //                 }
                    //                 return true;
                    //             })->isNotEmpty();
                    //         });
                    //     }
                    // }
                } else {
                    $cycles = Cycle::all();
                }
                
			    $offline_allowed = $settings->allow_offline_sessions == 1 ? true : false;
                $getOrganization = InviteMentor::where('status', 'accepted')
                    ->where('scope', 'Organization')
                    ->pluck('mentor_id')
                    ->toArray();

                $mentors = $mentors->map(function ($mentor) use ($getOrganization) {
                    $mentor->is_public = !in_array($mentor->id, $getOrganization);
                    return $mentor;
                });
                
                    
                // $mentors = User::role('mentor')->get();
                // GV-3554: founders only see mentor cards once their program has
                // reached the mentorship stage.
                $mentorshipActive = MentorshipAvailabilityService::isActiveForCompany($user->company);

                return view('mentors.index', compact('mentors', 'cycles', 'offline_allowed', 'programFilter', 'mentorshipActive'));
            }
        } else {

            if (Auth::user()->hasAnyRole('Admin|OrganizationManager|subadmin')) {
                $l = app()->getLocale();
                $firstNameColumn = $l == 'ar' ? 'first_name_en' : 'first_name_en';
                $lastNameColumn = $l == 'ar' ? 'first_name_en' : 'last_name_en';
                $mentors = User::role('mentor')
                    ->with(['bio', 'mentorExpertises', 'mentorCycles', 'schedule' => function ($q) {
                        $q->where(function ($query) {
                            $query->whereNull('date')
                                ->orWhere('date', '>', date('Y-m-d'))
                                ->orWhere(function ($q2) {
                                    $q2->where('date', date('Y-m-d'))
                                       ->where('tto', '>', date('H:i:s'));
                                });
                        });
                    }])
                    ->withAvg(['mentorshipRequests as avg_rating' => function ($q) {
                        $q->where('approved', 'Yes');
                    }], 'rating')
                    ->whereRaw("CONCAT($firstNameColumn, ' ', $lastNameColumn) LIKE ?", ["%{$search}%"]);
                if ($rateSearch > 0) {
                    $mentors = $mentors->whereHas('mentorshipRequests', function ($q) use ($rateSearch) {
                        $q->where(function ($q2) {
                            $q2->where('approved', 'Yes')
                                ->orWhereNull('approved');
                        })->havingRaw('AVG(rating) >= ?', [$rateSearch]);
                    });
                }
                if (!empty($expertisesSearch) && is_array($expertisesSearch)) {
                    $mentors = $mentors->whereHas('mentorExpertises', function ($query) use ($expertisesSearch) {
                        $query->whereIn('expertise_id', $expertisesSearch);
                    });
                }
                // LinkedIn filter - All/Yes/No options
                if ($linkedin !== null && $linkedin !== '') {
                    if ($linkedin == '1') {
                        // Yes - has LinkedIn profile
                        $mentors = $mentors->whereHas('bio', function ($query) {
                            $query->whereNotNull('linkedin')->where('linkedin', '!=', '');
                        });
                    } elseif ($linkedin == '0') {
                        // No - doesn't have LinkedIn profile
                        $mentors = $mentors->whereDoesntHave('bio', function ($query) {
                            $query->whereNotNull('linkedin')->where('linkedin', '!=', '');
                        });
                    }
                    // If linkedin is empty string (''), show all (no filter applied)
                }
                // Program filter - filter by specific program/cycle
                // Only apply programFilter if explicitly sent by the user (not the auto-set value)
                $explicitProgramFilter = $request->input('programFilter');
                if (!empty($explicitProgramFilter)) {
                    $mentors = $mentors->whereHas('mentorCycles', function ($query) use ($explicitProgramFilter) {
                        $query->where('cycle_id', $explicitProgramFilter);
                    });
                }
                // Enrolled in program filter - All/Yes/No options
                if ($enrolledInProgramFilter !== null && $enrolledInProgramFilter !== '') {
                    if ($enrolledInProgramFilter == '1') {
                        // Yes - enrolled in at least one program
                        $mentors = $mentors->has('mentorCycles');
                    } elseif ($enrolledInProgramFilter == '0') {
                        // No - not enrolled in any program
                        $mentors = $mentors->doesntHave('mentorCycles');
                    }
                    // If enrolledInProgramFilter is empty string (''), show all (no filter applied)
                }
                /** @var \App\User $user */
                $user = Auth::user();
                $orgIds = $user->organizations_ids;

                if (!empty($scopeFilter) && $scopeFilter !== 'all') {
                    if ($scopeFilter === 'public') {
                        $mentors = $mentors->where(function ($q) {
                            $q->whereDoesntHave('inviteMentors')
                              ->orWhereHas('inviteMentors', function ($q2) {
                                  $q2->where('scope', 'Public')->where('status', 'accepted');
                              });
                        });
                    } else {
                        $mentors = $mentors->whereHas('inviteMentors', function ($query) use ($scopeFilter) {
                            $query->where('status', 'accepted')
                                  ->whereJsonContains('organization_ids', (string) $scopeFilter);
                        });
                    }
                } else {
                    // scopeFilter = all: show public mentors + mentors belonging to this org
                    if (!$user->hasRole('Admin') && !empty($orgIds)) {
                        $mentors = $mentors->where(function ($q) use ($orgIds) {
                            // Public: no invitations at all, or has accepted public-scope invitation
                            $q->whereDoesntHave('inviteMentors')
                              ->orWhereHas('inviteMentors', function ($q2) {
                                  $q2->where('scope', 'Public')->where('status', 'accepted');
                              });
                            // OR belongs to this org
                            foreach ($orgIds as $orgId) {
                                $q->orWhereHas('inviteMentors', function ($q2) use ($orgId) {
                                    $q2->where('scope', 'Organization')
                                       ->where('status', 'accepted')
                                       ->whereJsonContains('organization_ids', (string) $orgId);
                                });
                            }
                        });
                    }
                }
                $mentors = $mentors->with('mentorExpertises');
                $mentors = $mentors->get();

                if (!$user->hasRole('Admin') && !empty($orgIds)) {
                    $cycles = Cycle::whereIn('organization_id', $orgIds)->get();
                } else {
                    $cycles = Cycle::all();
                }

                $settings = Settings::where('id', 1)->first();
			    $offline_allowed = $settings->allow_offline_sessions == 1 ? true : false;
                $getOrganization = InviteMentor::where('status', 'accepted')
                    ->where('scope', 'Organization')
                    ->pluck('mentor_id')
                    ->toArray();

                $mentors = $mentors->map(function ($mentor) use ($getOrganization) {
                    $mentor->is_public = !in_array($mentor->id, $getOrganization);
                    return $mentor;
                });
                // $mentors = User::role('mentor')->get();
                $mentorshipActive = MentorshipAvailabilityService::isActiveForCompany(Auth::user()->company);

                return view('mentors.index', compact('mentors', 'cycles', 'offline_allowed', 'mentorshipActive'));
            } else {
                return redirect()->route('core.dashboard_view');
            }

        }

    }


    public function savePhoneSession(Request $request)
    {

        $request->session()->forget('phone');
        $request->session()->put('phone', $request->phone);
        $request->session()->put('countryCode', $request->countryCode);

        return response()->json(['message' => 'تم حفظ رقم الهاتف بنجاح.']);

    }
    public function sendCode(Request $request)
    {
        $userId = Auth::user()->id;

        DB::table('otps')
            ->where('created_at', '<', Carbon::now()->subHours(6))
            ->where('bad', '0')
            ->delete();

        // جلب الـ OTPs السابقة التي تم إرسالها والتي هي "غير صالحة" (bad == 0)
        $otpget = Otp::where('user_id', $userId)
            ->where('bad', '0')
            ->where('created_at', '>=', DB::raw('created_at - INTERVAL 6 HOUR')) // تحقق من المحاولات في آخر 6 ساعات
            ->get();




        // إذا كان عدد المحاولات أكثر من 2 خلال 6 ساعات، يجب الانتظار 6 ساعات
        if ($otpget->count() >= 5) {
            return response()->json(['error' => __('admin.OTP_limit_exceeded_wait_6_hours')]);
        }

        $request->session()->forget('otp');
        $request->session()->forget('phone');
        $request->session()->forget('countryCode');
        $request->session()->put('phone', $request->phone);
        $request->session()->put('countryCode', $request->countryCode);

        $phoneNumber = $request->countryCode . $request->phone;

        $otp = $this->generateOtp();

        $otpsave = new Otp();
        $otpsave->user_id = $userId;
        $otpsave->phone = $phoneNumber;
        $otpsave->otp_code = $otp;
        $otpsave->expires_at = Carbon::now()->addMinutes(10);
        $otpsave->save();

        $result = $this->whatsAppService->sendOTP($phoneNumber, $otp);
        dd($result);
        if ($result) {
            $otpsave->otp_sent = true;
            $otpsave->save();
            $request->session()->put('otp', $otp);
            return response()->json(['success' => __('admin.OTP_sent_successfully')]);
        } else {
            return response()->json(['error' => __('admin.Failed_to_send_OTP')]);
        }




    }

    public function verifyCode(Request $request)
    {
        $codeFromUser = $request->code;
        $otpFromSession = session('otp');
        $phone = session('phone');
        $countryCode = session('countryCode');
        $userId = Auth::user()->id;

        if ($codeFromUser == $otpFromSession) {
            // إذا كان الرمز صحيحًا
            $otpget = Otp::where('user_id', $userId)
                ->where('bad', '0')
                ->where('expires_at', '>=', now()) // تأكد أن OTP لم تنته صلاحيته
                ->orderBy('created_at', 'desc')
                ->first();

            if ($otpget) {
                // وضع علامة "سيء" على الـ OTP
                $otpget->bad = 1;
                $otpget->save();

                $user = User::find($userId);
                $user->country_code_w_a = $countryCode;
                $user->whatsapp = $phone;
                $user->whatsapp_verified = 1;
                $user->save();

                return response()->json(['success' => __('admin.OTP_verified_successfully')]);
            } else {
                return response()->json(['error' => __('admin.No_valid_OTP_found_or_OTP_expired')]);
            }
        } else {
            // إذا كان الرمز غير صحيح
            $otpget2 = Otp::where('user_id', $userId)
                ->where('bad', '0')
                ->where('expires_at', '>=', now()) // تأكد أن OTP لم تنته صلاحيته
                ->orderBy('created_at', 'desc')
                ->first();

            if ($otpget2) {
                // زيادة المحاولات الخاطئة
                $otpget2->wrong_attempts += 1;

                if ($otpget2->wrong_attempts >= 3) {
                    // قفل المحاولات لمدة 6 ساعات
                    $otpget2->created_at = now()->addHours(6);
                    $otpget2->save();

                    return response()->json(['error' => __('admin.max_attempts_reached')]);
                } else {
                    $otpget2->save();

                    return response()->json([
                        'error' => __('admin.invalid_verification_code'),
                        'remaining_attempts' => 3 - $otpget2->wrong_attempts,
                    ]);
                }
            } else {
                return response()->json(['error' => __('admin.No_valid_OTP_found_or_OTP_expired')]);
            }
        }
    }

    private function generateOtp()
    {
        return mt_rand(100000, 999999);
    }

    /**
     * Show 2FA Setting form
     */
    public function show2faForm(Request $request)
    {
        $user = Auth::user();
        $google2fa_url = "";
        $secret_key = "";

        if ($user->exists()) {
            $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());
            $google2fa_url = $google2fa->getQRCodeInline(
                env('APP_NAME'),
                $user->email,
                $user->google2fa_secret
            );
            $secret_key = $user->google2fa_secret;
        }

        $data = array(
            'user' => $user,
            'secret' => $secret_key,
            'google2fa_url' => $google2fa_url
        );

        return view('auth.2fa_settings')->with('data', $data);
    }

    /**
     * Generate 2FA secret key
     */
    public function generate2faSecret(Request $request)
    {

        $user = Auth::user();
        // Initialise the 2FA class
        $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());

        // Add the secret key to the registration data

        $user->two_factor_enabled = 0;
        $user->google2fa_secret = $google2fa->generateSecretKey();
        $user->save();

        return redirect('/dashboard/myProfile')->with('success', "Secret key is generated.");
    }

    /**
     * Enable 2FA
     */
    public function enable2fa(Request $request)
    {

        $user = Auth::user();
        $google2fa = (new \PragmaRX\Google2FAQRCode\Google2FA());

        $secret = $request->input('secret');
        $valid = $google2fa->verifyKey($user->google2fa_secret, $secret);

        if ($valid) {
            $user->two_factor_enabled = 1;
            $user->save();
            return redirect('/dashboard/myProfile')->with('success', "2FA is enabled successfully.");
        } else {
            return redirect('/dashboard/myProfile')->with('error', "Invalid verification Code, Please try again.");
        }
    }

    /**
     * Disable 2FA
     */
    public function disable2fa(Request $request)
    {
        if (!(Hash::check($request->get('current-password'), Auth::user()->password))) {
            // The passwords matches
            return redirect()->back()->with("error", "Your password does not matches with your account password. Please try again.");
        }

        $validatedData = $request->validate([
            'current-password' => 'required',
        ]);
        $user = Auth::user();
        $user->two_factor_enabled = 0;
        $user->google2fa_secret = null;
        $user->save();
        return redirect('/dashboard/myProfile')->with('success', "2FA is now disabled.");
    }

    /**
     * Update notification settings
     */
    public function updateNotificationSettings(Request $request)
    {
        try {
            $request->validate([
                'accept_email' => 'sometimes|boolean',
                'accept_whatsapp' => 'sometimes|boolean',
                'accept_pusher' => 'sometimes|boolean',
            ]);

            $settings = [
                'accept_email' => filter_var($request->input('accept_email', auth()->user()->accept_email), FILTER_VALIDATE_BOOLEAN),
                'accept_whatsapp' => filter_var($request->input('accept_whatsapp', auth()->user()->accept_whatsapp), FILTER_VALIDATE_BOOLEAN),
                'accept_pusher' => filter_var($request->input('accept_pusher', auth()->user()->accept_pusher), FILTER_VALIDATE_BOOLEAN),
            ];

            $updated = $this->notificationSettingsService->updateNotificationSettings($settings);

            if ($updated) {
                return redirect()->back()->with('success', 'Notification settings updated successfully');
            }

            return redirect()->back()->with('error', 'Failed to update notification settings');

        } catch (\Exception $e) {
            Log::channel('system_exceptions')->info('Exception updating notification settings:', [$e]);
            return redirect()->back()->with('error', 'An error occurred while updating notification settings');
        }
    }
}