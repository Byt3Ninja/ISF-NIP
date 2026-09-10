<?php

namespace App\Http\Controllers;

use App\Almuni;
use App\Step;
use App\User;
use stdClass;
use App\Cycle;
use App\Sector;
use App\Company;
use App\Service;
use App\ServiceCategory;
use App\Calendar;
use App\Currency;
use App\Messages;
use App\Schedule;
use App\Settings;
use App\Messenger;
use App\Screening;
use App\TimeSheet;
use Carbon\Carbon;
use App\BuilderForm;
use App\Organization;
use App\SurveyScopes;
use App\ScreeningEvaluation;
use App\Milestone;

use App\Documentation;
use App\Finalscreening;
use App\MentorshipTask;
use App\SurveyResponses;
use App\SustainableIcon;
use App\TrainingSession;
use App\ServicesRequests;
use App\Services\MeetingWindowService;
use App\Services\MentorshipAvailabilityService;
use App\Events\WebMessage;
use App\MentorshipRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use App\Notifications\ServiceRequestNotification;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if (env('NEW_DASHBOARD', false) == true) {
            return $this->combinedDashboard();
        } else {
            $user = auth()->user();
            if ($user->hasAnyRole('Admin|Editor|Manager')) {

                return $this->AdminDashboard();
            } elseif ($user->hasRole('serviceProvider')) {

                return redirect()->route('service_provider_dashboard.view');
            } elseif ($user->hasAnyRole('Registered|Page Manager|Pages Editor')) {

                return view('admin.clients.dashboard');
            } elseif ($user->hasAnyRole('organizationManager|subadmin')) {

                return $this->organizationDashboard();
            } elseif ($user->hasAnyRole('Mentor')) {

                if (!Auth::user()->bio) {
                    return redirect('/dashboard/myProfile#bio');
                }
                return $this->mentorDashboard();
            } elseif ($user->hasAnyRole('Judger')) {

                return $this->judgerDashboard();
            } elseif ($user->hasAnyRole('Disabled')) {

                return view('admin.clients.disabled');
            }
        }
    }

    // ============> Functions of dashboards

    protected function combinedDashboard()
    {
        $user = auth()->user();

        // Service Provider gets its own dashboard
        if ($user->hasRole('serviceProvider')) {
            return app(ServiceProviderDashboardController::class)->index();
        }

        $l = App::getLocale();
        $permissions = [];
        $organizations_ids = $user->organizations_ids;
        $orgIds = $organizations_ids;
        $has_org = !empty($organizations_ids);
        if (auth()->check()) {
            $permissions = $user->myPermissions();
        }

        if (array_intersect(['dashboard.widgets_cards_managers_view', 'dashboard.widgets_cards_mentors_view', 'dashboard.widgets_cards_judgers_view', 'dashboard.widgets_tables_mentors_view'], $permissions)) {
            $users_q = "SELECT 
                    u.id,
                    u.img_url,
                    CONCAT(TRIM(COALESCE(u.first_name_en, '')), ' ', TRIM(COALESCE(u.last_name_en, ''))) AS name_en,
                    CONCAT(TRIM(COALESCE(u.first_name_ar, '')), ' ', TRIM(COALESCE(u.last_name_ar, ''))) AS name_ar,
                    u.title_en AS `title_en`,
                    u.title_ar AS `title_ar`,
                    u.gender,
                    u.email,
                    r.default_for AS `role`,
                    ub.bio_en AS bio_en,
                    ub.bio_ar AS bio_ar,
                    ub.specialization_en AS bio_specialization_en,
                    ub.specialization_ar AS bio_specialization_ar,
                    ub.linkedin AS linkedin,
                    cm.cycle_id AS cycle_id,
                    c.organization_id AS organization_id
                FROM
                    users u
                LEFT JOIN
                    model_has_roles mhr ON u.id = mhr.model_id AND model_type = 'App\\\User'
                LEFT JOIN
                    roles r ON mhr.role_id = r.id
                LEFT JOIN
                    users_bio ub ON ub.user_id = u.id
                LEFT JOIN
                    cycles_mentors cm ON cm.user_id = u.id
                LEFT JOIN
                    cycles c ON c.id = cm.cycle_id
                WHERE
                    u.deleted_at IS NULL
            ";
            $users = collect(DB::select($users_q));
            $users = $users->groupBy('id')->map(function ($user) use ($l) {
                $oneUser = $user->first();
                $roles = $user->map(function ($item) {
                    return strtolower($item->role);
                })->unique()->toArray();
                unset($oneUser->role);
                $oneUser->roles = $roles;
                $image = User::image_handler($oneUser->img_url);

                unset($oneUser->img_url);
                $oneUser->name = $l == 'en' ? $oneUser->name_en : $oneUser->name_ar;
                $oneUser->image_url = $image;
                $oneUser->bio = $oneUser->{'bio_' . $l};
                $oneUser->specialization = $oneUser->{'bio_specialization_' . $l};
                unset($oneUser->bio_en);
                unset($oneUser->bio_ar);
                unset($oneUser->bio_specialization_en);
                unset($oneUser->bio_specialization_ar);
                $oneUser->title = $oneUser->{'title_' . $l};
                unset($oneUser->title_ar);
                unset($oneUser->title_en);
                $oneUser->mentor_cycles = $user->map(function ($item) {
                    return $item->cycle_id;
                })->unique()->filter(function ($item) {
                    return $item != null;
                })->values()->toArray();
                return $oneUser;
            })->values();
        } else {
            $users = collect([]);
        }

        if (array_intersect(['dashboard.widgets_cards_contact_us_view'], $permissions)) {
            $messages_q = "SELECT 
                    m.id,
                    m.read
                FROM
                    messages m
                WHERE
                    m.deleted_at IS NULL
                    AND m.read != 1
            ";
            $messages = collect(DB::select($messages_q));
        } else {
            $messages = collect([]);
        }

        if (array_intersect(['dashboard.widgets.cards.new_messages.view'], $permissions)) {
            $messenger_q = "SELECT 
                    m.id,
                    m.read
                FROM
                    messenger m
                WHERE
                    m.deleted_at IS NULL
                    AND m.read IS NULL
                    AND m.receiver = " . $user->id . "
            ";
            $messenger = collect(DB::select($messenger_q));
        } else {
            $messenger = collect([]);
        }

        if (array_intersect(['dashboard.widgets_cards_service_requests_view'], $permissions)) {
            $services_requests_q = "SELECT 
                    sr.id,
                    sr.service_id,
                    sr.approved
                FROM
                    services_requests sr
                LEFT JOIN
                    services s ON sr.service_id = s.id
                WHERE
                    sr.deleted_at IS NULL
                    AND sr.approved IS NULL
            ";
            if($user->hasRole('Registered')){
                $services_requests_q .= " AND sr.user_id = " . $user->id;
            }
            if ($has_org && !empty($orgIds) && !$user->hasRole('admin')) {
                $services_requests_q .= " AND s.organization_id IN (" . implode(',', array_map('intval', $orgIds)) . ")";
            }
            $services_requests = collect(DB::select($services_requests_q));
        } else {
            $services_requests = collect([]);
        }

        if (array_intersect(['dashboard.widgets_cards_organizations_view'], $permissions)) {
            $organizations_q = "SELECT 
                    o.id
                FROM
                    organizations o
                WHERE
                    o.deleted_at IS NULL
            ";
            if ($has_org && !empty($orgIds) && !$user->hasRole('admin')) {
                $organizations_q .= " AND o.id IN (" . implode(',', array_map('intval', $orgIds)) . ")";
            }
            $organizations = collect(DB::select($organizations_q));
        } else {
            $organizations = collect([]);
        }

        if (array_intersect(['dashboard.widgets_cards_programs_view'], $permissions)) // remember to add more permissions here
        {
            $cycles_q = "SELECT 
                    c.id,
                    c.title,
                    c.organization_id,
                    cm.id AS company_id,
                    cm.name_en,
                    cm.name_ar
                FROM
                    cycles c
                LEFT JOIN
                    companies cm ON cm.cycle = c.id
                WHERE
                    c.deleted_at IS NULL
            ";
            if ($has_org && !empty($orgIds) && !$user->hasRole('admin')) {
                $cycles_q .= " AND c.organization_id IN (" . implode(',', array_map('intval', $orgIds)) . ")";
            }
            $cycles = collect(DB::select($cycles_q));
            $cycles = $cycles->groupBy('id')->map(function ($groupedCycle) use ($l) {
                $cycle = $groupedCycle->first();
                $output = new stdClass;
                $output->id = $cycle->id;
                $output->title = $cycle->title;
                $output->organization_id = $cycle->organization_id;
                $output->companies = $groupedCycle->map(function ($item) use ($l) {
                    $company = new stdClass;
                    $company->id = $item->company_id;
                    $company->name = $item->{'name_' . $l};
                    return $company;
                })->whereNotNull('id')->values();
                return $output;
            })->values();
        } else {
            $cycles = collect([]);
        }

        if (array_intersect(['dashboard.widgets_cards_startups_view'], $permissions)) {
            $companies_q = "SELECT
                    s.id,
                    c.organization_id
                FROM
                    companies s
                LEFT JOIN
                    cycles c ON c.id = s.cycle
                WHERE
                    s.deleted_at IS NULL
            ";
            if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
                // Scoping via the company's CURRENT cycle misses founders who applied
                // to this org's program but were later disapproved (which nulls
                // companies.cycle) or moved on — so key off form_data instead, which
                // is written at application time and never cleared afterward.
                $companies_q .= " AND EXISTS (
                        SELECT 1 FROM form_data fd
                        JOIN steps st ON st.id = fd.step_id
                        JOIN cycles cyc ON cyc.id = st.cycle_id
                        WHERE fd.company_id = s.id
                        AND cyc.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")
                    )";
            }
            $companies = collect(DB::select($companies_q));
            $registeredUserFilter = function ($q) {
                $q->whereHas('roles', function ($rq) {
                    $rq->where('default_for', 'registered');
                })->whereDoesntHave('roles', function ($rq) {
                    $rq->whereIn('default_for', ['admin', 'organizationmanager', 'subadmin']);
                });
            };
            if ($organizations_ids != null) {
                $incubators_company = Company::whereHas('cycle', function ($q) use ($organizations_ids) {
                    $q->whereIn('organization_id', $organizations_ids);
                })->whereHas('stepdata', function ($q) {
                    $q->where('stype', '!=', 1);
                })->whereHas('user', $registeredUserFilter)->get();
            } else {
                $incubators_company = Company::whereNotNull('cycle')->whereHas('stepdata', function ($q) {
                    $q->where('stype', '!=', 1);
                })->whereHas('user', $registeredUserFilter)->get();
            }

            if ($organizations_ids != null || $user->hasRole('admin')) {
                // "Applied Startups" counts every startup that has actually submitted
                // an application to at least one program, ever — regardless of its
                // current pipeline stage or approval status. Disapproval nulls out
                // companies.cycle/step (ScreeningController::changestep()), so those
                // columns can't tell "applied then rejected" apart from "registered,
                // never applied". form_data rows are written the moment a founder
                // submits an application (ExternalCyclesController::registerUser(),
                // CyclesController::saveForm()) and are never cleared on disapproval,
                // so their existence is the durable signal of "this company applied".
                $applied_form_data_org_filter = '';
                if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
                    $applied_form_data_org_filter = " AND EXISTS (
                            SELECT 1 FROM steps st
                            JOIN cycles cyc ON cyc.id = st.cycle_id
                            WHERE st.id = fd.step_id
                            AND cyc.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")
                        )";
                }
                $applied_companies_by_cycle_q = "SELECT
                        COUNT(comp.id) AS company_count
                    FROM
                        companies comp
                    WHERE
                        comp.deleted_at IS NULL
                        AND EXISTS (
                            SELECT 1 FROM form_data fd
                            WHERE fd.company_id = comp.id
                            {$applied_form_data_org_filter}
                        )
                        AND EXISTS (
                            SELECT 1 FROM users u
                            JOIN model_has_roles mhr ON mhr.model_id = u.id
                            JOIN roles r ON r.id = mhr.role_id
                            WHERE u.company_id = comp.id
                            AND r.default_for = 'registered'
                        )
                        AND NOT EXISTS (
                            SELECT 1 FROM users u
                            JOIN model_has_roles mhr ON mhr.model_id = u.id
                            JOIN roles r ON r.id = mhr.role_id
                            WHERE u.company_id = comp.id
                            AND r.default_for IN ('admin', 'organizationmanager', 'subadmin')
                        )
                ";

                $applied_companies_by_cycle = collect(DB::select($applied_companies_by_cycle_q));
                $applied_company_count = $applied_companies_by_cycle->sum('company_count');

                $alumni_company = Almuni::whereHas('cycle', function ($q) use ($organizations_ids) {
                    $q->whereIn('organization_id', $organizations_ids);
                })
                ->select('company_id')
                ->distinct()
                ->get();
                               
            } else {
                $applied_companies_by_cycle = collect([]);
                $applied_company_count = 0;
                $alumni_company = Almuni::select('company_id')->distinct()->get();

            }
        } else {
            $companies = collect([]);
            $incubators_company = collect([]);
            $alumni_company = collect([]);
            $applied_companies_by_cycle = collect([]);
            $applied_company_count = 0;
        }

        if (array_intersect(['dashboard.widgets_cards_mentorship_requests_view', 'dashboard.widgets_cards_mentorship_sessions_durations_view'], $permissions)) {
            $mentorship_requests_q = "SELECT
                    mr.id,
                    mr.period
                FROM
                    mentorship_requests mr
                LEFT JOIN
                    cycles c ON mr.cycle_id = c.id
                WHERE
                    mr.deleted_at IS NULL
            ";
            if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
                $mentorship_requests_q .= " AND c.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")";
            }
            $mentorship_requests = collect(DB::select($mentorship_requests_q));

            $completed_mentorship_sessions_q = "SELECT
                    mr.id
                FROM
                    mentorship_requests mr
                LEFT JOIN
                    cycles c ON mr.cycle_id = c.id
                WHERE
                    mr.deleted_at IS NULL
                    AND mr.done = 1
            ";
            if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
                $completed_mentorship_sessions_q .= " AND c.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")";
            }
            $completed_mentorship_sessions = collect(DB::select($completed_mentorship_sessions_q));
        } else {
            $mentorship_requests = collect([]);
            $completed_mentorship_sessions = collect([]);
        }

        if (array_intersect(['dashboard.widgets_cards_training_sessions_view', 'dashboard.widgets_cards_training_sessions_durations_view'], $permissions)) {
            $training_sessions_q = "SELECT
                    ts.id,
                    ts.type,
                    ts.duration
                FROM
                    training_sessions ts
                LEFT JOIN
                    steps s ON ts.step_id = s.id AND s.stype = 4
                LEFT JOIN
                    cycles c ON c.id = s.cycle_id
                WHERE
                    ts.deleted_at IS NULL
            ";
            if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
                $training_sessions_q .= " AND c.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")";
            }
            $training_sessions = collect(DB::select($training_sessions_q))->map(function ($session) {
                $session->duration = !empty($session->duration) ? $session->duration : 0;
                return $session;
            });
        } else {
            $training_sessions = collect([]);
        }

        if (array_intersect(['dashboard.widgets_cards_services_view'], $permissions)) {
            $services = DB::table('services AS s')
                ->select([
                    's.id',
                    's.name_en',
                    's.name_ar',
                    's.price',
                    's.description_en',
                    's.description_ar',
                    'sc.id AS service_category_id',
                    'sc.name_en AS service_category_name_en',
                    'sc.name_ar AS service_category_name_ar',
                ])
                ->leftJoin('service_categories AS sc', 'sc.id', '=', 's.service_category_id')
                ->join('organization_service AS so', 'so.service_id', '=', 's.id')
                ->where('s.deleted_at', null)
                // ->where('so.organization_id', $user->organization_id)
                ->when($user->hasRole('serviceProvider'), function ($query) use ($user) {
					$provider_id = $user?->service_provider?->id ?? 0;
					$query->where('s.service_provider_id', $provider_id);
				})
				->when($user->hasRole('registered'), function ($query) {
					$query->where('s.status', true);
				})
				->whereIn('so.organization_id', $orgIds)
                ->orderBy('s.created_at', 'desc')
        	    ->get();

            $services = $services->groupBy('id')->map(function ($groupedService) use ($l) {
                $service = $groupedService->first();
                $output = new stdClass;
                $output->id = $service->id;
                $output->name = $service->{'name_' . $l};
                $output->price = floatval($service->price);
                $output->description = $service->{'description_' . $l};
                $service_category = new stdClass;
                $service_category->id = $service->service_category_id;
                $service_category->name = $service->{'service_category_name_' . $l};
                $output->service_category = $service_category;
                return $output;
            })->values();
        } else {
            $services = collect([]);
        }

        if (array_intersect(['dashboard.widgets_cards_service_requests_view'], $permissions)) {
            $my_requests_q = "SELECT
                    sr.id
                FROM
                    services_requests sr
                LEFT JOIN
                    services s ON sr.service_id = s.id
                WHERE
                    sr.deleted_at IS NULL
                    AND sr.user_id = ?
            ";
            $my_requests = collect(DB::select($my_requests_q, [$user->id]));
        } else {
            $my_requests = collect([]);
        }

        if (array_intersect(['dashboard.widgets_cards_my_mentors_view'], $permissions)) {
            $my_mentors_q = "SELECT 
                    u.id,
                    CONCAT(TRIM(COALESCE(u.first_name_en, '')), ' ', TRIM(COALESCE(u.last_name_en, ''))) AS name_en,
                    CONCAT(TRIM(COALESCE(u.first_name_ar, '')), ' ', TRIM(COALESCE(u.last_name_ar, ''))) AS name_ar,
                    u.gender,
                    u.email,
                    r.default_for AS `role`
                FROM
                    users u
                LEFT JOIN
                    model_has_roles mhr ON u.id = mhr.model_id AND model_type = 'App\\\User'
                LEFT JOIN
                    roles r ON mhr.role_id = r.id
                LEFT JOIN
                    cycles_mentors cm ON u.id = cm.user_id
                LEFT JOIN
                    cycles c ON cm.cycle_id = c.id
                WHERE
                    u.deleted_at IS NULL
                    AND r.default_for = 'mentor'
            ";
            // if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
            //     $my_mentors_q .= " AND c.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")";
            // }
            $my_mentors = collect(DB::select($my_mentors_q));
            $my_mentors = $my_mentors->groupBy('id')->map(function ($user) {
                $oneUser = $user->first();
                $roles = $user->map(function ($item) {
                    return strtolower($item->role);
                })->unique()->toArray();
                unset($oneUser->role);
                $oneUser->roles = $roles;
                return $oneUser;
            })->values();
        } else {
            $my_mentors = collect([]);
        }
        if( $user->hasRole('Registered') )
        {
            $cycle = $user->company->cycle ?? 0;
            $mentorIds = DB::table('cycles_mentors')->where('cycle_id', $cycle)->pluck('user_id')->toArray();
            $my_mentors =  $my_mentors->whereIn('id', $mentorIds);
        }

        if (array_intersect(['dashboard.widgets_cards_filtration_view'], $permissions)) {
            // A startup belongs to "First screening" based on its CURRENT step
            // (companies.step = the screening step), not on whether today falls
            // inside the step's from/to window. The old `today BETWEEN from AND to`
            // gate hid valid in-progress initial screenings whose date window had
            // already passed (e.g. a judge still had startups sitting at the step).
            // The company join below already restricts to startups currently at
            // the screening step, so the window filter only ever dropped real rows.
            $inputs = [];
            $isJudgeOnly = !$user->hasAnyRole(['admin', 'organizationmanager']);
            $screeningJoinUserClause = $isJudgeOnly ? ' AND sc.user_id = ?' : '';
            if ($isJudgeOnly) { array_unshift($inputs, $user->id); }

            $filtration_q = "SELECT
                    s.id,
                    s.arr,
                    s.title,
                    s.from,
                    s.to,
                    s.description,
                    s.stype as `type`,
                    c.id AS cycle_id,
                    c.title AS `cycle_title`,
                    c.start AS `cycle_start`,
                    c.end AS `cycle_end`,
                    sc.user_id AS `screening_user_id`,
                    sc.users AS `screening_users`,
                    sc.panel AS `screening_panel`,
                    sc.session_date AS `screening_session_date`,
                    sc.start_time AS `screening_start_time`,
                    sc.end_time AS `screening_end_time`,
                    sc.`interval` AS `screening_interval`,
                    sc.meeting_link AS `screening_meeting_link`,
                    sc.meeting_password AS `screening_meeting_password`,
                    cp.id AS company_id,
                    cp.name_en AS `company_name_en`,
                    cp.name_ar AS `company_name_ar`,
                    cp.logo AS `company_logo`,
                    u.id AS company_user_id,
                    u.first_name_en AS company_user_first_name_en,
                    u.last_name_en AS company_user_last_name_en,
                    u.first_name_ar AS company_user_first_name_ar,
                    u.last_name_ar AS company_user_last_name_ar,
                    u.title_ar AS company_user_title_ar,
                    u.title_en AS company_user_title_en,
                    u.img_url AS company_user_img_url
                FROM
                    steps s
                LEFT JOIN
                    cycles c ON c.id = s.cycle_id
                LEFT JOIN
                    screenings sc ON sc.step_id = s.id $screeningJoinUserClause
                LEFT JOIN
                    companies cp ON cp.step = sc.step_id
                        AND (
                            sc.users IS NULL
                            OR JSON_LENGTH(sc.users) = 0
                            OR JSON_CONTAINS(sc.users, CAST(cp.id AS CHAR))
                        )
                LEFT JOIN
                    users u ON u.company_id = cp.id AND u.HaveCompany = 1
                WHERE
                    s.deleted_at IS NULL
                    AND s.stype = 2
                    AND s.id IN (
                        SELECT
                            step_id
                        FROM
                            screenings
                        WHERE
                            deleted_at IS NULL
            ";
            if ($isJudgeOnly) {
                $filtration_q .= " AND user_id = ? ";
                $inputs[] = $user->id;
            }
            $filtration_q .= " )";
            if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
                $filtration_q .= " AND c.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")";
            }
            $filtration = collect(DB::select($filtration_q, $inputs));
        } else {
            $filtration = collect([]);
        }

        $filtration = $filtration->groupBy('id')->map(function ($groupedSteps) use ($l) {
            $s = $groupedSteps->first();
            $step = new stdClass;
            $step->id = $s->id;
            $step->arr = $s->arr;
            $step->type = $s->type;
            $step->title = $s->title;
            $step->from = Carbon::parse($s->from)->format('d-m-Y') ?? '-';
            $step->to = Carbon::parse($s->to)->format('d-m-Y') ?? '-';
            $step->description = $s->description;
            $step->cycle = new stdClass;
            $step->cycle->id = $s->cycle_id;
            $step->cycle->title = $s->cycle_title;
            $step->cycle->start = Carbon::parse($s->cycle_start)->format('d-m-Y') ?? '-';
            $step->cycle->end = Carbon::parse($s->cycle_end)->format('d-m-Y') ?? '-';
            $step->groups = $groupedSteps->map(function ($oneStep) {
                $users = $oneStep->screening_users;
                $groups = json_decode('[' . str_replace(['[', ']'], ' ', json_encode($users)) . ']');
                return $groups;
            });
            $step->companies = $groupedSteps->filter(function ($step) {
                return !is_null($step->company_id);
            })->groupBy('company_id')->map(function ($oneStep) use ($l) {
                $c = $oneStep->first();
                $company = new stdClass;
                $company->id = $c->company_id;
                $company->name = $c->{"company_name_" . $l};
                $company->logo = Company::logo_handler($c->company_logo);
                $company->users = $oneStep->filter(function ($step) {
                    return !is_null($step->company_user_id);
                })->map(function ($step) use ($l) {
                    $user = new stdClass;
                    $user->id = $step->company_user_id;
                    $user->first_name = $step->{"company_user_first_name_" . $l};
                    $user->last_name = $step->{"company_user_last_name_" . $l};
                    $user->name = $user->first_name . ' ' . $user->last_name;
                    $user->title = $step->{"company_user_title_" . $l};
                    $user->logo = User::image_handler($step->company_user_img_url);
                    return $user;
                })->unique('id')->values();

                // Per-startup meeting slot (GV-3311). Derived from the panel's
                // start time, the admin's interval and this startup's position
                // within the panel, so each startup sees its own window rather
                // than the panel-wide one.
                $slot = \App\Services\ScreeningSlotService::slotFor(
                    $c->screening_session_date ?? null,
                    $c->screening_start_time ?? null,
                    isset($c->screening_interval) ? (int) $c->screening_interval : null,
                    \App\Services\ScreeningSlotService::positionOf($c->screening_users ?? [], $c->company_id),
                    $c->screening_end_time ?? null
                );
                $company->meeting_date = $slot['start'] ? $slot['start']->format('d-m-Y') : null;
                $company->meeting_time = \App\Services\ScreeningSlotService::formatRange($slot);
                $company->meeting_start = $slot['start'];
                $company->meeting_end = $slot['end'];
                $company->join_url = $c->screening_meeting_link ?? null;
                $company->passcode = $c->screening_meeting_password ?? null;

                return $company;
            })->values();
            $step->panel = $groupedSteps->map(function ($oneStep) {
                return $oneStep->screening_panel;
            });
            return $step;
        })->values();

        $mySteps = Screening::where('user_id', $user->id)
            // ->whereIn('step_id', $filtration->pluck('id')->toArray())
            ->get();
        $groups = $mySteps->whereIn('step_id', $filtration->pluck('id')->toArray())->pluck('users')->flatten()->unique()->whereNotNull()->values();

        if (array_intersect(['dashboard.widgets_cards_evaluation_view'], $permissions)) {
            $evaluation_q = "SELECT
                    fs.id,
                    fs.company_id,
                    c.name_en AS company_name_en,
                    c.name_ar AS company_name_ar,
                    c.logo AS company_logo,
                    u.id AS company_user_id,
                    u.img_url AS company_user_img_url,
                    u.first_name_en AS company_user_first_name_en,
                    u.last_name_en AS company_user_last_name_en,
                    u.first_name_ar AS company_user_first_name_ar,
                    u.last_name_ar AS company_user_last_name_ar,
                    u.title_ar AS company_user_title_ar,
                    u.title_en AS company_user_title_en,
                    fs.step_id,
                    s.title AS step_title,
                    s.from AS step_start,
                    s.to AS step_end,
                    s.description AS step_description,
                    cy.id AS cycle_id,
                    cy.title AS cycle_title,
                    cy.start AS cycle_start,
                    cy.end AS cycle_end,
                    fs.judges,
                    fs.datetime,
                    fs.duration,
                    fs.zoom_id,
                    fs.zoom_passcode,
                    fs.start_url,
                    fs.join_url,
                    sc.session_date AS `panel_session_date`,
                    sc.start_time AS `panel_start_time`,
                    sc.end_time AS `panel_end_time`,
                    sc.`interval` AS `panel_interval`,
                    sc.users AS `panel_users`
                FROM
                    finalscreenings fs
                LEFT JOIN
                    companies c ON fs.company_id = c.id
                LEFT JOIN
                    users u ON c.id = u.company_id
                LEFT JOIN
                    steps s ON fs.step_id = s.id
                LEFT JOIN
                    cycles cy ON s.cycle_id = cy.id
                LEFT JOIN
                    screenings sc ON sc.id = (
                        -- One panel per company, chosen deterministically.
                        --
                        -- `screenings` holds ONE ROW PER JUDGE PER PANEL, and a
                        -- company may sit in several panels of the same step, so
                        -- joining on (step_id, JSON_CONTAINS(users)) alone matched
                        -- many rows carrying DIFFERENT meeting windows. Which one
                        -- won was down to arbitrary row order, so a judge could be
                        -- shown another panel window -- or an unscheduled panel
                        -- empty one, which never expires and leaves the row stuck
                        -- reading as not started.
                        --
                        -- Prefer a panel that is actually scheduled, then the
                        -- earliest one, then the lowest id, so the choice is stable
                        -- across requests.
                        SELECT sc2.id
                        FROM screenings sc2
                        WHERE sc2.step_id = fs.step_id
                            AND sc2.deleted_at IS NULL
                            AND JSON_CONTAINS(sc2.users, CAST(fs.company_id AS CHAR))
                        ORDER BY
                            (sc2.session_date IS NULL) ASC,
                            sc2.session_date ASC,
                            sc2.start_time ASC,
                            sc2.panel ASC,
                            sc2.id ASC
                        LIMIT 1
                    )
                WHERE
                    fs.deleted_at IS NULL
                    AND c.step = fs.step_id
            ";
            if (!$user->hasAnyRole(['admin', 'organizationmanager'])) {
                // judges may be stored either as a JSON array of string IDs (legacy: ["15","16"])
                // or as a double-encoded JSON string of int IDs ("[15,16]") from older addJudgingPanels.
                // Normalize via JSON_UNQUOTE when STRING, then check both string and int membership.
                $uid = (int) $user->id;
                $evaluation_q .= " AND (
                    JSON_CONTAINS(
                        CASE WHEN JSON_TYPE(fs.judges) = 'STRING' THEN JSON_UNQUOTE(fs.judges) ELSE fs.judges END,
                        JSON_QUOTE('{$uid}')
                    ) = 1
                    OR JSON_CONTAINS(
                        CASE WHEN JSON_TYPE(fs.judges) = 'STRING' THEN JSON_UNQUOTE(fs.judges) ELSE fs.judges END,
                        '{$uid}'
                    ) = 1
                )";
            }
            if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
                $evaluation_q .= " AND cy.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")";
            }
            $evaluation = collect(DB::select($evaluation_q))->map(function ($e) {
                $judges = json_decode($e->judges, true);
                // Handle double-encoded values like "\"[1,2]\"" by decoding once more.
                if (is_string($judges)) { $judges = json_decode($judges, true); }
                if (!is_array($judges)) { $judges = []; }
                $e->judges = array_map('intval', $judges);
                return $e;
            });
            $evaluation = $evaluation->groupBy('step_id')->map(function( $groupedEvaluation ) use ( $l ) {
                $_step = $groupedEvaluation->first();
                $step = new stdClass;
                $step->id = $_step->step_id;
                $step->title = $_step->step_title;
                $step->start = $_step->step_start;
                $step->end = $_step->step_end;
                $step->description = $_step->step_description;
                $step->cycle = new stdClass;
                $step->cycle->id = $_step->cycle_id;
                $step->cycle->title = $_step->cycle_title;
                $step->cycle->start = $_step->cycle_start;
                $step->cycle->end = $_step->cycle_end;
                $step->companies = $groupedEvaluation->groupBy('company_id')->map(function ( $groupedCompanies ) use ( $l ) {
                    $_company = $groupedCompanies->first();
                    $company = new stdClass;
                    $company->id = $_company->company_id;
                    $company->name = $_company->{'company_name_' . $l};
                    $company->logo = Company::logo_handler( $_company->company_logo );
                    $company->users = $groupedCompanies->map(function ( $u ) use ( $l ) {
                        $user = new stdClass;
                        $user->id = $u->company_user_id;
                        $user->name = $u->{'company_user_first_name_' . $l} . ' ' . $u->{'company_user_last_name_' . $l};
                        $user->title = $u->{'company_user_title_' . $l};
                        $user->logo = User::image_handler( $u->company_user_img_url );
                        return $user;
                    })->unique('id')->values();
                    $company->judges = $_company->judges;
                    $company->datetime = $_company->datetime;
                    $company->duration = $_company->duration;
                    $company->zoom_id = $_company->zoom_id;
                    $company->passcode = $_company->zoom_passcode;
                    $company->start_url = $_company->start_url;
                    $company->join_url = $_company->join_url;

                    // Per-startup meeting slot (GV-3312). When the admin scheduled
                    // the panel with an interval, each startup gets its own window
                    // derived from the panel start time and its position in the
                    // panel; otherwise fall back to the stored panel datetime.
                    $slot = \App\Services\ScreeningSlotService::slotFor(
                        $_company->panel_session_date ?? null,
                        $_company->panel_start_time ?? null,
                        isset($_company->panel_interval) ? (int) $_company->panel_interval : null,
                        \App\Services\ScreeningSlotService::positionOf($_company->panel_users ?? [], $_company->company_id),
                        $_company->panel_end_time ?? null
                    );
                    $company->meeting_start = $slot['start'];
                    $company->meeting_end = $slot['end'];
                    $company->meeting_date = $slot['start'] ? $slot['start']->format('d-m-Y') : null;
                    $company->meeting_time = \App\Services\ScreeningSlotService::formatRange($slot);

                    return $company;
                })->values();
                return $step;
            })->values();
        } else {
            $evaluation = collect([]);
        }

        if (array_intersect(['dashboard.widgets_cards_coming_sessions_view', 'dashboard.widgets_tables.my_next_mentorship_sessions_view'], $permissions)) {
            $upcoming_sessions_q = "SELECT
                    mr.id AS `id`,
                    mr.period,
                    mr.admin_comment,
                    mr.zoom_date,
                    mr.start_url,
                    mr.zoom_passcode,
                    mr.mentor_id AS `mentor_id`,
                    mr.session_type AS `session_type`,
                    mr.Approved AS `approved`,
                    mr.organization_id AS `organization_id`,
                    u.id AS `user_id`,
                    u.img_url AS `user_image_url`,
                    u.first_name_ar AS `first_name_ar`,
                    u.last_name_ar AS `last_name_ar`,
                    u.first_name_en AS `first_name_en`,
                    u.last_name_en AS `last_name_en`,
                    u.title_ar AS `user_title_ar`,
                    u.title_en AS `user_title_en`,
                    c.id AS `company_id`,
                    c.logo AS `company_logo`,
                    c.name_ar AS `company_name_ar`,
                    c.name_en AS `company_name_en`,
                    o.google_map_url AS `google_map_url`,
                    o.address AS `org_address`,
                    cy.offline_location_address AS `offline_location_address`,
                    cy.offline_location_map AS `offline_location_map`,
                    o.name_en AS `organization_name_en`,
                    o.name_ar AS `organization_name_ar`
                FROM
                    mentorship_requests mr
                LEFT JOIN
                    users u ON u.id = mr.user_id
                LEFT JOIN
                    companies c ON c.id = u.company_id
                LEFT JOIN
                    cycles cy ON cy.id = mr.cycle_id
                LEFT JOIN
                    organizations o ON o.id = mr.organization_id
                WHERE   
                    mr.deleted_at IS NULL
                    AND DATE_ADD(mr.zoom_date, INTERVAL mr.period MINUTE) >= ?
                    AND mr.approved = 'Yes'
                    
            ";
            if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
                $upcoming_sessions_q .= " AND mr.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")";
            }
            if (!empty($user->company_id) && !is_null($user->company_id) && trim($user->company_id)) {
                $upcoming_sessions_q .= " AND mr.user_id = " . $user->id;
            }
            if ($user->hasAnyRole(['mentor'])) {
                $upcoming_sessions_q .= " AND mr.mentor_id = " . $user->id;
            }
            $params = [];
            $params[] = date("Y-m-d H:i:s");
            $l = app()->getLocale();
            $upcoming_sessions = collect(DB::select($upcoming_sessions_q, $params))->map(function ($session) use ($l) {
                $output = new stdClass;
                $output->id = $session->id;
                $output->period = $session->period;
                $output->admin_comment = $session->admin_comment;
                $output->zoom_date = $session->zoom_date;
                $output->start_url = $session->start_url;
                $output->passcode = $session->zoom_passcode;
                $output->session_type = $session->session_type;
                $output->approved = $session->approved;
                $output->organization_id = $session->organization_id;

                $image = User::image_handler($session->user_image_url);
                $user = new stdClass;
                $user->id = $session->user_id;
                $user->image_url = $image;
                $user->name = $session->{"first_name_{$l}"} . ' ' . $session->{"last_name_{$l}"};
                $user->title = $session->{"user_title_{$l}"};

                $companyLogo = Company::logo_handler($session->company_logo);
                $company = new stdClass;
                $company->id = $session->company_id;
                $company->logo_url = $companyLogo;
                $company->name = $session->{"company_name_{$l}"};
                $user->company = $company;
                $output->user = $user;

                $organization = new stdClass;
                $organization->id = $session->organization_id;
                $organization->google_map_url = $session->offline_location_map ? $session->offline_location_map : $session->google_map_url;
                $organization->address = $session->offline_location_address ? $session->offline_location_address : $session->org_address;
                $organization->name = $session->{"organization_name_{$l}"};
                $output->organization = $organization;

                $output->meeting_status = mentorshipMeetingStatus($output->zoom_date, $output->period);

                return $output;
            });
        } else {
            $upcoming_sessions = collect([]);
        }
        
        if (array_intersect(['dashboard_cards.my_programs'], $permissions)) {
            $my_cycles_q = "SELECT 
                    c.id AS `id`
                FROM
                    cycles c
                LEFT JOIN
                    cycles_mentors cm ON cm.cycle_id = c.id
                WHERE
                    cm.user_id = ?
            ";
            $params = [];
            $params[] = $user->id;
            $my_cycles = collect(DB::select($my_cycles_q, $params));
        } else {
            $my_cycles = collect([]);
        }

        if ($user->hasAnyRole(['mentor']) && array_intersect(['dashboard.widgets_cards_startup_attendance_rate_view'], $permissions)) {
            $completed_sessions_q = "SELECT 
                    mr.id AS `id`,
                    mr.done AS `done`
                FROM
                    mentorship_requests mr
                 WHERE   
                    mr.deleted_at IS NULL 
                    AND mr.mentor_id = ?
                    AND mr.start_url IS NOT NULL
                    AND mr.zoom_date <= ?
            ";

            $params = [];
            $params[] = $user->id;
            $params[] = now()->format('Y-m-d');
            $completed_sessions = collect(DB::select($completed_sessions_q, $params));
            $count = $completed_sessions->count();
            $attends_rate = $count > 0 ? (($completed_sessions->sum('done') / $count) * 100 . '%') : '0%';
        } else {
            $attends_rate = '0%';
        }

        $sessionWithoutTasks = collect([]);
        $completedTasks = collect([]);
        $nonCompletedTasks = collect([]);
        if ($user->hasAnyRole(['mentor']) && array_intersect(['dashboard.widgets_cards_sessions_without_tasks_view', 'dashboard.widgets_cards_completed_tasks_view', 'dashboard.widgets_cards_non_completed_tasks_view'], $permissions)) {
            $tasks_q = "SELECT 
                    mr.id,
                    mt.id AS `task_id`,
                    mt.title AS `task_title`,
                    mt.status AS `task_status`
                FROM
                    mentorship_requests mr
                LEFT JOIN
                    mentorship_tasks mt ON mt.mentorship_request_id = mr.id
                WHERE
                    mr.deleted_at IS NULL 
                    AND mr.mentor_id = ?
            ";

            $params = [];
            $params[] = $user->id;
            $tasks = collect(DB::select($tasks_q, $params));
            if (array_intersect(['dashboard.widgets_cards_sessions_without_tasks_view'], $permissions)) {
                $sessionWithoutTasks = $tasks->whereNull('task_id')->values();
            }
            if (array_intersect(['dashboard.widgets_cards_completed_tasks_view'], $permissions)) {
                $completedTasks = $tasks->whereNotNull('task_id')->where('task_status', 'completed')->values();
            }
            if (array_intersect(['dashboard.widgets_cards_non_completed_tasks_view'], $permissions)) {
                $nonCompletedTasks = $tasks->whereNotNull('task_id')->where('task_status', 'pending')->values();
            }
        }

        if ($user->hasAnyRole(['mentor']) && array_intersect(['dashboard.widgets_cards_rate_view'], $permissions)) {
            $sessions_q = "SELECT 
                    mr.id AS `id`,
                    mr.rating AS `rating`
                FROM
                    mentorship_requests mr
                 WHERE   
                    mr.deleted_at IS NULL 
                    AND mr.mentor_id = ?
            ";

            $params = [];
            $params[] = $user->id;
            $sessions = collect(DB::select($sessions_q, $params));
            $my_rating = $sessions->avg('rating') ?? 0;
        } else {
            $my_rating = 0;
        }

        $judgers = $users->filter(function ($user) {
            return in_array('judger', $user->roles);
        })->values();
        $mentors = $users->filter(function ($user) {
            return in_array('mentor', $user->roles);
        })->values();
        $startups = $users->filter(function ($user) {
            return in_array('registered', $user->roles);
        })->values();
        $managers = $users->filter(function ($user) {
            return /*in_array('admin', $user->roles) ||*/ in_array('organizationmanager', $user->roles);
        })->values();

        $data = [];
        $cType = 0;
        $cycle_id = 0;
        $stepData = null;
        $cycleData = null;
        $organization = null;

        $latest_events_q = "SELECT
                c.id,
                c.title,
                c.start
            FROM
                calendars c
            WHERE
                c.start >= ?
                AND (
                    c.user_id = $user->id
                    OR c.isPrivate IS null
                )
            ORDER BY
                c.start ASC
            LIMIT
                5
        ";
        $latest_events = collect(DB::select($latest_events_q, [now()->format('Y-m-d')]));

        $conditions = '';
        if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
            $conditions = " WHERE s.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")";
        }
        $latest_services_q = "SELECT
                s.id,
                s.name_en,
                s.name_ar,
                sc.id AS category_id,
                sc.name_en AS category_name_en,
                sc.name_ar AS category_name_ar
            FROM 
                services s
            LEFT JOIN
                service_categories sc ON s.service_category_id = sc.id
            $conditions
            ORDER BY 
                s.created_at DESC
            LIMIT
                5
        ";
        $latest_services = collect(DB::select($latest_services_q))->map(function ($item) use ($l) {
            $service = new stdClass;
            $service->id = $item->id;
            $service->name_en = $item->name_en;
            $service->name_ar = $item->name_ar;
            $service->category = new stdClass;
            $service->category->id = $item->category_id;
            $service->category->name_en = $item->category_name_en;
            $service->category->name_ar = $item->category_name_ar;
            return $service;
        });

        $working_cycles_q = "SELECT
                c.id,
                c.title,
                c.description,
                c.start,
                c.organization_id,
                c.type,
                c.allow_non_company,
                o.name_en AS `org_name_en`,
                o.name_ar AS `org_name_ar`,
                o.img_url AS `org_img_url`
            FROM
                cycles c
            LEFT JOIN
                organizations o ON o.id = c.organization_id
            WHERE
                c.private IS null AND c.type = 'General_Program'
                AND ? BETWEEN c.start AND c.end
        ";
        $workingCycles = collect(DB::select($working_cycles_q, [now()->format('Y-m-d')]))->map(function ($item) use ($l) {
            $cycle = new stdClass;
            $cycle->id = $item->id;
            $cycle->title = $item->title;
            $cycle->description = $item->description;
            $cycle->start = Carbon::parse($item->start)->format('Y-m-d');
            $cycle->organization = new stdClass;
            $cycle->organization->id = $item->organization_id;
            $cycle->organization->name = $item->{'org_name_' . $l};
            $cycle->organization->image_url = Organization::image_handler($item->org_img_url);
            $cycle->type = $item->type;
            $cycle->allow_non_company = $item->allow_non_company;
            $preRequests = [];
            $user = auth()->user();
            if (isset($cycle->type) && $cycle->type === 'General_Program' && $user) {


                if (method_exists($user, 'hasCompletedProfile')) {
                    $profileComplete = $user->hasCompletedProfile();
                } else {
                    $profileComplete = filled($user->phone)
                        && filled($user->city_id)
                        && filled($user->country_id)
                        && filled($user->address)
                        && filled($user->timezone);
                }

                if (! $profileComplete) {
                    if (function_exists('route_exists') && route_exists('user.profile_Edit')) {
                        $profileUrl = route('user.profile_Edit');
                    } elseif (function_exists('route_exists') && route_exists('user.profile_Edit')) {
                        $profileUrl = route('user.profile_Edit');
                    } else {
                        $profileUrl = route('user.profile_Edit');
                    }

                    $preRequests[] = [
                        'key'   => 'profile',
                        'label' => 'admin.Complete Profile',
                        'url'   => $profileUrl,
                        'id'    => 'pre-request-profile-' . $cycle->id,
                    ];
                }

                // Only require startup profile if program doesn't allow non-company
                if (empty($cycle->allow_non_company)) {
                    if (method_exists($user, 'hasCompletedStartup')) {
                        $startupComplete = method_exists($user, 'hasCompletedStartup') ? $user->hasCompletedStartup() : $user->hasCompletedCompany();
                    } else {
                        $startupComplete = false;
                        if (method_exists($user, 'company') || isset($user->company)) {
                            $company = $user->company ?? null;
                            $startupComplete = $company
                                && filled($company->name_en ?? '')
                                && filled($company->est_date ?? '')
                                && filled($company->address_en ?? '')
                                && filled($company->stage ?? '');
                        }
                    }

                    if (!$startupComplete) {
                        if (function_exists('route_exists') && route_exists('company.profile_view_mine')) {
                            $startupUrl = route('company.profile_view_mine');
                        } elseif (function_exists('route_exists') && route_exists('company.profile_view_mine')) {
                            $startupUrl = route('company.profile_view_mine');
                        } elseif (function_exists('route_exists') && route_exists('company.profile_view_mine')) {
                            $startupUrl = route('company.profile_view_mine');
                        } else {
                            $startupUrl = url('/dashboard/myCompany');
                        }
                        $preRequests[] = [
                            'key'   => 'startup',
                            'label' => 'admin.Complete Startup/Idea',
                            'url'   => $startupUrl,
                            'id'    => 'pre-request-startup-' . $cycle->id,
                        ];
                    }
                }
            }

            $cycle->pre_requests = $preRequests;
            return $cycle;
        });
        
        $myFinal = null;
        $myInitial = null;
        // GV-3320/3321: defaults so the Evaluation Session header renders even
        // when the user has no company/step yet.
        $data['evaluation_step_title'] = null;
        $data['evaluation_program_title'] = null;
        $days = request()->query('days', 14);
        $company = $user->company;
        $sessions = collect([]);
        $organization_id = null;
        if ($company && $company->cycledata) {
            $sessions_q = "SELECT
                    ts.id,
                    ts.type,
                    ts.title,
                    ts.datetime,
                    ts.enddatetime AS `end_datetime`,
                    ts.duration,
                    ts.join_url,
                    ts.trainer_name,
                    ts.training_location AS `location`
                FROM
                    training_sessions ts
                WHERE
                    ts.step_id = ?
                ORDER BY
                    ts.datetime ASC
                LIMIT 5";
            $sessions = collect(DB::select($sessions_q, [$company->step]));

            // GV-8301: the widget's join icon must close once the session has ended.
            $sessions = $sessions->map(function ($session) use ($user) {
                $session->can_join = MeetingWindowService::isOpen(
                    TrainingSession::wallClock($session->datetime),
                    TrainingSession::wallClock($session->end_datetime),
                    $user,
                    'founder'
                );

                return $session;
            });

            $myFinal_q = "SELECT
                    fs.id,
                    fs.judges,
                    fs.join_url,
                    fs.datetime,
                    fs.duration,
                    fs.zoom_passcode,
                    sc.session_date AS `panel_session_date`,
                    sc.start_time AS `panel_start_time`,
                    sc.end_time AS `panel_end_time`,
                    sc.`interval` AS `panel_interval`,
                    sc.users AS `panel_users`,
                    u.id AS `user_id`,
                    u.img_url AS `user_image_url`,
                    u.first_name_en AS `user_first_name_en`,
                    u.first_name_ar AS `user_first_name_ar`,
                    u.last_name_en AS `user_last_name_en`,
                    u.last_name_ar AS `user_last_name_ar`,
                    u.title_en AS `user_title_en`,
                    u.title_ar AS `user_title_ar`
                FROM
                    finalscreenings fs
                LEFT JOIN
                    companies c ON c.id = fs.company_id
                LEFT JOIN
                    users u ON u.company_id = c.id AND u.id = ?
                LEFT JOIN
                    screenings sc ON sc.id = (
                        -- One panel per company, chosen deterministically.
                        --
                        -- `screenings` holds ONE ROW PER JUDGE PER PANEL, and a
                        -- company may sit in several panels of the same step, so
                        -- joining on (step_id, JSON_CONTAINS(users)) alone matched
                        -- many rows carrying DIFFERENT meeting windows. Which one
                        -- won was down to arbitrary row order, so a judge could be
                        -- shown another panel window -- or an unscheduled panel
                        -- empty one, which never expires and leaves the row stuck
                        -- reading as not started.
                        --
                        -- Prefer a panel that is actually scheduled, then the
                        -- earliest one, then the lowest id, so the choice is stable
                        -- across requests.
                        SELECT sc2.id
                        FROM screenings sc2
                        WHERE sc2.step_id = fs.step_id
                            AND sc2.deleted_at IS NULL
                            AND JSON_CONTAINS(sc2.users, CAST(fs.company_id AS CHAR))
                        ORDER BY
                            (sc2.session_date IS NULL) ASC,
                            sc2.session_date ASC,
                            sc2.start_time ASC,
                            sc2.panel ASC,
                            sc2.id ASC
                        LIMIT 1
                    )
                WHERE
                    fs.company_id = ?
                AND fs.step_id = ?
                LIMIT 1";
            $myFinal = collect(DB::select($myFinal_q, [$user->id, $company->id, $company->step]))->first();
            if (!empty($myFinal)) {
                // GV-3321: this startup's own slot within the final panel.
                $finalSlot = \App\Services\ScreeningSlotService::slotFor(
                    $myFinal->panel_session_date ?? null,
                    $myFinal->panel_start_time ?? null,
                    isset($myFinal->panel_interval) ? (int) $myFinal->panel_interval : null,
                    \App\Services\ScreeningSlotService::positionOf($myFinal->panel_users ?? [], $company->id),
                    $myFinal->panel_end_time ?? null
                );
                $myFinal->meeting_start = $finalSlot['start'];
                $myFinal->meeting_end = $finalSlot['end'];
                $myFinal->meeting_date = $finalSlot['start'] ? $finalSlot['start']->format('d-m-Y') : null;
                $myFinal->meeting_time = \App\Services\ScreeningSlotService::formatRange($finalSlot);
                $myFinal->passcode = $myFinal->zoom_passcode ?? null;

                $judges = json_decode($myFinal->judges, true) ?? [];
                if (!is_array($judges)) {
                    $judges = [];
                }

                if (count($judges) > 0)
                {
                    $placeholders = implode(',', array_fill(0, count($judges), '?'));

                    $judgers_q = "SELECT
                            u.id,
                            u.first_name_en,
                            u.first_name_ar,
                            u.last_name_en,
                            u.last_name_ar,
                            u.img_url
                        FROM
                            users u
                        WHERE u.id IN ( $placeholders )
                    ";

                    $judgers = collect(DB::select($judgers_q, $judges))->map(function ($item) use ($l) {
                        $judger = new stdClass;
                        $judger->id = $item->id;
                        $judger->name = trim($item->{'first_name_' . $l} . ' ' . $item->{'last_name_' . $l});
                        // `image_url` so the shared judges-cell partial renders
                        // final and initial screening rows identically.
                        $judger->image_url = User::image_handler($item->img_url);
                        $judger->img_url = $judger->image_url;
                        return $judger;
                    });

                    $myFinal->judges = $judgers;
                    $myFinal->company = new stdClass;
                    $myFinal->company->id = $company->id;
                    $myFinal->company->user = new stdClass;
                    $myFinal->company->user->id = $myFinal->user_id;
                    $myFinal->company->user->image_url = User::image_handler($myFinal->user_image_url);
                    $myFinal->company->user->first_name = $myFinal->{'user_first_name_' . $l};
                    $myFinal->company->user->last_name = $myFinal->{'user_last_name_' . $l};
                    $myFinal->company->user->name = trim($myFinal->company->user->first_name . ' ' . $myFinal->company->user->last_name);
                    $myFinal->company->user->title = $myFinal->{'user_title_' . $l};

                    unset($myFinal->user_id);
                    unset($myFinal->user_image_url);
                    unset($myFinal->user_first_name_en);
                    unset($myFinal->user_first_name_ar);
                    unset($myFinal->user_last_name_en);
                    unset($myFinal->user_last_name_ar);
                    unset($myFinal->user_title_en);
                    unset($myFinal->user_title_ar);
                } else {
                    // GV-8357: leaving the raw JSON string here made the blade
                    // @foreach fatal on judge-less final screenings.
                    $judgers = collect();
                    $myFinal->judges = $judgers;
                }
            }

            $stepData = $company->stepdata;
            if (!empty($stepData)) {
                $cType = $stepData->stype;
            }
            // GV-3320/3321: the Evaluation Session section shows which program and
            // step the scheduled meeting belongs to.
            $data['evaluation_step_title'] = $stepData->title ?? null;
            $data['evaluation_program_title'] = $company->cycledata->title ?? null;
            $cycle_id = $company->cycle;
            $cycleData = $company->cycledata;
            if ($cycleData) {
                $organization = $cycleData->organization;
                if (!empty($organization)) {
                    $organization_id = $organization->id;
                }
            }

            $myInitial = Screening::where('step_id', $company->step)->with(['user'])->get();

            $companyId = $company->id;
            $myInitial = $myInitial->map(function ($item) {
                $users = is_array( $item->users ) ? $item->users : json_decode($item->users, true);
                $item->users = $users;
                return $item;
            })->filter(function ($item) use ($companyId) {
                return in_array($companyId, $item->users);
            })->values();

            $myInitial = $myInitial->map(function ($item) use ($companyId) {
                $item->judge = $item->user;
                if( !is_null( $item->session_date ) )
                {
                    $item->dateTime = ( $item->session_date . ' ' . ($item->start_time ?? '00:00:00') ) ?? null;
                    $item->endTime = ( $item->session_date . ' ' . ($item->end_time ?? '00:00:00') ) ?? null;
                    $item->duration = $item->dateTime && $item->endTime ? ( carbon::parse( $item->dateTime )->diffInMinutes( $item->endTime ) ) : null;
                }
                else
                {
                    $item->dateTime = null;
                    $item->endTime = null;
                    $item->duration = null;
                }

                // GV-3320: this startup's own slot within the panel, derived from
                // the admin's interval and the startup's position in the panel.
                $slot = \App\Services\ScreeningSlotService::slotForCompany($item, $companyId);
                $item->meeting_start = $slot['start'];
                $item->meeting_end = $slot['end'];
                $item->meeting_date = $slot['start'] ? $slot['start']->format('d-m-Y') : null;
                $item->meeting_time = \App\Services\ScreeningSlotService::formatRange($slot);

                return $item;
            });

            // GV-8357: `screenings` holds one row per judge per panel, so a
            // 3-judge panel produced 3 identical rows for the startup. Collapse
            // each panel into a single row whose `judges` collection carries
            // every judge on it; the panel keeps its own time slot and link.
            $myInitial = $myInitial->groupBy(function ($item) {
                return $item->step_id . '-' . $item->panel;
            })->map(function ($panelRows) {
                $row = $panelRows->first();

                $row->judges = $panelRows
                    ->pluck('judge')
                    ->filter()
                    ->unique('id')
                    ->map(function ($judge) {
                        $entry = new stdClass;
                        $entry->id = $judge->id;
                        $entry->name = $judge->name;
                        $entry->image_url = $judge->image_url;
                        return $entry;
                    })
                    ->values();

                return $row;
            })->values();
        }

        $start = Carbon::now()->startOfDay()->format('Y-m-d H:i:s');
        $end = Carbon::now()->addDays($days)->endOfDay()->format('Y-m-d H:i:s');

        $steps_q = "SELECT
                s.id,
                s.title,
                s.arr,
                s.stype,
                s.from,
                s.to,
                s.description,
                s.data,
                c.id AS cycle_id,
                c.step_type
            FROM
                steps s
            LEFT JOIN
                cycles c ON c.id = s.cycle_id
            WHERE
                s.cycle_id = $cycle_id
                AND s.cycle_id != 0
                AND s.cycle_id IS NOT null
            ORDER BY
                s.arr asc
        ";
        $now = time();
        $stepType = $company?->cycledata?->step_type;
        $steps = collect(DB::select($steps_q));
        $cycleSteps = $steps->pluck('id')->toArray();

        // array_search($company->step, $cycleSteps)
        $steps = $steps->map(function ($step) use ($l, $company, $stepData, $stepType, $cycle_id, $now) {
            $starting = Carbon::parse($step->from)->format('Y-m-d 00:00:00');
            $ending = Carbon::parse($step->to)->format('Y-m-d 23:59:59');

            $step->from = Carbon::parse($step->from)->format('d-m-Y');
            $step->to = Carbon::parse($step->to)->format('d-m-Y');
            $status_flag = 'not_started';
            $status = $l == 'ar' ? 'لم تبدأ' : 'Not started';
            $status_flag_tile = $l == 'ar' ? 'لم تبدأ' : 'Not started';
            $color = 'label-light';
            $textColor = 'light';
            if (!empty($step) && !empty($stepData))
            {
                if( $stepType == 'time' )
                {
                    if( $now > Carbon::parse($ending)->timestamp )
                    {
                        $status_flag = 'completed';
                        $status_flag_tile = $l == 'ar' ? 'انتهت' : 'Completed';
                        $status = 'Completed';
                        $color = 'label-success';
                        $textColor = 'success';
                    }
                    if( $now <= Carbon::parse($ending)->timestamp && $now >= Carbon::parse($starting)->timestamp )
                    {
                        $status_flag = 'ongoing';
                        $status_flag_tile = $l == 'ar' ? 'جاري' : 'Ongoing';
                        $status = 'Ongoing';
                        $color = 'label-primary';
                        $textColor = 'primary';
                    }
                    /* if ($stepData->arr > $step->arr) {
                        $status = $l == 'ar' ? 'انتهت' : 'Completed';
                        $color = 'label-success';
                        $textColor = 'success';
                    } elseif ($stepData->arr == $step->arr) {
                        $status = $l == 'ar' ? 'بدأت' : 'Started';
                        $color = 'label-primary';
                        $textColor = 'primary';
                    } */
                }
                if( $stepType == 'cycle' )
                {
                    if( $stepData->arr > $step->arr )
                    {
                        $status_flag = 'completed';
                        $status_flag_tile = $l == 'ar' ? 'انتهت' : 'Completed';
                        $status = 'Completed';
                        $color = 'label-success';
                        $textColor = 'success';
                    }
                    elseif( $stepData->arr == $step->arr )
                    {
                        $status_flag = 'ongoing';
                        $status_flag_tile = $l == 'ar' ? 'جاري' : 'Ongoing';
                        $status = 'Ongoing';
                        $color = 'label-primary';
                        $textColor = 'primary';
                    }
                }
            }
            
            $trType = $fLink = null;
            if ($company->approved) {
                $trType = 'my/' . $step->id;
            } else {
                $fLink = 'view/' . $cycle_id;
            }

            $step->status = $status;
            $step->status_flag = $status_flag;
            $step->status_flag_tile = $status_flag_tile;
            $step->color = $color;
            $step->textColor = $textColor;
            $step->trType = $trType;
            $step->fLink = $fLink;
            return $step;
        });
        
        $nextSessions_q = "SELECT
                mr.id,
                mr.mentor_id,
                mr.user_id,
                mr.comment,
                mr.approved,
                mr.rating,
                mr.join_url,
                mr.zoom_date,
                mr.period,
                mr.rate_comment,
                mr.done,
                mr.session_type,
                mr.organization_id,
                u.img_url AS `user_img_url`,
                u.first_name_en AS `user_first_name_en`,
                u.first_name_ar AS `user_first_name_ar`,
                u.last_name_en AS `user_last_name_en`,
                u.last_name_ar AS `user_last_name_ar`,
                u.title_en AS `user_title_en`,
                u.title_ar AS `user_title_ar`,
                c.id AS `company_id`,
                c.logo AS `company_logo`,
                c.name_en AS `company_name_en`,
                c.name_ar AS `company_name_ar`,
                m.first_name_en AS `mentor_first_name_en`,
                m.first_name_ar AS `mentor_first_name_ar`,
                m.last_name_en AS `mentor_last_name_en`,
                m.last_name_ar AS `mentor_last_name_ar`,
                m.img_url AS `mentor_img_url`,
                msr.rating AS `mentor_rating`,
                o.name_en AS `org_name_en`,
                o.name_ar AS `org_name_ar`,
                o.img_url AS `org_img_url`,
                o.google_map_url AS `org_google_map_url`,
                o.address AS `org_address`,
                cy.offline_location_address AS `offline_location_address`,
                cy.offline_location_map AS `offline_location_map`
            FROM
                mentorship_requests mr
            LEFT JOIN
                users m ON m.id = mr.mentor_id
            LEFT JOIN
                users u ON u.id = mr.user_id
            LEFT JOIN
                companies c ON c.id = u.company_id
            LEFT JOIN
                cycles cy ON cy.id = mr.cycle_id
            LEFT JOIN
               mentorship_requests msr ON msr.mentor_id = m.id
            LEFT JOIN
                organizations o ON o.id = mr.organization_id
            WHERE
                mr.user_id = $user->id
                AND (
                    mr.approved = 'Yes'
                    OR mr.approved IS null
                )
                AND mr.zoom_date between '$start' AND '$end'
            ORDER BY
                mr.zoom_date ASC
        ";
        $nextSessions = collect(DB::select($nextSessions_q));
        
        $nextSessions = $nextSessions->groupBy('id')->map(function ($groupedSession) use ($l) {
            $_session = $groupedSession->first();
            $session = new stdClass;
            $session->id = $_session->id;
            $session->mentor_id = $_session->mentor_id;
            $session->user_id = $_session->user_id;
            $session->comment = $_session->comment;
            $session->approved = $_session->approved;
            $session->rating = $_session->rating;
            $session->join_url = $_session->join_url;
            $session->zoom_date = $_session->zoom_date;
            $session->period = $_session->period;
            $session->rate_comment = $_session->rate_comment;
            $session->done = $_session->done;
            $session->session_type = $_session->session_type;
            $session->organization = $_session->organization_id;

            $user = new stdClass;
            $user->id = $_session->user_id;
            $user->image_url = User::image_handler($_session->user_img_url);
            $user->first_name = $_session->{'user_first_name_' . $l};
            $user->last_name = $_session->{'user_last_name_' . $l};
            $user->name = trim($user->first_name . ' ' . $user->last_name);
            $user->title = $_session->{'user_title_' . $l};

            $company = new stdClass;
            $company->id = $_session->company_id;
            $company->logo_url = Company::logo_handler($_session->company_logo);
            $company->name = $_session->{'company_name_' . $l};
            $user->company = $company;
            $session->user = $user;

            $mentor = new stdClass;
            $mentor->id = $_session->mentor_id;
            $mentor->first_name = $_session->{'mentor_first_name_' . $l};
            $mentor->last_name = $_session->{'mentor_last_name_' . $l};
            $mentor->name = trim($mentor->first_name . ' ' . $mentor->last_name);
            $mentor->image_url = User::image_handler($_session->mentor_img_url);
            $mentor->ratings = $groupedSession->sum('mentor_rating');
            $session->mentor = $mentor;

            $org = new stdClass;
            $org->id = $_session->organization_id;
            $org->name = $_session->{'org_name_' . $l};
            $org->image_url = Organization::image_handler($_session->org_img_url);
            $org->google_map_url = $_session->offline_location_map ? $_session->offline_location_map : $_session->org_google_map_url;
            $org->address = $_session->offline_location_address ? $_session->offline_location_address : $_session->org_address;
            $session->organization = $org;
            $session->meeting_status = mentorshipMeetingStatus($session->zoom_date, $session->period);
            return $session;
        })->values();

        $my_schedules_q = "SELECT
                s.id,
                s.user_id,
                s.type,
                s.days,
                s.dfrom,
                s.dto,
                s.tfrom,
                s.tto,
                s.notes,
                s.active,
                s.session_type,
                s.date
            FROM
                schedules s
            WHERE
                s.user_id = $user->id
        ";
        $my_schedules = collect(DB::select($my_schedules_q))->map(function ($schedule) {
            $schedule->active = $schedule->active == 1 || $schedule->active == true ? true : false;
            // $date_from = Carbon::parse($schedule->dfrom)->format('d-m-Y');
            // $date_to = Carbon::parse($schedule->dto)->format('d-m-Y');
            $schedule->t_from = substr($schedule->tfrom, 0, 5);
            $schedule->t_to = substr($schedule->tto, 0, 5);
            $schedule->notes ? $schedule->notes : '...';

            if (trim( strtolower( $schedule->type ) ) === 'weekly') {
                return [
                    'id' => $schedule->id,
                    'weekday' => $schedule->days,
                    'start_time' => $schedule->t_from,
                    'end_time' => $schedule->t_to,
                    'session_type' => $schedule->session_type,
                    'weekly' => true
                ];
            } else {
                return [
                    'id' => $schedule->id,
                    'date' => $schedule->date ?? $schedule->date_to ?? null,
                    'start_time' => $schedule->t_from,
                    'end_time' => $schedule->t_to,
                    'session_type' => $schedule->session_type,
                    'weekly' => false
                ];
            }
        });

        $currentTime = now();
        $my_schedules = $my_schedules->filter(function ($schedule) use ( $currentTime ) {
            if( $schedule['weekly'] == true ) { return true; }
            else
            {
                $end_at = Carbon::parse( $schedule['date'] . $schedule['end_time'] . ':00' );
                return $currentTime->lte( $end_at );
            }
        })->values();

        $setting = collect(DB::select("SELECT * FROM settings where `id` = 1 limit 1"))->first();
        
        if($user->hasAnyRole(['mentor'])){
            $cyclementor = $user->mentorCycles->pluck('id')->toArray();
            $cycle_idMentor = implode(',', $cyclementor);
        } else {
            $cycle_idMentor = [];
        }

        /* if( $cycle_id != 0 || $cycle_idMentor != 0 )
        {
            $surveyEligible = [];
            foreach( $steps as $step )
            {
                if( (optional($step)->stype == 3 || optional($step)->stype == 4) && (now() > Carbon::parse($step->to)->format('Y-m-d H:i:s')) )
                {
                    $surveyEligible[] = [
                        'triggered_on' => $step->stype == 3 ? 'mentorship' : 'training',
                        'triggered_at' => $step->id,
                        'triggered_by' => 'due'
                    ];
                }
            }

            if( $user->hasAnyRole(['mentor']) )
            {
                if( empty( $cycle_idMentor ) )
                {
                    $my_mentorship_requests_q = "SELECT
                            mr.id,
                            mr.period,
                            mr.zoom_date,
                            mr.created_at
                        FROM mentorship_requests mr
                        WHERE 1 = 0
                    ";
                }
                else
                {
                    $my_mentorship_requests_q = "SELECT
                            mr.id,
                            mr.period,
                            mr.zoom_date,
                            mr.created_at
                        FROM mentorship_requests mr
                        WHERE
                            mr.mentor_id = {$user->id}
                            AND mr.approved = 'Yes'
                            AND mr.cycle_id IN ($cycle_idMentor)
                    ";
                }
            }
            else
            {
                $my_mentorship_requests_q = "SELECT
                        mr.id,
                        mr.period,
                        mr.zoom_date,
                        mr.created_at
                    FROM
                        mentorship_requests mr
                    INNER JOIN attendance_training_offline ato
                        ON ato.mentorship_request_id = mr.id
                        AND ato.user_id = {$user->id}
                        AND ato.attendance = 1
                    WHERE
                        mr.user_id = {$user->id}
                        AND mr.approved = 'Yes'
                        AND mr.rating IS NOT NULL
                        AND mr.cycle_id = $cycle_id
                ";
            }

            $my_mentorship_requests = collect(DB::select($my_mentorship_requests_q))->map(function ($item) {
                $item->end_at = Carbon::parse($item->zoom_date)->addMinutes($item->period)->format('Y-m-d H:i:s');
                $item->added_at = $item->created_at;
                return $item;
            })->filter(function ($item) {
                return !empty($item->zoom_date) && !empty($item->period) && now() > $item->end_at;
            })->values();

            if( $user->hasAnyRole(['registered']) )
            {
                $my_trainings_q = "SELECT
                        ts.id,
                        ts.type,
                        ts.duration,
                        ts.datetime,
                        ts.created_at
                    FROM
                        training_sessions ts
                    INNER JOIN
                        steps s ON ts.step_id = s.id
                    INNER JOIN
                        attendance_training_offline ato ON ato.training_session_id = ts.id
                            AND ato.user_id = {$user->id}
                            AND ato.attendance = 1
                    WHERE
                        s.cycle_id = $cycle_id;
                ";
                $my_trainings = collect(DB::select($my_trainings_q))->map(function ($item) {
                    // Calculate end_at based on session type
                    $item->end_at = null;
                    $item->added_at = $item->created_at;
                    if( $item->type == 'sessions' && !empty($item->datetime) && !empty($item->duration) ) {
                        $sessionStart = Carbon::createFromTimestamp($item->datetime);
                        $item->end_at = $sessionStart->copy()->addMinutes($item->duration)->format('Y-m-d H:i:s');
                    } elseif( in_array($item->type, ['online', 'offline']) && !empty($item->datetime) ) {
                        $item->end_at = FormatDate($item->datetime, 'Y-m-d H:i:s');
                    }
                    return $item;
                })->filter(function ($item) {
                    // Same logic as mentorship: must have end_at and session must have ended
                    return !empty($item->end_at) && now() > $item->end_at;
                })->values();

                $my_quizzes_q = "SELECT
                        q.id,
                        q.end,
                        q.attempts,
                        q.passing_criteria AS criteria,
                        qr.id AS attempt_id,
                        qr.score AS attempt_score
                    FROM
                        quizzes q
                    LEFT JOIN
                        quiz_results qr ON qr.quiz_id = q.id AND qr.user_id = ?
                    WHERE
                        q.status = 'published'
                ";
                if($organization_id != null){
                    $my_quizzes_q .= " AND q.organization_id = $organization_id ";
                }
                $my_quizzes = collect(DB::select($my_quizzes_q, [$user->id]))->groupBy('id')->map(function ($items) {
                    $item = $items->first();
                    $quiz = new stdClass;
                    $quiz->id = $item->id;
                    $quiz->criteria = $item->criteria;
                    $quiz->end = Carbon::parse($item->end)->format('Y-m-d H:i:s');
                    $quiz->trials = $item->attempts;
                    $quiz->attempts = $items->map(function ($result) {
                        $attempt = new stdClass;
                        $attempt->id = $result->attempt_id;
                        $attempt->score = $result->attempt_score;
                        return $attempt;
                    });
                    return $quiz;
                })->values()->filter(function ($quiz) {
                    $attempts = $quiz->attempts;
                    if( $attempts->count() > 0 )
                    {
                        if (
                            $attempts->where('score', '>=', $quiz->criteria)->count() >= 1
                            || $attempts->where('score', '<', $quiz->criteria)->count() >= $quiz->trials
                            || now()->format('Y-m-d H:i:s') > $quiz->end
                        ) {
                            return true;
                        } else {
                            return false;
                        }
                    } else {
                        return false;
                    }
                })->values();
            }

            foreach( $my_mentorship_requests as $mentorship )
            {
                $surveyEligible[] = [
                    'triggered_on' => 'mentorship',
                    'triggered_at' => $mentorship->id,
                    'triggered_by' => 'session',
                    'added_at' => $mentorship->added_at,
                    'end_at' => $mentorship->end_at
                ];
            }
            
            if( $user->hasAnyRole(['registered']) )
            {
                foreach( $my_trainings as $session )
                {
                    $surveyEligible[] = [
                        'triggered_on' => 'training',
                        'triggered_at' => $session->id,
                        'triggered_by' => 'session',
                        'added_at' => $session->added_at,
                        'end_at' => $session->end_at
                    ];
                }

                foreach( $my_quizzes as $quiz )
                {
                    $surveyEligible[] = [
                        'triggered_on' => 'training',
                        'triggered_at' => $quiz->id,
                        'triggered_by' => 'quiz'
                    ];
                }
            }

            $surveysToAdd = [];
            if( $user->hasAnyRole(['mentor']) )
            {
                $surveys = SurveyScopes::whereIn('cycle_id', $user->mentorCycles->pluck('id'))
                    ->where(function($query) { $query->whereIn('type', ['mentor', 'both'])->orWhereNull('type'); })
                    ->with(['myResponses'])
                ->get()->unique('survey_id');
            }
            else
            {
                $surveys = SurveyScopes::where('cycle_id', $cycle_id)
                    ->where(function($query) { $query->whereIn('type', ['startup', 'both'])->orWhereNull('type'); })
                    ->with(['myResponses'])
                ->get()->unique('survey_id');
            }

            // Query responses by survey_id (not scope_id) to prevent duplicates across cycles
            $responsesBySurvey = SurveyResponses::where('user_id', $user->id)
                ->whereIn('survey_id', $surveys->pluck('survey_id')->unique())
            ->get()->groupBy('survey_id');

            foreach( $surveys as $survey_scope )
            {
                $my_responses = $responsesBySurvey->get($survey_scope->survey_id, collect());

                // Initialize fresh collection for each survey to avoid overwriting issues
                $mentorshipFiltered = collect($surveyEligible)->where('triggered_on', 'mentorship');
                $trainingFiltered = collect($surveyEligible)->where('triggered_on', 'training');

                if( $survey_scope->scope == 'mentorship' && $mentorshipFiltered->isNotEmpty() )
                {
                    $due = $survey_scope->trigger_by_due;
                    $session = $survey_scope->trigger_by_session;
                    $triggers = [];
                    if( $due == true ) { $triggers[] = 'due'; }
                    if( $session == true ) { $triggers[] = 'session'; }

                    $triggers = array_unique($triggers);
                    if( count( $triggers ) == 0)  {
                        $mentorshipFiltered = $mentorshipFiltered->where('triggered_at', null);
                    } else {
                        $mentorshipFiltered = $mentorshipFiltered->whereIn('triggered_by', $triggers);
                    }

                    // Filter sessions: must be added after survey creation AND session must have ended
                    $surveyCreatedAt = $survey_scope->created_at;
                    $mentorshipFiltered = $mentorshipFiltered->filter(function ($item) use ($surveyCreatedAt) {
                        // Session must be added to system after survey scope creation
                        if( !empty($item['added_at']) && Carbon::parse($item['added_at']) <= Carbon::parse($surveyCreatedAt) ) { return false; }
                        // Session must have ended
                        if( !empty($item['end_at']) && now() <= Carbon::parse($item['end_at']) ) { return false; }
                        return true;
                    })->values();

                    foreach( $mentorshipFiltered as $conditions )
                    {
                        $checkResponse = $my_responses->where('triggered_on', $conditions['triggered_on'])->where('triggered_at', $conditions['triggered_at'])->where('triggered_by', $conditions['triggered_by'])->first();
                        if( empty( $checkResponse ) )
                        {
                            $surveysToAdd[] = [
                                'user_id' => $user->id,
                                'survey_id' => $survey_scope->survey_id,
                                'cycle_id' => $survey_scope->cycle_id,
                                'scope_id' => $survey_scope->id,
                                'triggered_on' => $conditions['triggered_on'],
                                'triggered_at' => $conditions['triggered_at'],
                                'triggered_by' => $conditions['triggered_by'],
                                'responses' => Null
                            ];
                        }
                    }
                }
                elseif( $survey_scope->scope == 'training' && $trainingFiltered->isNotEmpty() )
                {
                    $due = $survey_scope->trigger_by_due;
                    $quiz = $survey_scope->trigger_by_quiz;
                    $session = $survey_scope->trigger_by_session;
                    $triggers = [];
                    if( $due == true ) { $triggers[] = 'due'; }
                    if( $quiz == true ) { $triggers[] = 'quiz'; }
                    if( $session == true ) { $triggers[] = 'session'; }
                    $triggers = array_unique($triggers);
                    if( count( $triggers ) == 0 ) {
                        $trainingFiltered = $trainingFiltered->where('triggered_at', null);
                    } else {
                        $trainingFiltered = $trainingFiltered->whereIn('triggered_by', $triggers);
                    }
                    // Filter sessions: must be added after survey creation AND session must have ended
                    $surveyCreatedAt = $survey_scope->created_at;
                    $trainingFiltered = $trainingFiltered->filter(function ($item) use ($surveyCreatedAt) {
                        // Session must be added to system after survey scope creation
                        if( !empty($item['added_at']) && Carbon::parse($item['added_at']) <= Carbon::parse($surveyCreatedAt) ) { return false; }
                        // Session must have ended
                        if( !empty($item['end_at']) && now() <= Carbon::parse($item['end_at']) ) { return false; }
                        return true;
                    })->values();
                    foreach( $trainingFiltered as $conditions )
                    {
                        $checkResponse = $my_responses->where('triggered_on', $conditions['triggered_on'])->where('triggered_at', $conditions['triggered_at'])->where('triggered_by', $conditions['triggered_by'])->first();
                        if( empty($checkResponse) )
                        {
                            $surveysToAdd[] = [
                                'user_id' => $user->id,
                                'survey_id' => $survey_scope->survey_id,
                                'cycle_id' => $survey_scope->cycle_id,
                                'scope_id' => $survey_scope->id,
                                'triggered_on' => $conditions['triggered_on'],
                                'triggered_at' => $conditions['triggered_at'],
                                'triggered_by' => $conditions['triggered_by'],
                                'responses' => Null
                            ];
                        }
                    }
                }
            }

            if( !empty( $surveysToAdd ) )
            {
                $surveysToAdd = collect($surveysToAdd)->unique(function ($item) {
                    return $item['user_id'].'-'.$item['survey_id'].'-'.$item['triggered_on'].'-'.$item['triggered_at'].'-'.$item['triggered_by'];
                })->values()->toArray();
                SurveyResponses::insert($surveysToAdd);
            }
        } */
        $available_surveys = SurveyResponses::where('user_id', $user->id)->where('cycle_id', $cycle_id)->whereNull('responses')->get();
        
        $available_surveys = $available_surveys->map(function ($item) {
            $middleware = collect([]);
            if ($item->triggered_on == 'mentorship') {
                if ($item->triggered_by == 'session') {
                    $middleware = $item->mentorship_session;
                }
                if ($item->triggered_by == 'due') {
                    $middleware = $item->mentorship_step;
                }
            } elseif ($item->triggered_on == 'training') {
                if ($item->triggered_by == 'session') {
                    $middleware = $item->training_session;
                }
                if ($item->triggered_by == 'due') {
                    $middleware = $item->training_step;
                }
                if ($item->triggered_by == 'quiz') {
                    $middleware = $item->training_quiz;
                }
            }
            $item->middleware = $middleware;
            return $item;
        })->filter(function ($item) {
            $middleware = $item->middleware;
            if (is_null($middleware) || empty($middleware)) {
                return false;
            } elseif (!$middleware->count()) {
                return false;
            }

            // الحصول على تاريخ إنشاء الـ survey scope
            $surveyScope = $item->survey_scope;
            $surveyCreatedAt = $surveyScope ? $surveyScope->created_at : null;

            // فحص التوقيت للـ mentorship sessions
            if ($item->triggered_on == 'mentorship' && $item->triggered_by == 'session') {
                if (!empty($middleware->zoom_date) && !empty($middleware->period)) {
                    // 1. فحص أن الـ request تم بعد إنشاء الـ survey
                    if ($surveyCreatedAt && Carbon::parse($middleware->zoom_date) <= Carbon::parse($surveyCreatedAt)) {
                        return false; // الـ request قديم وتم قبل إنشاء الـ survey
                    }

                    // 2. فحص أن الجلسة انتهت
                    $endAt = Carbon::parse($middleware->zoom_date)->addMinutes($middleware->period);
                    if (now() <= $endAt) {
                        return false; // الجلسة لم تنتهي بعد
                    }
                }
            }

            // فحص التوقيت للـ training sessions (للـ startup فقط)
            if ($item->triggered_on == 'training' && $item->triggered_by == 'session') {
                if ($middleware->type == 'sessions' && !empty($middleware->datetime) && !empty($middleware->duration)) {
                    // 1. فحص أن الـ session تم بعد إنشاء الـ survey
                    $sessionDate = Carbon::createFromTimestamp($middleware->datetime);
                    if ($surveyCreatedAt && $sessionDate <= Carbon::parse($surveyCreatedAt)) {
                        return false; // الـ session قديم وتم قبل إنشاء الـ survey
                    }

                    // 2. فحص أن الجلسة انتهت
                    $endAt = $sessionDate->copy()->addMinutes($middleware->duration);
                    if (now() <= $endAt) {
                        return false; // الجلسة لم تنتهي بعد
                    }
                }
            }

            return true;
        })->values();

        $data['my_schedules'] = $my_schedules;
        $data['upcoming_sessions'] = $upcoming_sessions;
        $data['cType'] = $cType;
        $data['myFinal'] = $myFinal;
        $data['myInitial'] = $myInitial;
        $data['sessions'] = $sessions;
        $data['cycle_id'] = $cycle_id;
        $data['days'] = $days;
        $data['mentors'] = $mentors;
        $data['nextSessions'] = $nextSessions;
        $data['steps'] = $steps;
        $data['company'] = $company;
        $data['cycleData'] = $cycleData;
        $data['organization'] = $organization;
        $data['available_surveys'] = $available_surveys;
        $data['latest_services'] = $latest_services;
        $data['latest_events'] = $latest_events;
        $data['workingCycles'] = $workingCycles;
        $data['setting'] = $setting;
        $data['countCards'] =[
            [
                'color' => 'success',
                'href' => '/dashboard/users?r=organizationmanager',
                'icon' => 'ti ti-user-share',
                'text' => 'Managers',
                'permissions' => ['users.index', 'dashboard.widgets_cards_managers_view'],
                'count' => $managers->count(),
                'isRating' => false,
            ]
        ];
        $data['countCards'][] = [
            'color' => 'warning',
            'href' => '/dashboard/organizations',
            'icon' => 'ti-businessplan',
            'text' => 'Organizations',
            'permissions' => ['organizations.index', 'dashboard.widgets_cards_organizations_view'],
            'count' => $organizations->count(),
            'isRating' => false,
        ];

        if( $user->hasRole('Registered') )
        {
            if( $user->company?->cycle != null )
            {
                $data['countCards'][] = [
                    'color' => 'secondary',
                    'href' => '/dashboard/requests/all',
                    'icon' => 'ti-speakerphone',
                    'text' => 'Services Requests',
                    'permissions' => ['service_requests.list_all', 'dashboard.widgets_cards_service_requests_view'],
                    'count' => $services_requests->count(),
                    'isRating' => false,
                ];
                $data['countCards'][] = [
                    'color' => 'success',
                    'href' => '/dashboard/directory',
                    'icon' => 'ti ti-briefcase',
                    'text' => 'Services',
                    'permissions' => ['users.directory_view', 'dashboard.widgets_cards_services_view'],
                    'count' => $services->count(),
                    'isRating' => false,
                ];
                $data['countCards'][] = [
                    'color' => 'primary',
                    'href' => '/dashboard/requests/mine',
                    'icon' => 'ti ti-speakerphone',
                    'text' => 'My Requests',
                    'permissions' => ['service_requests.list_mine', 'dashboard.widgets_cards_my_requests_view'],
                    'count' => $my_requests->count(),
                    'isRating' => false,
                ];
                $data['countCards'][] = [
                    'color' => 'secondary',
                    'href' => '/dashboard/mentors',
                    'icon' => 'ti ti-school',
                    'text' => 'Mentors',
                    'permissions' => ['mentors.list', 'dashboard.widgets_cards_my_mentors_view'],
                    'count' => $my_mentors->count(),
                    'isRating' => false,
                ];
                /* $data['countCards'][] = [
                    'color' => 'danger',
                    'href' => '#',
                    'icon' => 'ti ti-star',
                    'text' => 'Rate',
                    'permissions' => ['dashboard.widgets_cards_rate_view'],
                    'count' => $my_rating,
                    'isRating' => true, // Only this card has the isRating key
                ]; */
            }
        }
        else
        {
            $data['countCards'][] = [
                'color' => 'secondary',
                'href' => '/dashboard/requests/all',
                'icon' => 'ti-speakerphone',
                'text' => 'Services Requests',
                'permissions' => ['service_requests.list_all', 'dashboard.widgets_cards_service_requests_view'],
                'count' => $services_requests->count(),
                'isRating' => false,
            ];
            // $data['countCards'][] = [
            //     'color' => 'success',
            //     'href' => '/dashboard/directory',
            //     'icon' => 'ti ti-briefcase',
            //     'text' => 'Services',
            //     'permissions' => ['users.directory_view', 'dashboard.widgets_cards_services_view'],
            //     'count' => $services->count(),
            //     'isRating' => false,
            // ];
            $data['countCards'][] = [
                'color' => 'primary',
                'href' => '/dashboard/requests/mine',
                'icon' => 'ti ti-speakerphone',
                'text' => 'My Requests',
                'permissions' => ['service_requests.list_mine', 'dashboard.widgets_cards_my_requests_view'],
                'count' => $my_requests->count(),
                'isRating' => false,
            ];
            $data['countCards'][] = [
                'color' => 'secondary',
                'href' => '/dashboard/mentors',
                'icon' => 'ti ti-school',
                'text' => 'Mentors',
                'permissions' => ['mentors.list', 'dashboard.widgets_cards_my_mentors_view'],
                'count' => $my_mentors->count(),
                'isRating' => false,
            ];
            /* $data['countCards'][] = [
                'color' => 'danger',
                'href' => '#',
                'icon' => 'ti ti-star',
                'text' => 'Rate',
                'permissions' => ['dashboard.widgets_cards_rate_view'],
                'count' => $my_rating,
                'isRating' => true, // Only this card has the isRating key
            ]; */
        }
        $data['countCards'][] = [
            'color' => 'success',
            'href' => '/dashboard/inbox',
            'icon' => 'ti-message',
            'text' => 'New messages',
            'permissions' => ['inbox', 'dashboard.widgets.cards.new_messages.view'],
            'count' => $messenger->count(),
            'isRating' => false,
        ];
        /* $data['countCards'][] = [
            'color' => 'danger',
            'href' => '/dashboard/mymsg',
            'icon' => 'ti-network',
            'text' => 'Contact us',
            'permissions' => ['messages.view', 'dashboard.widgets_cards_contact_us_view'],
            'count' => $messages->count(),
            'isRating' => false,
        ];*/
        $data['countCards'][] = [
            'color' => 'danger',
            'href' => '/dashboard/programlist',
            'icon' => 'ti-circles',
            'text' => 'Cycles',
            'permissions' => ['cycles.list', 'dashboard.widgets_cards_programs_view'],
            'count' => $cycles->count(),
            'isRating' => false,
        ];
            $data['countCards'][] = [
            'color' => 'success',
            'href' => '/dashboard/mentorship/sessions?show=all&done=yes',
            'icon' => 'ti-calendar-check',
            'text' => 'completed_mentorship_sessions',
            'permissions' => ['mentorships.sessions_list'],
            'count' => $completed_mentorship_sessions->count(),
            'isRating' => false,
        ];
        $data['countCards'][] = [
            'color' => 'info',
            'href' => '/dashboard/companies?status=alumni',
            'icon' => 'ti-building',
            'text' => 'Alumni_Startups',
            'permissions' => ['users.view_companies', 'dashboard.widgets_cards_startups_view'],
            'count' => $alumni_company->count(),
            'isRating' => false,
        ];
        $data['countCards'][] = [
            'color' => 'info',
            'href' => '/dashboard/companies?status=incubated',
            'icon' => 'ti-building',
            'text' => 'Incubated_Startups',
            'permissions' => ['users.view_companies', 'dashboard.widgets_cards_startups_view'],
            'count' => $incubators_company->count(),
            'isRating' => false,
        ];
        $data['countCards'][] = [
            'color' => 'info',
            'href' => '/dashboard/companies?status=applied',
            'icon' => 'ti-building',
            'text' => 'Applied_Startups',
            'permissions' => ['users.view_companies', 'dashboard.widgets_cards_startups_view'],
            'count' => $applied_company_count,
            'isRating' => false,
        ];
        $data['countCards'][] = [
            'color' => 'info',
            'href' => '/dashboard/companies',
            'icon' => 'ti-building',
            'text' => 'Founders',
            'permissions' => ['users.view_companies', 'dashboard.widgets_cards_startups_view'],
            'count' => $companies->count(),
            'isRating' => false,
        ];
        $data['countCards'][] = [
            'color' => 'danger',
            'href' => '/dashboard/mentors',
            'icon' => 'ti-school',
            'text' => 'Mentors',
            'permissions' => ['users.index', 'dashboard.widgets_cards_mentors_view'],
            'count' => $mentors->count(),
            'isRating' => false,
        ];
        $data['countCards'][] = [
            'color' => 'secondary',
            'href' => '/dashboard/mentorship/sessions',
            'icon' => 'ti-brand-my-oppo',
            'text' => 'Mentorship requests',
            'permissions' => ['mentorships.sessions_list', 'dashboard.widgets_cards_mentorship_requests_view'],
            'count' => $mentorship_requests->count(),
            'isRating' => false,
        ];
        $data['countCards'][] = [
            'color' => 'success',
            'href' => '/dashboard/mentorship/sessions',
            'icon' => 'ti-calendar-event',
            'text' => 'Mentorship requests Duration',
            'permissions' => ['mentorships.sessions_list', 'dashboard.widgets_cards_mentorship_sessions_durations_view'],
            'count' => number_format( ( $mentorship_requests->sum('period') / 60 ), 2 ),
            'isRating' => false,
        ];
        $data['countCards'][] = [
            'color' => 'primary',
            'href' => route('users.judger'),
            'icon' => 'ti-certificate',
            'text' => 'Judgers',
            'permissions' => ['users.index', 'dashboard.widgets_cards_judgers_view'],
            'count' => $judgers->count(),
            'isRating' => false,
        ];
        $data['countCards'][] = [
            'color' => 'warning',
            'href' => '/dashboard/training/sessions',
            'icon' => 'ti-camera-question',
            'text' => 'Training sessions',
            'permissions' => ['trainings.sessions_list', 'dashboard.widgets_cards_training_sessions_view'],
            'count' => $training_sessions->count(),
            'isRating' => false,
        ];
        $data['countCards'][] = [
            'color' => 'info',
            'href' => '/dashboard/training/sessions',
            'icon' => 'ti-alarm',
            'text' => 'Training sessions Duration',
            'permissions' => ['trainings.sessions_list', 'dashboard.widgets_cards_training_sessions_durations_view'],
            'count' => number_format($training_sessions->sum('duration') / 60, 2),
            'isRating' => false,
        ];
        $data['countCards'][] = [
            'color' => 'secondary',
            'href' => '#',
            'icon' => 'ti ti-chalkboard',
            'text' => 'First screening',
            'permissions' => ['dashboard.widgets_cards_filtration_view'],
            // Count startups currently in initial screening (not the number of
            // steps). Mirrors the Final screening card, which counts companies.
            // An initial step the judge owns but with no startup currently sitting
            // at it contributes 0.
            'count' => $filtration->map(function ($step) { return $step->companies; })->flatten()->count(),
            'isRating' => false,
        ];
        $data['countCards'][] = [
            'color' => 'warning',
            'href' => '#',
            'icon' => 'ti ti-calendar',
            'text' => 'Final screening',
            'permissions' => ['dashboard.widgets_cards_evaluation_view'],
            'count' => $evaluation->map(function($e) { return $e->companies; })->flatten()->count(),
            'isRating' => false,
        ];
        $data['countCards'][] = [
            'color' => 'secondary',
            'href' => '#',
            'icon' => 'ti ti-chalkboard',
            'text' => 'Coming sessions',
            'permissions' => ['dashboard.widgets_cards_coming_sessions_view'],
            'count' => $upcoming_sessions->count(),
            'isRating' => false,
        ];
        $data['countCards'][] = [
            'color' => 'warning',
            'href' => '#',
            'icon' => 'ti ti-calendar',
            'text' => 'Cycles',
            'permissions' => ['dashboard_cards.my_programs'],
            'count' => $my_cycles->count(),
            'isRating' => false,
        ];
        $data['countCards'][] = [
            'color' => 'info',
            'href' => '#',
            'icon' => 'ti ti-server',
            'text' => 'Company Attendance Rate',
            'permissions' => ['dashboard.widgets_cards_startup_attendance_rate_view'],
            'count' => preg_match('/^\d+(\.\d+)?%$/', $attends_rate) ? number_format((float)rtrim($attends_rate, '%'), 1) . '%' : $attends_rate,
            'isRating' => false,
        ];
        
        $data['countCards'][] = [
            'color' => 'warning',
            'href' => '#',
            'icon' => 'ti ti-select',
            'text' => 'withoutTasks',
            'permissions' => ['dashboard.widgets_cards_sessions_without_tasks_view'],
            'count' => $sessionWithoutTasks->count(),
            'isRating' => false,
        ];
        $data['countCards'][] = [
            'color' => 'success',
            'href' => '#',
            'icon' => 'ti ti-check',
            'text' => 'completedTasks',
            'permissions' => ['dashboard.widgets_cards_completed_tasks_view'],
            'count' => $completedTasks->count(),
            'isRating' => false,
        ];
        $data['countCards'][] = [
            'color' => 'info',
            'href' => '#',
            'icon' => 'ti ti-archive',
            'text' => 'nonCompletedTasks',
            'permissions' => ['dashboard.widgets_cards_non_completed_tasks_view'],
            'count' => $nonCompletedTasks->count(),
            'isRating' => false,
        ];
       
            
        // start of graphs queries ----
        $country_data_q = "SELECT
                c.name_en AS `country_name_en`,
                c.name_ar AS `country_name_ar`,
                count(u.id) AS total
            FROM
                users u
            INNER JOIN 
                countries c ON u.country_id = c.id
        ";
        if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
            $country_data_q .= " WHERE u.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")";
        }
        $country_data_q .= " GROUP BY
                c.name_en,
                c.name_ar";

        $country_data = collect(DB::select($country_data_q))->map(function ($item) {
            return [
                'label_en' => $item->country_name_en,
                'label_ar' => $item->country_name_ar,
                'count' => $item->total,
            ];
        });

        $conditions = '';
        if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
            $conditions = " WHERE cy.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")";
        }
        $sector_data_q = "SELECT
                s.id AS `id`,
                s.name_en AS `sector_name_en`,
                s.name_ar AS `sector_name_ar`,
                -- count(c.id) AS total
                cr.id AS `cr_id`,
                cr.company_id AS `company_id`,
                cr.sector_id AS `sector_id`,
                cr.industry_id AS `industry_id`
            FROM
                sectors s
            LEFT JOIN
                company_relations cr ON cr.sector_id = s.id
            LEFT JOIN
                companies c ON cr.company_id = c.id
            LEFT JOIN
                cycles cy on c.cycle = cy.id
            $conditions
            -- GROUP BY
                -- s.name_en,
                -- s.name_ar
        ";
        /* $sector_data = collect(DB::select($sector_data_q))->map(function ($item) {
            return [
                'label_en' => $item->sector_name_en,
                'label_ar' => $item->sector_name_ar,
                'count' => $item->total,
            ];
        }); */
        // properly grouping data
        $sector_data = collect(DB::select($sector_data_q));
        $sector_data = $sector_data->filter(function($item) {
            return !is_null( $item->cr_id );
        })->values()->groupBy('id')->map(function($items) {
            $base = $items->first();
            $sector_data = new stdClass;
            $sector_data->label_en = $base->sector_name_en;
            $sector_data->label_ar = $base->sector_name_ar;
            $sector_data->count = $items->groupBy('company_id')->count();
            return $sector_data;
        })->values();
        
        $sector_data = $sector_data->sortBy('count')->reverse()->values();

        if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
            $conditions = " AND u.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")";
        }
        $project_types_q = "SELECT
                c.business_type,
                count(c.id) as total
            FROM
                companies c
            INNER JOIN
                cycles cy on c.cycle = cy.id
            LEFT JOIN
                users u on cy.organization_id = u.organization_id
            WHERE
                business_type is not null
                $conditions
            GROUP BY
                c.business_type
        ";

        $project_types = collect(DB::select($project_types_q))->map(function ($item) {
            return [
                'label' => $item->business_type,
                'count' => $item->total,
            ];
        });

        if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
            $conditions = " AND cy.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")";
        }
        $developmentStages = collect(DB::select(
            "SELECT 
                c.development_stage, 
                COUNT(*) AS count 
            FROM
                companies c
            INNER JOIN
                cycles cy ON c.cycle = cy.id
            WHERE
                c.development_stage IS NOT NULL 
            $conditions
            GROUP BY
                c.development_stage"
        ));
        $ids = $developmentStages->pluck('development_stage')->filter(function ($value) {
            return is_numeric($value);
        })->all();
        $stages = collect();
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stages = collect(DB::select("SELECT id, name_en, name_ar FROM stages WHERE id IN ($placeholders)", $ids))->keyBy('id');
        }

        $development_stages = $developmentStages->map(function ($item) use ($stages) {
            if (is_numeric($item->development_stage)) {
                $stage = $stages->get($item->development_stage);
                return [
                    'label_en' => $stage->name_en ?? null,
                    'label_ar' => $stage->name_ar ?? null,
                    'count' => $item->count,
                ];
            } else {
                return [
                    'label_en' => __('admin.' . $item->development_stage, [], 'en'),
                    'label_ar' => __('admin.' . $item->development_stage, [], 'ar'),
                    'count' => $item->count,
                ];
            }
        });

        $target_market_data_q = "SELECT
                c.name_en AS `country_name_en`,
                c.name_ar AS `country_name_ar`,
                COUNT(com.id) AS company_count
            FROM
                countries c
            LEFT JOIN
                companies com ON JSON_CONTAINS(
                    com.targeted_market,
                    CONCAT('\"', c.id, '\"'),
                    \"$\"
                ) = 1
            INNER JOIN
                cycles cy ON com.cycle = cy.id
            LEFT JOIN
                users u ON cy.organization_id = u.organization_id
            GROUP BY
                c.id,
                c.name_en,
                c.name_ar
            ORDER BY
                company_count DESC
        ";

        $target_market_data = collect(DB::select($target_market_data_q))->map(function ($item) {
            return [
                'label_en' => $item->country_name_en,
                'label_ar' => $item->country_name_ar,
                'count' => $item->company_count,
            ];
        });

        if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
            $conditions = " WHERE u.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")";
        }
        $project_status_q = "SELECT
                c.title AS `cycle_title`,
                count(ts.program_id) AS `total_programs`,
                c.start AS `cycle_start_date`,
                c.end AS `cycle_end_date`
            FROM
                time_sheets ts
            INNER JOIN
                cycles c ON ts.program_id = c.id
            LEFT JOIN
                users u ON c.organization_id = u.organization_id
            $conditions
            GROUP BY
                c.title,
                c.start,
                c.end
        ";

        $project_status = collect(DB::select($project_status_q))->map(function ($item) {
            return [
                'cycle_title' => $item->cycle_title,
                'total_programs' => $item->total_programs,
                'cycle_start_date' => $item->cycle_start_date,
                'cycle_end_date' => $item->cycle_end_date,
            ];
        });

        if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
            $conditions = " AND u.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")";
        }
        $company_data_q = "SELECT
                c.name_en AS `company_name_en`,
                c.name_ar AS `company_name_ar`,
                c.team_num,
                c.founder
            FROM
                companies c
            INNER JOIN
                cycles cy on c.cycle = cy.id
            LEFT JOIN
                users u on cy.organization_id = u.organization_id
            WHERE
                c.deleted_at IS NULL
                $conditions
        ";
        $company_data = collect(DB::select($company_data_q));
        $total_jobs = $company_data->whereNotNull('team_num')->values();
        $total_jobs = $total_jobs->map(function ($company) {
            return [
                'company_name_en' => $company->company_name_en,
                'company_name_ar' => $company->company_name_ar,
                'team_members' => $company->team_num,
            ];
        });
        $total_jobs_created = [
            'total' => $total_jobs->count('team_num'),
            'companies' => $total_jobs,
        ];

        $founders = $company_data->whereNotNull('founder')->values();
        $founders = $founders->map(function ($company) {
            return [
                'company_name_en' => $company->company_name_en,
                'company_name_ar' => $company->company_name_ar,
                'team_members' => $company->founder,
            ];
        });
        $total_founders = [
            'total' => $founders->count(),
            'companies' => $founders,
        ];

        if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
            $conditions = " AND cy.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")";
        }
        $company_sdg_q = "SELECT
                c.Sustainable_development
            FROM
                companies c
            LEFT JOIN
                cycles cy ON c.cycle = cy.id $conditions
            WHERE
                c.Sustainable_development IS NOT NULL
        ";
        $sdg_data = collect(DB::select($company_sdg_q))->flatMap(function ($item) {
            $sdgs = json_decode($item->Sustainable_development, true);
            return is_array($sdgs) ? $sdgs : [];
        })->countBy()->map(function ($count, $sdg) {
            return [
                'sdg_en' => __('admin.' . $sdg, [], 'en'),
                'sdg_ar' => __('admin.' . $sdg, [], 'ar'),
                'total' => $count,
            ];
        })->values();

        if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
            $conditions = " AND u.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")";
        }
        $sector_distribution_q = "SELECT
            s.id AS sector_id,
            s.name_en,
            s.name_ar,
            count(DISTINCT c.id) AS total
        FROM
            companies c
        LEFT JOIN
            cycles cy ON c.cycle = cy.id
        INNER JOIN
            company_relations cr ON cr.company_id = c.id
        INNER JOIN
            sectors s ON cr.sector_id = s.id
        LEFT JOIN 
            users u ON cy.organization_id = u.organization_id
        WHERE
             c.deleted_at IS NULL
             $conditions
        GROUP BY
             s.id, s.name_en, s.name_ar
        ORDER BY
             total DESC
        ";
        $sector_distribution = collect(DB::select($sector_distribution_q))->map(function ($item) {
            return [
                'sector_en' => $item->name_en ?? __('admin.undefined', [], 'en'),
                'sector_ar' => $item->name_ar ?? __('admin.undefined', [], 'ar'),
                'count'     => $item->total,
            ];
        });

        $cycles_labels = $cycles->pluck('title')->toArray();
        $cycles_startups_count = $cycles->map(function ($key) {
            return $key->companies->count();
        });

        $users_gov_q = "SELECT
                s.name_en AS `state_en`,
                s.name_ar AS `state_ar`,
                count(*) AS total
            FROM
                users u
            INNER JOIN
                states s ON u.state_id = s.id
        ";
        if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
            $users_gov_q .= " WHERE u.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")";
        }
        $users_gov_q .= " GROUP BY
                        s.name_en,
                        s.name_ar";

        $users_gov = collect(DB::select($users_gov_q));

        $users_gender_q = "SELECT
                u.gender,
                count(*) as total
            FROM
                users u
        ";
        if ($has_org && !empty($organizations_ids) && !$user->hasRole('admin')) {
            $users_gender_q .= " WHERE u.organization_id IN (" . implode(',', array_map('intval', $organizations_ids)) . ")";
        }
        $users_gender_q .= " GROUP BY gender";
        $users_gender = collect(DB::select($users_gender_q))
            ->filter(fn($item) => in_array(strtolower($item->gender ?? ''), ['male', 'female']))
            ->values()
            ->map(function ($item) {
                $item->gender = strtolower($item->gender);
                return $item;
            })
            ->keyBy('gender');

        $users_gender = collect([
            (object) ['gender' => 'female', 'total' => $users_gender->get('female')->total ?? 0],
            (object) ['gender' => 'male', 'total' => $users_gender->get('male')->total ?? 0],
        ]);

        $data['countries'] = $country_data;
        $data['sector_data'] = $sector_data;
        $data['project_types'] = $project_types;
        $data['development_stages'] = $development_stages;
        $data['target_market_data'] = $target_market_data;
        $data['project_status'] = $project_status;
        $data['company_data'] = $company_data;
        $data['total_jobs_created'] = $total_jobs_created;
        $data['total_founders'] = $total_founders;
        $data['sdg_data'] = $sdg_data;
        $data['sector_distribution'] = $sector_distribution;
        $data['cycles_labels'] = $cycles_labels;
        $data['cycles_startups_count'] = $cycles_startups_count;
        $data['users_gov'] = $users_gov;
        $data['users_gender'] = $users_gender;

        // end of graphs queries ---->

        $data['graphsCards'] = [
            [
                'div-class' => 'col-12 mt-6',
                'card-title' => __('admin.Cycle Startups Statistics'),
                'type' => 'div',
                'id' => 'horizontalBarChart',
                'permissions' => ['dashboard.widgets_graphs_startup_by_program_view']
            ],
            [
                'div-class' => 'col-12 mt-6',
                'card-title' => __('admin.Total_company_by_SDG'),
                'type' => 'canvas',
                'id' => 'sdgCompaniesChart',
                'permissions' => ['dashboard.widgets_graphs_startups_by_sdgs_view']
            ],
            [
                'div-class' => 'col-12 mt-6',
                'card-title' => __('admin.usersdetails'),
                'type' => 'canvas',
                'id' => 'verticalBarChart',
                'permissions' => ['dashboard.widgets_graphs_demographics_view']
            ],
            [
                'div-class' => 'col-12 mt-6',
                'card-title' => __('admin.Total_companies_by_sector'),
                'type' => 'canvas',
                'id' => 'companyBySectorChart',
                'permissions' => ['dashboard.widgets_graphs_top_startups_sectors_view']
            ],
            [
                'div-class' => 'col-md-6 col-12 mt-6',
                'card-title' => __('admin.totalusersmf'),
                'type' => 'div',
                'id' => 'pieChart',
                'permissions' => ['dashboard.widgets_graphs_male_female_ratio_view']
            ],
            [
                'div-class' => 'col-md-6 col-12 mt-6',
                'card-title' => __('admin.company_business_model'),
                'type' => 'div',
                'id' => 'projectsPieChart',
                'permissions' => ['dashboard.widgets_graphs_company_by_business_model_view']
            ],
            [
                'div-class' => 'col-md-6 col-12 mt-6',
                'card-title' => __('admin.Development_Stage'),
                'type' => 'canvas',
                'id' => 'devStageStackedBarChart',
                'permissions' => ['dashboard.widgets_graphs_startups_development_stage_view']
            ],
            [
                'div-class' => 'col-md-6 mt-6',
                'card-title' => __('admin.Sector_Breakdown'),
                'type' => 'div',
                'id' => 'sectorBreakdownChart',
                'permissions' => ['dashboard.widgets_graphs_sector_breakdown_view']
            ],
            [
                'div-class' => 'col-md-6 mt-6',
                'card-title' => __('admin.User_distribution_by_country'),
                'type' => 'canvas',
                'id' => 'userDistributionChart',
                'permissions' => ['dashboard.widgets_graphs_startups_hq_view']
            ],
            [
                'div-class' => 'col-md-6 mt-6',
                'card-title' => __('admin.Target_Market_Analysis'),
                'type' => 'canvas',
                'id' => 'targetMarketChart',
                'permissions' => ['dashboard.widgets_graphs_startups_target_markets_view']
            ],
            // [
            //     'div-class' => 'col-12 col-md-6 mt-6',
            //     'card-title' => __('admin.Project_Status_Over_Time'),
            //     'type' => 'div',
            //     'id' => 'projectStatusOverTimeChart',
            //     'permissions' => ['dashboard.widgets_graphs_program_progress_view']
            // ],
        ];

        $dateRate = date("Y-m-d 23:59:59");
        $notRated_men = "SELECT
                *, 
                CONCAT(TRIM(COALESCE(u.first_name_en, '')), ' ', TRIM(COALESCE(u.last_name_en, ''))) AS MentorName_en,
                CONCAT(TRIM(COALESCE(u.first_name_ar, '')), ' ', TRIM(COALESCE(u.last_name_ar, ''))) AS MentorName_ar,
                mr.id as mr_id
            FROM
                mentorship_requests as mr
            LEFT JOIN
                users as u ON u.id = mr.mentor_id 
            WHERE
                zoom_date < ?
                AND user_id = ?
                AND approved = 'Yes'
                AND rating IS NULL
            ORDER BY
                zoom_date ASC
        ";
        $getNotEvaluatedCompanies = collect(DB::select("SELECT
                results.id AS result_id,
                results.evaluation_id,
                eval.step_id AS step_id,
                results.company_id AS company_id,
                results.status,
                results.comment
            FROM
                screening_evaluation_results as results
            LEFT JOIN
                screening_evaluation as eval ON eval.id = results.evaluation_id
            WHERE
                results.user_id = ?
        ", [
            $user->id
        ]));
        $established_evaluations_q = "SELECT
                eval.id,
                eval.step_id,
                eval.status
            FROM
                screening_evaluation as eval
            WHERE
                eval.id > 0
        ";
        $established_evaluations_inputs = [];
        $initial_steps_ids = $filtration->pluck('id')->unique()->toArray();
        $final_steps_ids = $evaluation->pluck('id')->unique()->toArray();
        $steps_ids = array_merge($initial_steps_ids, $final_steps_ids);
        if( count( $steps_ids ) > 0 )
        {
            $steps_idsPlaceholders = implode(',', array_fill(0, count($steps_ids), '?'));
            $established_evaluations_q .= " AND eval.step_id IN ($steps_idsPlaceholders)";
            $established_evaluations_inputs = $steps_ids;
        }
        $established_evaluations = collect(DB::select($established_evaluations_q, $established_evaluations_inputs));
        $filtration = ScreeningEvaluation::getMyEvaluationStatus( $filtration, $getNotEvaluatedCompanies, $established_evaluations );
        $evaluation = ScreeningEvaluation::getMyEvaluationStatus( $evaluation, $getNotEvaluatedCompanies, $established_evaluations );

        $notRated_men = DB::select($notRated_men, [$dateRate, Auth::id()]);
        $notRated_men = collect($notRated_men);
        // check for rating services

        $service_requests = DB::table('services_requests AS sr')
            ->select([
                'sr.id AS id',
                'sr.service_id AS service_id',
                's.name_en AS service_name_en',
                's.name_ar AS service_name_ar',
                's.service_provider_id AS service_provider_id',
                'sp.company_name AS provider_name',
                'sp.email AS provider_email',
                'sp.phone AS provider_phone',
                'sr.comment AS comment',
                'sr.date_time AS date_time',
                'sr.rate AS rate',
                'sr.rate_comment AS rate_comment',
                'sr.done AS done',
            ])
            ->leftJoin('services AS s', 's.id', '=', 'sr.service_id')
            ->leftJoin('service_providers AS sp', 'sp.id', '=', 's.service_provider_id')
            ->where('sr.user_id', $user->id)
            ->where('sr.approved', 'Yes')
            ->whereNull('sr.rate')
            ->whereNull('sr.done')
            ->whereNull('sr.deleted_at')
            ->get();

        $service_requests = $service_requests->map(function ( $item ) use ($l) {
            $_request = new stdClass;
            $_request->id = $item->id;
            $_request->service = new stdClass;
            $_request->service->id = $item->service_id;
            $_request->service->name = $item->{'service_name_' . $l};
            $_request->provider = new stdClass;
            $_request->provider->id = $item->service_provider_id;
            $_request->provider->name = $item->provider_name;
            $_request->provider->email = $item->provider_email;
            $_request->provider->phone = $item->provider_phone;
            $_request->comment = $item->comment;
            $_request->date_time = $item->date_time;
            $_request->rate = $item->rate;
            $_request->rate_comment = $item->rate_comment;
            $_request->done = $item->done;
            return $_request;
        });
        $data['unrated_services_requests'] = $service_requests;
        $data['notrated_mens'] = $notRated_men;
        $data['initial_screening_steps'] = $filtration;
        $data['final_screening_steps'] = $evaluation;

        if(Auth::user()->hasRole('Registered') || Auth::user()->hasRole('Mentor')){
            
        
                $user = Auth::user();
			if(isset($user->team_member) && $user->team_member->teamFormation->user->company?->cycle != null){
				$cycle = $user->team_member->teamFormation->user->company->cycle;
            }elseif(isset($user->company) && $user->company->cycle != null){
                    $cycle = $user->company->cycle;
                    $cycleType = Cycle::where('id', $cycle)->first();
                    if($cycleType->type != 'Hackathon'){
                        $cycle = null;
                    }else{
                        $cycle = $user->company->cycle;
                    }
            }
            $params = [];
        $newhackathon_q = "SELECT *
                            FROM 
                            cycles
                            WHERE `type` = 'hackathon' ";
                            if(isset($cycle) && $cycle != null){
                                $newhackathon_q .= "AND id = ? ";
                                $params[] = $cycle;
                                $newhackathon_q .= "ORDER BY created_at DESC LIMIT 1";
                            $newhackathon = DB::select($newhackathon_q, $params);
                            }else{
                                $newhackathon_q .= "AND `start` < now() ";
                                $newhackathon_q .= "AND `end` > now() ";
                                $newhackathon_q .= "ORDER BY created_at DESC LIMIT 1";
                                $newhackathon = DB::select($newhackathon_q);
                                
                            }
        $newhackathon = collect($newhackathon)->first();
        $data['newhackathon'] = $newhackathon;
        }
        

        $team_member_q = "SELECT 
                        tm.id as 'tm_id',
                        tm.user_id as 'tm_user_id',
                        tm.team_formation_id as 'tm_team_formation_id',
                        tm.Name as 'tm_name',
                        tm.Email as 'tm_email',
                        tm.isTeamLeader as 'tm_isTeamLeader',
                        tf.id as 'tf_id',
                        tf.name as 'tf_name',
                        tf.description as 'tf_description',
                        tf.cycle_id as 'tf_cycle_id',
                        tf.step_id as 'tf_step_id',
                        tf.img_team as 'tf_img_team',
                        tf.user_id as 'tf_user_id',
                        tf.project_name_en as 'tf_projectName',
                        u.id as 'u_id',
                        concat(u.first_name_en, ' ', u.last_name_en) as 'user_name_en',
                        concat(u.first_name_ar, ' ', u.last_name_ar) as 'user_name_ar',
                        u.email as 'u_email',
                        u.img_url as 'u_img_url'
                    FROM
                        team_members as tm
                    LEFT JOIN
                        team_formations as tf ON tf.id = tm.team_formation_id
                    LEFT JOIN
                        users as u ON u.id = tf.user_id
                    WHERE tm.Email = ? 
                    AND tm.TeamMember = 'hackathon' 
                    AND tm.acceptInvition IN ('pending', 'join') ";
        $team_member = DB::select($team_member_q, [$user->email]);
        $team_member = collect($team_member);
        $data['team_member'] = $team_member;

        // Determine if polling is needed for completion status
        $user = auth()->user();
        $profileComplete = method_exists($user, 'hasCompletedProfile') ? $user->hasCompletedProfile() : (!empty($user->phone) && !empty($user->country_id) && !empty($user->address) && !empty($user->timezone));
        $startupComplete = method_exists($user, 'hasCompletedStartup') ? $user->hasCompletedStartup() : ($user->company && !empty($user->company->name_en) && !empty($user->company->address_en) && !empty($user->company->sector) && !empty($user->company->stage));
        $data['pollingNeeded'] = !$profileComplete || !$startupComplete;
        $settings = Settings::where('id', 1)->first();
		$data['offline_allowed'] = $settings->allow_offline_sessions == 1 ? true : false;

        // Fetch Startup Milestones
        $milestones = collect();
        if ($user->company_id) {
            $milestones = Milestone::where('Company_id', $user->company_id)
                ->orderBy('milestone_date', 'asc')
                ->get();
        }
        $data['milestones'] = $milestones;
        $data['profile_completed'] = true;
        $data['company_completed'] = true;

        if( $user->hasRole('registered') )
        {
            $data['profile_completed'] = $profileComplete;
            $data['company_completed'] = $startupComplete;
        }

        // GV-3691: the mentors widget offers "Request Mentorship", so it is only
        // relevant while the founder's program sits on its mentorship stage.
        $data['mentorshipActive'] = MentorshipAvailabilityService::isActiveForCompany($user->company);

        return view('includes.dashboard', $data);
    }
    protected function adminDashboard()
    {
        $l = app()->getLocale();
        $startups =   Company::count();
        $messages = Messenger::where('receiver', Auth::user()->id)->whereNull('read')->count();
        $servrequests = ServicesRequests::whereNull('approved')->count();
        $mentrequests = new MentorshipRequest();
        // $managers = User::join('model_has_roles', 'id', '=', 'model_id')->whereIn('role_id', [5, 1])->count();
		$managers = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
			->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
			->whereIn('roles.default_for', ['admin', 'organizationmanager'])
			->select('users.*')
			->get();
        $cmessages = Messages::where('read', '<>', 1)->count();
        // $mentors = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 6)->count();
		$mentors = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
			->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
			->where('roles.default_for', 'mentor')
			->select('users.*')
			->get();
        // $judgers = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 7)->count();
		$judgers = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
			->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
			->where('roles.default_for', 'judger')
			->select('users.*')
			->get();

        $cycles = new Cycle();
        $sessions = TrainingSession::whereHas('step', function ($q) {
            $q->where('stype', 4);
        });
        $organizations = new Organization();

        //=================> Counts Section<==============

        $data['messages_count'] = $messages;
        $data['servrequests_count'] = $servrequests;

        $data['managers_count'] = $managers;
        $data['cmessages_count'] = $cmessages;
        $data['startups_count'] =   $startups;
        $data['mentors_count'] = $mentors;
        $data['mentrequests_count'] = $mentrequests->count();
        $data['mentrequests_period'] = $mentrequests->sum('period') / 60;
        $data['judgers_count'] = $judgers;
        $data['cycles_count'] = $cycles->count();

        $data['sessions_count'] = $sessions->count();
        $data['sessions_period'] = $sessions->sum('duration') / 60;
        $data['organizations_count'] = $organizations->count();

        //=============> Charts Section <=====================
        $usersgov = User::join('states', 'state_id', '=', 'states.id')->select('name_en as state_en', 'name_ar as state_ar', DB::raw('count(*) as total'));
        $usersgender = User::select('gender', DB::raw('count(*) as total'));

        $data['usersgov'] = $usersgov->groupBy('name_en', 'name_ar');
        $data['usersgender'] = $usersgender->groupBy('gender');

        $data['cycles_labels'] = [];
        $data['cycles_startups_count'] = [];
        foreach ($cycles->get() as $key) {
            $data['cycles_labels'][] = $key->title;
            $data['cycles_startups_count'][] = (int)$key->companies()->count();
        }

        // Target Market Analysis
        $targetMarket = DB::table('countries')
            ->select(
                'countries.name_en as country_name_en',
                'countries.name_ar as country_name_ar',
                DB::raw('COUNT(companies.id) as company_count')
            )
            ->leftJoin(
                'companies',
                DB::raw('JSON_CONTAINS(companies.targeted_market, CONCAT(\'"\', countries.id, \'"\'), "$")'),
                '=',
                DB::raw('1')
            )
            ->groupBy('countries.id', 'countries.name_en', 'countries.name_ar')
            ->orderByDesc('company_count')
            ->get();

        $data['target_market_data'] = $targetMarket->map(function ($item) {
            return [
                'label_en' => $item->country_name_en,
                'label_ar' => $item->country_name_ar,
                'count' => $item->company_count,
            ];
        });

        // Sector Breakdown
        $sectorBreakdown = Sector::withCount('company')->get();

        $data['sector_data'] = $sectorBreakdown->map(function ($item) {
            return [
                'label_en' => $item->name_en,
                'label_ar' => $item->name_ar,
                'count' => $item->company_count,
            ];
        });


        // Company By Business Model
        $projectTypes = Company::select('business_type', DB::raw('count(*) as total'))
            ->groupBy('business_type')
            ->whereNotNull('business_type')
            ->get();

        $data['project_types'] = $projectTypes->map(function ($item) {
            return [
                'label' => $item->business_type,
                'count' => $item->total,
            ];
        });

        // Total Jobs Created
        $totalJobsCreated = Company::count('team_num');

        $companyData = Company::select('name_en', 'name_ar', 'team_num')
            ->whereNotNull('team_num')
            ->get();

        $companies = $companyData->map(function ($company) {
            return [
                'company_name_en' => $company->name_en,
                'company_name_ar' => $company->name_ar,
                'team_members' => $company->team_num,
            ];
        });

        $data['total_jobs_created'] = [
            'total' => $totalJobsCreated,
            'companies' => $companies,
        ];

        // Total Founders
        $totalFounders = DB::table('team_members')->where('TeamMember', 'Founder')->count();

        $teamMembersData = DB::table('team_members')
            ->select('TeamMember', DB::raw('count(*) as count'))
            ->groupBy('TeamMember')
            ->get();


        $foundersData = $teamMembersData->map(function ($item) {
            return [
                'team_member' => $item->TeamMember,
                'count' => $item->count,
            ];
        });

        $data['total_founders'] = [
            'total' => $totalFounders,
            'team_members' => $foundersData,
        ];


        // Companies Per Sector
        $sectorData = Company::select('sector', DB::raw('count(*) as total'))
            ->whereNotNull('sector')
            ->groupBy('sector')
            ->get();

        $data['sector_distribution'] = $sectorData->map(function ($item) {
            if (is_numeric($item->sector)) {
                $sectorDetails = DB::table('sectors')
                    ->where('id', $item->sector)
                    ->select('name_en', 'name_ar')
                    ->first();

                return [
                    'sector_en' => $sectorDetails ? $sectorDetails->name_en : __('admin.undefined', [], 'en'),
                    'sector_ar' => $sectorDetails ? $sectorDetails->name_ar : __('admin.undefined', [], 'ar'),
                    'count' => $item->total,
                ];
            } else {
                return [
                    'sector_en' => __('admin.' . $item->sector, [], 'en'),
                    'sector_ar' => __('admin.' . $item->sector, [], 'ar'),
                    'count' => $item->total,
                ];
            }
        });


        // Companies by SDG
        $companySdg = Company::select('Sustainable_development')
            ->whereNotNull('Sustainable_development')
            ->get();

        $data['sdg_data'] = $companySdg
            ->flatMap(function ($item) {
                $sdgs = json_decode($item->Sustainable_development, true);
                return is_array($sdgs) ? $sdgs : [];
            })
            ->countBy()
            ->map(function ($count, $sdg) {
                return [
                    'sdg_en' => __('admin.' . $sdg, [], 'en'),
                    'sdg_ar' => __('admin.' . $sdg, [], 'ar'),
                    'total' => $count,
                ];
            })
            ->values();


        // Total Startups Per Organization
        $organizationsData = User::join('organizations', 'users.organization_id', '=', 'organizations.id')
            ->join('cycles', 'users.organization_id', '=', 'cycles.organization_id')
            ->join('companies', 'companies.cycle', '=', 'cycles.id')
            ->select(
                'organizations.name_en as organization_name_en',
                'organizations.name_ar as organization_name_ar',
                DB::raw('count(companies.id) as total_startups')
            )
            ->groupBy('organizations.name_en', 'organizations.name_ar')
            ->get();

        $data['organization_counts'] = $organizationsData->map(function ($item) {
            return [
                'organization_name_en' => $item->organization_name_en,
                'organization_name_ar' => $item->organization_name_ar,
                'total_startups' => $item->total_startups,
            ];
        });

        // Total Raised Funds
        $totalRaisedFunds = Company::sum('raised_fund');

        $companyData = Company::select('name_en', 'name_ar', 'raised_fund')
            ->whereNotNull('raised_fund')
            ->get();

        $companies = $companyData->map(function ($company) {
            return [
                'company_name_en' => $company->name_en,
                'company_name_ar' => $company->name_ar,
                'raised_fund' => $company->raised_fund,
            ];
        });

        $data['total_raised_funds'] = [
            'total' => $totalRaisedFunds,
            'companies' => $companies,
        ];

        // User Distribution by Country
        $countryData = User::join('countries', 'users.country_id', '=', 'countries.id')
            ->select('countries.name_en as country_name_en', 'countries.name_ar as country_name_ar', DB::raw('count(users.id) as total'))
            ->groupBy('countries.name_en', 'countries.name_ar')
            ->get();

        $data['countries'] = $countryData->map(function ($item) {
            return [
                'label_en' => $item->country_name_en,
                'label_ar' => $item->country_name_ar,
                'count' => $item->total,
            ];
        });

        // Program Status
        $projectStatus = TimeSheet::join('cycles', 'program_id', '=', 'cycles.id')
            ->select(
                'cycles.title as cycle_title',
                DB::raw('count(time_sheets.program_id) as total_programs'),
                'cycles.start as cycle_start_date',
                'cycles.end as cycle_end_date'
            )
            ->groupBy('cycles.title', 'cycles.start', 'cycles.end')
            ->get();
        $data['project_status'] = $projectStatus->map(function ($item) {
            return [
                'cycle_title' => $item->cycle_title,
                'total_programs' => $item->total_programs,
                'cycle_start_date' => $item->cycle_start_date,
                'cycle_end_date' => $item->cycle_end_date,
            ];
        });

        // Development Stage
        $developmentStages = Company::whereNotNull('development_stage')->get();
        $uniqueDevelopmentStages = $developmentStages->unique('development_stage');

        $data['development_stages'] = $uniqueDevelopmentStages->map(function ($item) use ($developmentStages) {
            $count = $developmentStages->where('development_stage', $item->development_stage)->count();

            if (is_numeric($item->development_stage)) {
                $stage = DB::table('stages')
                    ->where('id', $item->development_stage)
                    ->select('name_en', 'name_ar')
                    ->first();

                return [
                    'label_en' => $stage ? $stage->name_en : null,
                    'label_ar' => $stage ? $stage->name_ar : null,
                    'count' => $count,
                ];
            } else {
                return [
                    'label_en' => __('admin.' . $item->development_stage, [], 'en'),
                    'label_ar' => __('admin.' . $item->development_stage, [], 'ar'),
                    'count' => $count,
                ];
            }
        })->values();

        return view('admin.dashboard', $data);
    }
    protected function organizationDashboard()
    {
        $l = app()->getLocale();
        $user = Auth::user();
        $orgIds = $user->organizations_ids;
        $mentorshipRequests = MentorshipRequest::whereIn('organization_id', $orgIds);
        $forms = BuilderForm::whereIn('organization_id', $orgIds);
        $sessions = TrainingSession::whereHas('step', function ($q) use ($orgIds) {
            $q->where('stype', 4)->whereHas('cycle', function ($query) use ($orgIds) {
                $query->whereIn('organization_id', $orgIds);
            });
        });
        $messages = Messenger::where('receiver', auth()->id())->whereNull('read');
        $startups = Company::whereHas('cycledata', function ($q) use ($orgIds) {
            $q->whereIn('organization_id', $orgIds);
        });
        $cycles = Cycle::whereIn('organization_id', $orgIds);

        // ============> Counts Section <==================
        $data['startups'] = $startups->count();
        $data['sessions'] = $sessions->count();
        $data['sessions_period'] = $sessions->sum('duration') / 60;

        $data['cycles_count'] = $cycles->count();
        $data['forms_count'] = $forms->count();

        $data['mentrequests'] = $mentorshipRequests->count();
        $data['mentrequests_period'] = $mentorshipRequests->sum('period') / 60;
        $data['messages'] = $messages->count();
        // ============> Chart Statistics Section <==================
        $data['sessions_labels'] = [];
        $data['sessions_durations'] = [];
        $data['mentorship_labels'] = [];
        $data['mentorship_durations'] = [];
        foreach ($sessions->get() as $key) {
            $data['sessions_labels'][] = $key->title . ' ' . $key->start_format;
            $data['sessions_durations'][] = (int)$key->duration / 60;
        }
        foreach ($mentorshipRequests->get() as $key) {
            $data['mentorship_labels'][] = $key->mentor->name . ' ' . $key->zoom_date;
            $data['mentorship_durations'][] = (int)$key->period / 60;
        }
        //=============> listing Section <===================
        $data['mentorshipRequests'] = $mentorshipRequests->whereNull('approved')->latest()->limit(5)->get();
        $data['latest_cycles'] = $cycles->latest()->limit(10)->get();

        $data['cycles_labels'] = [];
        $data['cycles_startups_count'] = [];
        foreach ($cycles->get() as $key) {
            $data['cycles_labels'][] = $key->title;
            $data['cycles_startups_count'][] = (int)$key->companies()->count();
        }

        // Demographic
        $data['usersgov'] = User::join('states', 'users.state_id', '=', 'states.id')
            ->whereIn('users.organization_id', $orgIds)
            ->select(
                'states.name_en as state_en',
                'states.name_ar as state_ar',
                DB::raw('count(*) as total')
            )
            ->groupBy('states.name_en', 'states.name_ar')
            ->get();


        // Male and Female Ratio
        $usersgender = User::select('gender', DB::raw('count(*) as total'))->whereIn('organization_id', $orgIds)->groupBy('gender')->get();
        $data['usersgender'] = $usersgender;

        // Development Stage  

        $data['development_stages'] = [];

        foreach ($cycles->get() as $cycle) {
            $developmentStages = Company::where('cycle', $cycle->id)
                ->whereNotNull('development_stage')
                ->select('development_stage', DB::raw('count(*) as total'))
                ->groupBy('development_stage')
                ->get();

            foreach ($developmentStages as $stage) {
                if (is_numeric($stage->development_stage)) {
                    $stageDetails = DB::table('stages')
                        ->where('id', $stage->development_stage)
                        ->select('name_en', 'name_ar')
                        ->first();

                    $data['development_stages'][] = [
                        'label_en' => $stageDetails ? $stageDetails->name_en : __('admin.undefined', [], 'en'), // Fallback to 'undefined' if not found
                        'label_ar' => $stageDetails ? $stageDetails->name_ar : __('admin.undefined', [], 'ar'), // Fallback to 'undefined' if not found
                        'count' => $stage->total,
                    ];
                } else {
                    $data['development_stages'][] = [
                        'label_en' => __('admin.' . $stage->development_stage, [], 'en'),
                        'label_ar' => __('admin.' . $stage->development_stage, [], 'ar'),
                        'count' => $stage->total,
                    ];
                }
            }
        }

        // Target Market Analysis
        $targetMarket = DB::table('countries')
            ->select('countries.name_en as country_name_en', 'countries.name_ar as country_name_ar', DB::raw('COUNT(companies.id) as company_count'))
            ->leftJoin('companies', DB::raw('JSON_CONTAINS(companies.targeted_market, CONCAT(\'"\', countries.id, \'"\'), "$")'), '=', DB::raw('1'))
            ->join('cycles', 'companies.cycle', '=', 'cycles.id')
            ->join('users', 'cycles.organization_id', '=', 'users.organization_id')
            ->where('users.organization_id', $organization_id)
            ->groupBy('countries.id', 'countries.name_en', 'countries.name_ar')
            ->orderByDesc('company_count')
            ->get();

        $data['target_market_data'] = $targetMarket->map(function ($item) {
            return [
                'label_en' => $item->country_name_en,
                'label_ar' => $item->country_name_ar,
                'count' => $item->company_count,
            ];
        });

        // Sector Breakdown
        $sectorBreakdown = Sector::join('companies', 'companies.sector', '=', 'sectors.id')
            ->join('cycles', 'companies.cycle', '=', 'cycles.id')
            ->where('cycles.organization_id', $organization_id)
            ->select(
                'sectors.name_en as sector_name_en',
                'sectors.name_ar as sector_name_ar',
                DB::raw('count(companies.id) as total')
            )
            ->groupBy('sectors.name_en', 'sectors.name_ar')
            ->get();

        $data['sector_data'] = $sectorBreakdown->map(function ($item) {
            return [
                'label_en' => $item->sector_name_en,
                'label_ar' => $item->sector_name_ar,
                'count' => $item->total,
            ];
        });

        // Company By Business Model
        $projectTypes = Company::join('cycles', 'companies.cycle', '=', 'cycles.id')
            ->join('users', 'cycles.organization_id', '=', 'users.organization_id')
            ->where('users.organization_id', $organization_id)
            ->select('companies.business_type', DB::raw('count(companies.id) as total'))
            ->whereNotNull('business_type')
            ->groupBy('companies.business_type')
            ->get();

        $data['project_types'] = $projectTypes->map(function ($item) {
            return [
                'label' => $item->business_type,
                'count' => $item->total,
            ];
        });


        // Total Jobs Created
        $companyData = Company::join('cycles', 'companies.cycle', '=', 'cycles.id')
            ->join('users', 'cycles.organization_id', '=', 'users.organization_id')
            ->where('users.organization_id', $organization_id)
            ->select('companies.name_en as company_name_en', 'companies.name_ar as company_name_ar', 'companies.team_num')
            ->whereNotNull('companies.team_num')
            ->get();

        $companies = $companyData->map(function ($company) {
            return [
                'company_name_en' => $company->company_name_en,
                'company_name_ar' => $company->company_name_ar,
                'team_members' => $company->team_num,
            ];
        });

        $totalJobsCreated = $companyData->count('team_num');

        $data['total_jobs_created'] = [
            'total' => $totalJobsCreated,
            'companies' => $companies,
        ];

        // Total Founders
        $companyData = Company::join('cycles', 'companies.cycle', '=', 'cycles.id')
            ->join('users', 'cycles.organization_id', '=', 'users.organization_id')
            ->where('users.organization_id', $organization_id)
            ->whereNotNull('companies.founder')
            ->select('companies.name_en as company_name_en', 'companies.name_ar as company_name_ar', 'companies.founder')
            ->get();

        $companies = $companyData->map(function ($company) {
            return [
                'company_name_en' => $company->company_name_en,
                'company_name_ar' => $company->company_name_ar,
                'founder' => $company->founder,
            ];
        });

        $totalFounders = $companyData->count();

        $data['total_founders'] = [
            'total' => $totalFounders,
            'companies' => $companies,
        ];


        // Companies by SDG
        $companySdg = Company::whereHas('cycledata', function ($query) use ($organization_id) {
            $query->where('organization_id', $organization_id);
        })
            ->select('Sustainable_development')
            ->whereNotNull('Sustainable_development')
            ->get();

        $data['sdg_data'] = $companySdg
            ->flatMap(function ($item) {
                $sdgs = json_decode($item->Sustainable_development, true);
                return is_array($sdgs) ? $sdgs : [];
            })
            ->countBy()
            ->map(function ($count, $sdg) {
                return [
                    'sdg_en' => __('admin.' . $sdg, [], 'en'),
                    'sdg_ar' => __('admin.' . $sdg, [], 'ar'),
                    'total' => $count,
                ];
            })
            ->values();

        // Total Startups Per Organization

        $organizationStartups = User::join('organizations', 'users.organization_id', '=', 'organizations.id')
            ->join('cycles', 'users.organization_id', '=', 'cycles.organization_id')
            ->join('companies', 'companies.cycle', '=', 'cycles.id')
            ->where('users.organization_id', $organization_id)
            ->select(
                'organizations.name_en as organization_name_en',
                'organizations.name_ar as organization_name_ar',
                DB::raw('count(companies.id) as total_startups')
            )
            ->groupBy('organizations.name_en', 'organizations.name_ar')
            ->first();

        $data['organization_counts'] = $organizationStartups ? [
            'organization_name_en' => $organizationStartups->organization_name_en,
            'organization_name_ar' => $organizationStartups->organization_name_ar,
            'total_startups' => $organizationStartups->total_startups,
        ] : [
            'organization_name_en' => null,
            'organization_name_ar' => null,
            'total_startups' => 0,
        ];

        // Total Raised Funds

        $totalRaisedFunds = Company::join('cycles', 'companies.cycle', '=', 'cycles.id')
            ->join('users', 'cycles.organization_id', '=', 'users.organization_id')
            ->where('users.organization_id', $organization_id)
            ->sum('companies.raised_fund');

        $companyData = Company::join('cycles', 'companies.cycle', '=', 'cycles.id')
            ->join('users', 'cycles.organization_id', '=', 'users.organization_id')
            ->where('users.organization_id', $organization_id)
            ->select('companies.name_en as company_name_en', 'companies.name_ar as company_name_ar', 'companies.raised_fund')
            ->whereNotNull('companies.raised_fund')
            ->get();

        $companies = $companyData->map(function ($company) use ($l) {
            return [
                'company_name_en' => $company->company_name_en,
                'company_name_ar' => $company->company_name_ar,
                'raised_fund' => $company->raised_fund,
            ];
        });

        $data['total_raised_funds'] = [
            'total' => $totalRaisedFunds,
            'companies' => $companies,
        ];

        // User Distribution by Country
        $countryData = User::join('countries', 'users.country_id', '=', 'countries.id')
            ->where('users.organization_id', $organization_id)
            ->select(
                "countries.name_en as country_name_en",
                "countries.name_ar as country_name_ar",
                DB::raw('count(users.id) as total')
            )
            ->groupBy('countries.name_en', 'countries.name_ar')
            ->get();

        $data['countries'] = $countryData->map(function ($item) {
            return [
                'label_en' => $item->country_name_en,
                'label_ar' => $item->country_name_ar,
                'count' => $item->total,
            ];
        });

        // Program Status
        $projectStatus = TimeSheet::join('cycles', 'time_sheets.program_id', '=', 'cycles.id')
            ->join('users', 'cycles.organization_id', '=', 'users.organization_id')
            ->where('users.organization_id', $organization_id)
            ->select(
                'cycles.title as cycle_title',
                DB::raw('count(time_sheets.program_id) as total_programs'),
                'cycles.start as cycle_start_date',
                'cycles.end as cycle_end_date'
            )
            ->groupBy('cycles.title', 'cycles.start', 'cycles.end')
            ->get();
        $data['project_status'] = $projectStatus->map(function ($item) {
            return [
                'cycle_title' => $item->cycle_title,
                'total_programs' => $item->total_programs,
                'cycle_start_date' => $item->cycle_start_date,
                'cycle_end_date' => $item->cycle_end_date,
            ];
        });

        // Companies By Sector
        $sectorData = Company::join('cycles', 'companies.cycle', '=', 'cycles.id')
            ->join('users', 'cycles.organization_id', '=', 'users.organization_id')
            ->where('users.organization_id', $organization_id)
            ->select('companies.sector', DB::raw('count(companies.id) as total'))
            ->whereNotNull('companies.sector')
            ->groupBy('companies.sector')
            ->get();

        $data['sector_distribution'] = $sectorData->map(function ($item) {
            if (is_numeric($item->sector)) {
                $sectorDetails = DB::table('sectors')
                    ->where('id', $item->sector)
                    ->select('name_en', 'name_ar')
                    ->first();

                return [
                    'sector_en' => $sectorDetails ? $sectorDetails->name_en : __('admin.undefined', [], 'en'), // Fallback to 'undefined' if not found
                    'sector_ar' => $sectorDetails ? $sectorDetails->name_ar : __('admin.undefined', [], 'ar'), // Fallback to 'undefined' if not found
                    'count' => $item->total,
                ];
            } else {
                return [
                    'sector_en' => __('admin.' . $item->sector, [], 'en'),
                    'sector_ar' => __('admin.' . $item->sector, [], 'ar'),
                    'count' => $item->total,
                ];
            }
        });


        return view('admin.organizations.dashboard', $data);
    }

    protected function judgerDashboard()
    {
        $myid = auth()->id();
        // ===> counts <=====
        $data['services_count'] = Service::count();
        $data['service_requests_count'] = auth()->user()->myServiceRequests()->count();
        $data['messenger_count'] = Messenger::where('receiver', $myid)->whereNull('read')->count();

        // ======> tables <=====
        $data['services_latest'] = Service::orderBy('created_at', 'DESC')->take(5)->get();
        $data['events_latest'] = Calendar::where('start', '>=', now()->format('Y-m-d'))->where(function ($q) {
            $q->orWhere('user_id', Auth::id())
                ->orWhereNull('isPrivate');
        })->orderBy('start')->take(5)->get();
        // ======> process <=========
        $data['mysteps'] = auth()->user()->screeningRequests()->get();
        $today = date("Y-m-d");
        $data['csteps'] = Step::whereRaw('"' . $today . '" BETWEEN `from` AND `to`')->whereIn('id', $data['mysteps']->pluck('step_id'))->get();
        $data['fcompanies'] = Finalscreening::where('judges', 'like', "%\"{$myid}\"%")->get();
        return view('admin.clients.judge', $data);
    }
    protected function mentorDashboard()
    {
        $data['services_latest'] = Service::orderBy('created_at', 'DESC')->take(5)->get();
        $data['event_latest'] = Calendar::where('start', '>=', now()->format('Y-m-d'))->where(function ($q) {
            $q->orWhere('user_id', Auth::id())
                ->orWhereNull('isPrivate');
        })->orderBy('start')->take(5)->get();
        $data['services_count'] = Service::count();
        $data['service_requests_count'] = ServicesRequests::where('user_id', Auth::id())->count();
        $data['nm_count'] = Messenger::where('receiver', Auth::id())->whereNull('read')->count();
        $data['mysessions'] = auth()->user()->mentorSessions()->where('zoom_date', '>=', date("Y-m-d 00:00:00"))->where('approved', 'Yes')->get()
            ->each(function ($session) {
                // zoom_date is stored as local wall-clock time (see book-session-modal.blade.php), not UTC (app.timezone)
                $today = \Carbon\Carbon::now(config('app.user_timezone', 'Africa/Cairo'))->format('Y-m-d');
                $session->is_today = $today == date('Y-m-d', strtotime($session->zoom_date));
            });
        $data['mycycles'] = auth()->user()->mentorCycles;
        $data['myschedules'] = Schedule::where('user_id', Auth::id())->get();
        if (auth()->user()->mentorSessions()->whereNotNull('start_url')->whereDate('zoom_date', '<=', now()->format('Y-m-d'))->count()) {
            $data['attends_rate'] = (int)(auth()->user()->mentorSessions()->whereNotNull('start_url')->whereDate('zoom_date', '<=', now()->format('Y-m-d'))->sum('done') / auth()->user()->mentorSessions()->whereNotNull('start_url')->whereDate('zoom_date', '<=', now()->format('Y-m-d'))->count() * 100) . '%';
        } else {
            $data['attends_rate'] = '0%';
        }
        $data['sessionWithoutTasks'] = auth()->user()->mentorSessions()->whereHas('mentorshipTasks', function ($q) {
            return $q->whereNull('title');
        })->count();
        $data['completedTasks'] = MentorshipTask::whereIn('mentorship_request_id', auth()->user()->mentorSessions()->pluck('id'))->where('status', 'completed')->whereNotNull('title')->count();
        $data['nonCompletedTasks'] = MentorshipTask::whereIn('mentorship_request_id', auth()->user()->mentorSessions()->pluck('id'))->where('status', 'pending')->whereNotNull('title')->count();


        return view('mentor.dashboard', $data);
    }

    // ============> End Functions <=======================


    private function getInterstedinServices($user)
    {
        return null;
    }

    public function directory(Request $request)
    {
        // deprecated and moved to ServiceController.php
        $t = $request->input('type');
        $find = $request->input('find');
        $services = Service::with('serviceCategory')
            ->where('status', true)
            ->whereHas('organizations')
            ->get()
            ->map(function ($service) {
                $service->category_name_en = $service->serviceCategory->name_en ?? null;
                $service->category_name_ar = $service->serviceCategory->name_ar ?? null;
                return $service;
            });
        
        // if ($t) {
        //     $services = $services->where('service_category_id', $t);
        // }
        // if ($find) {
        //     $services = $services->where(function ($query) use ($find) {
        //         $query->where('services.name_en', 'like', '%' . $find . '%')
        //             ->orWhere('services.name_ar', 'like', '%' . $find . '%');
        //     });
        // }

        $setting = Settings::where('id', 1)->first();
        $currency = Currency::where('id', $setting->Currency)->first();
        return view('admin.clients.directory')->with('services', $services)->with('setting', $setting)->with('currency', $currency);
    }
    public function rateservice($id, Request $request)
    {
        // deprecated and moved to ServiceController.php
        if ($request && $id) {
            $request->done ? $done = 1 : $done = null;
            ServicesRequests::updateOrInsert(['id' => $id], ['rate' => $request->rate, 'rate_comment' => $request->comment, 'done' => $done]);
            return back();
        }
        return back();
    }
    public function showone($id, Request $request)
    {
        // deprecated and moved to ServiceController.php
        if ($request && $id) {
            $service = Service::findOrFail($id);
            $rate = (int)$service->myRate();
            $service->rate = $rate;
            return response()->json($service);
        }
        return response()->json(['state' => 'failed'], 500);
    }

    public function servicereq(Request $request)
    {
        // deprecated and moved to ServiceController.php
        if ($request) {
            $serviceRequest = ServicesRequests::create([
                'service_id' => $request->id,
                'user_id' => Auth::id(),
                'comment' => $request->comment,
                'date_time' => $request->datetime
            ]);

            event(new WebMessage('NewServ', Auth::user()->first_name_en . ' ' . Auth::user()->last_name_en));

            $service = Service::find($request->id);

            if( $service && $service->vendor_email && !in_array(env('APP_ENV', 'local'), ['local']) )
            {
                // Create anonymous notifiable for vendor email
                Notification::route('mail', $service->vendor_email)->notify(new ServiceRequestNotification($serviceRequest));
            }

            return response()->json(['state' => 'success']);
        }

        return response()->json(['state' => 'failed'], 500);
    }
    public function dark(Request $request)
    {
        $request->l == 'dark' ? session(['layout' => 'light', 'icon' => 'bxs-sun']) : ($request->l == 'light' ? session(['layout' => 'semi-dark', 'icon' => 'bx-moon']) : session(['layout' => 'dark', 'icon' => 'bx-sun']));
        return back();
    }
    public function help($id = 1)
    {
        if (Auth::user()->hasAnyRole('Admin|Manager')) {
            $role = 'admin';
        } elseif (Auth::user()->hasRole('Mentor')) {
            $role = 'mentor';
        } elseif (Auth::user()->hasRole('Judger')) {
            $role = 'judge';
        } else {
            $role = 'user';
        }
        $role = ['all', $role];
        $doc = Documentation::whereIn('role', array_values($role))->where('id', $id)->first();
        $menu = Documentation::whereIn('role', array_values($role))->orderBy('arr', 'ASC')->get(['id', 'menu_ar', 'menu_en']);
        if (count($menu) == 0 or !$doc) {
            abort(404);
        }
        return view('admin.documentation', compact('doc', 'menu'));
    }

    public function checkCompletionStatus(Request $request)
    {
        $user = auth()->user();
        $profileComplete = false;
        $startupComplete = false;

        // Check allow_non_company for the specific cycle if cycle_id is provided
        $allowNonCompany = false;
        if ($request->has('cycle_id')) {
            $allowNonCompany = DB::table('cycles')
                ->where('id', $request->get('cycle_id'))
                ->value('allow_non_company');
        }

        // Profile check always runs
        if (method_exists($user, 'hasCompletedProfile')) {
            $profileComplete = $user->hasCompletedProfile();
        } else {
            // fallback logic
            $profileComplete = !empty($user->phone) && !empty($user->country_id) && !empty($user->address) && !empty($user->timezone);
        }

        // Only check startup completion if cycle doesn't allow non-company
        if (!$allowNonCompany) {
            if (method_exists($user, 'hasCompletedStartup')) {
                $startupComplete = $user->hasCompletedStartup();
            } else {
                // fallback logic for startup completion
                $startupComplete = $user->company && !empty($user->company->name_en) && !empty($user->company->address_en) && !empty($user->company->sector) && !empty($user->company->stage);
            }
        } else {
            $startupComplete = true;
        }

        $preRequests = [];

        if (!$profileComplete) {
            $preRequests[] = [
                'id' => 'complete_profile',
                'url' => route('user.profile_Edit'),
                'label' => __('admin.Complete Profile')
            ];
        }

        if (!$startupComplete) {
            $preRequests[] = [
                'id' => 'complete_startup',
                'url' => route('company.profile_view_mine', ['company' => $user->company_id]),
                'label' => __('admin.Complete Startup/Idea'),
                'action' => 'complete-profile'
            ];
        }

        return response()->json([
            'profile_complete' => $profileComplete,
            'startup_complete' => $startupComplete,
            'has_pre_requests' => count($preRequests) > 0,
            'pre_requests' => $preRequests
        ]);
    }
}
