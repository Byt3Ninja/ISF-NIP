<?php

namespace App\Http\Controllers;

use App\Step;
use App\User;
use stdClass;
use App\Cycle;
use App\Sector;
use App\Company;
use App\Service;
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

use App\Documentation;
use App\Finalscreening;
use App\MentorshipTask;
use App\SurveyResponses;
use App\SustainableIcon;
use App\TrainingSession;
use App\ServicesRequests;
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
            } elseif ($user->hasAnyRole('Registered|Page Manager|Pages Editor')) {

                return view('admin.clients.dashboard');
            } elseif ($user->hasAnyRole('organizationManager')) {

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
        $l = App::getLocale();
        $permissions = [];
        $has_org = !empty($user->organization_id) && !is_null($user->organization_id) && trim($user->organization_id) ? true : false;
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
            if ($has_org) {
                $services_requests_q .= " AND s.organization_id = " . $user->organization_id;
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
            if ($has_org) {
                $organizations_q .= " AND o.id = " . $user->organization_id;
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
            if ($has_org) {
                $cycles_q .= " AND c.organization_id = " . $user->organization_id;
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
            if ($has_org) {
                $companies_q .= " AND c.organization_id = " . $user->organization_id;
            }
            $companies = collect(DB::select($companies_q));
        } else {
            $companies = collect([]);
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
            if ($has_org) {
                $mentorship_requests_q .= " AND c.organization_id = " . $user->organization_id;
            }
            $mentorship_requests = collect(DB::select($mentorship_requests_q));
        } else {
            $mentorship_requests = collect([]);
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
            if ($has_org) {
                $training_sessions_q .= " AND c.organization_id = " . $user->organization_id;
            }
            $training_sessions = collect(DB::select($training_sessions_q))->map(function ($session) {
                $session->duration = !empty($session->duration) ? $session->duration : 0;
                return $session;
            });
        } else {
            $training_sessions = collect([]);
        }

        if (array_intersect(['dashboard.widgets_cards_services_view'], $permissions)) {
            $services_q = "SELECT DISTINCT
                    s.id,
                    s.name_en,
                    s.name_ar,
                    s.price,
                    s.description_en,
                    s.description_ar,
                    sc.id AS service_category_id,
                    sc.name_en AS service_category_name_en,
                    sc.name_ar AS service_category_name_ar
                FROM
                    services s
                LEFT JOIN
                    service_categories sc ON s.service_category_id = sc.id
                INNER JOIN
                     organization_service so ON s.id = so.service_id
                WHERE
                    s.deleted_at IS NULL
            ";
            $services = collect(DB::select($services_q))->map(function ($service) use ($l) {
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
            });
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
            if ($has_org) {
                $my_mentors_q .= " AND c.organization_id = " . $user->organization_id;
            }
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
        $cycle = $user->company->cycle ?? 0;
        $mentorIds = DB::table('cycles_mentors')->where('cycle_id', $cycle)->pluck('user_id')->toArray();
        $my_mentors =  $my_mentors->whereIn('id', $mentorIds);
        if (array_intersect(['dashboard.widgets_cards_filtration_view'], $permissions)) {
            $today = date("Y-m-d");
            $inputs = [$today];
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
                    screenings sc ON sc.step_id = s.id
                LEFT JOIN
                    companies cp ON cp.step = sc.step_id
                LEFT JOIN
                    users u ON u.company_id = cp.id AND u.HaveCompany = 1
                WHERE
                    ? BETWEEN s.from AND s.to
                    AND s.deleted_at IS NULL
                    AND s.id IN (
                        SELECT
                            step_id
                        FROM
                            screenings
                        WHERE
                            deleted_at IS NULL
            ";
            if (!$user->hasAnyRole(['admin', 'organizationmanager'])) {
                $filtration_q .= " AND user_id = ? ";
                $inputs[] = $user->id;
            }
            $filtration_q .= " )";
            if ($has_org) {
                $filtration_q .= " AND c.organization_id = " . $user->organization_id;
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
                    fs.join_url
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
                WHERE   
                    fs.deleted_at IS NULL
            ";
            if (!$user->hasAnyRole(['admin', 'organizationmanager'])) {
                $evaluation_q .= " AND fs.judges LIKE '%\"{$user->id}\"%'";
            }
            if ($has_org) {
                $evaluation_q .= " AND cy.organization_id = " . $user->organization_id;
            }
            $evaluation = collect(DB::select($evaluation_q))->map(function ($e) {
                $e->judges = array_map(function ($j) {
                    return intval($j);
                }, json_decode($e->judges, true));
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
                    })->values();
                    $company->judges = $_company->judges;
                    $company->datetime = $_company->datetime;
                    $company->duration = $_company->duration;
                    $company->zoom_id = $_company->zoom_id;
                    $company->passcode = $_company->zoom_passcode;
                    $company->start_url = $_company->start_url;
                    $company->join_url = $_company->join_url;
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
                    c.name_en AS `company_name_en`
                FROM
                    mentorship_requests mr
                LEFT JOIN
                    users u ON u.id = mr.user_id
                LEFT JOIN
                    companies c ON c.id = u.company_id
                WHERE   
                    mr.deleted_at IS NULL
                    AND mr.zoom_date >= ?
                    AND mr.approved = 'Yes'
            ";
            if ($has_org) {
                $upcoming_sessions_q .= " AND mr.organization_id = " . $user->organization_id;
            }
            if (!empty($user->company_id) && !is_null($user->company_id) && trim($user->company_id)) {
                $upcoming_sessions_q .= " AND mr.user_id = " . $user->id;
            }
            if ($user->hasAnyRole(['mentor'])) {
                $upcoming_sessions_q .= " AND mr.mentor_id = " . $user->id;
            }
            $params = [];
            $params[] = date("Y-m-d 00:00:00");
            $l = app()->getLocale();
            $upcoming_sessions = collect(DB::select($upcoming_sessions_q, $params))->map(function ($session) use ($l) {
                $output = new stdClass;
                $output->id = $session->id;
                $output->period = $session->period;
                $output->admin_comment = $session->admin_comment;
                $output->zoom_date = $session->zoom_date;
                $output->start_url = $session->start_url;
                $output->passcode = $session->zoom_passcode;

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
            return in_array('admin', $user->roles) || in_array('manager', $user->roles);
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
        if ($has_org) {
            $conditions = " WHERE s.organization_id = " . $user->organization_id;
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

            if (method_exists($user, 'hasCompletedStartup')) {
                $startupComplete = method_exists($user, 'hasCompletedStartup') ? $user->hasCompletedStartup() : $user->hasCompletedCompany();
            } else {
                $startupComplete = false;
                if (method_exists($user, 'company') || isset($user->company)) {
                    $company = $user->company ?? null;
                    $startupComplete = $company
                        && filled($company->name_en ?? '')
                        && filled($company->sector ?? '')
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

            $cycle->pre_requests = $preRequests;
            return $cycle;
        });
        $myFinal = null;
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

            $myFinal_q = "SELECT
                    fs.id,
                    fs.judges,
                    fs.join_url,
                    fs.datetime,
                    fs.duration,
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
                WHERE
                    fs.company_id = ?
                AND fs.step_id = ?
                LIMIT 1";
            $myFinal = collect(DB::select($myFinal_q, [$user->id, $company->id, $company->step]))->first();
            if (!empty($myFinal)) {
                $myFinal->judges = json_decode($myFinal->judges);

                $judgers_q = "SELECT
                        u.id,
                        u.first_name_en,
                        u.first_name_ar,
                        u.last_name_en,
                        u.last_name_ar,
                        u.img_url
                    FROM
                        users u
                    WHERE u.id IN (" . implode(',', $myFinal->judges) . ")
                ";

                $judgers = collect(DB::select($judgers_q))->map(function ($item) use ($l) {
                    $judger = new stdClass;
                    $judger->id = $item->id;
                    $judger->name = trim($item->{'first_name_' . $l} . ' ' . $item->{'last_name_' . $l});
                    $judger->img_url = User::image_handler($item->img_url);
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
            }

            $stepData = $company->stepdata;
            if (!empty($stepData)) {
                $cType = $stepData->stype;
            }
            $cycle_id = $company->cycle;
            $cycleData = $company->cycledata;
            if ($cycleData) {
                $organization = $cycleData->organization;
                if (!empty($organization)) {
                    $organization_id = $organization->id;
                }
            }
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
                s.data
            FROM
                steps s
            WHERE
                s.cycle_id = $cycle_id
                AND s.cycle_id != 0
                AND s.cycle_id IS NOT null
            ORDER BY
                s.arr asc
        ";
        $now = time();
        $steps = collect(DB::select($steps_q))->map(function ($step) use ($l, $company, $stepData, $cycle_id, $now) {
            $step->from = Carbon::parse($step->from)->format('d-m-Y');
            $step->to = Carbon::parse($step->to)->format('d-m-Y');

            $status = $l == 'ar' ? 'لم تبدأ' : 'Not started';
            $color = 'label-light';
            $textColor = 'light';
            if (!empty($step) && !empty($stepData))
            {
                if( $now > Carbon::parse($step->to)->timestamp )
                {
                    $status = $l == 'ar' ? 'انتهت' : 'Completed';
                    $color = 'label-success';
                    $textColor = 'success';
                }
                if( $now <= Carbon::parse($step->to)->timestamp && $now >= Carbon::parse($step->from)->timestamp )
                {
                    $status = $l == 'ar' ? 'يحدث اﻵن' : 'On going';
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
            $trType = $fLink = null;
            if ($company->approved) {
                $trType = 'my/' . $step->id;
            } else {
                $fLink = 'view/' . $cycle_id;
            }

            $step->status = $status;
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
                msr.rating AS `mentor_rating`
            FROM
                mentorship_requests mr
            LEFT JOIN
                users m ON m.id = mr.mentor_id
            LEFT JOIN
                users u ON u.id = mr.user_id
            LEFT JOIN
                companies c ON c.id = u.company_id
            LEFT JOIN
               mentorship_requests msr ON msr.mentor_id = m.id
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
                s.active
            FROM
                schedules s
            WHERE
                s.user_id = $user->id
        ";
        $my_schedules = collect(DB::select($my_schedules_q))->map(function ($schedule) {
            $schedule->active = $schedule->active == 1 || $schedule->active == true ? true : false;
            $date_from = Carbon::parse($schedule->dfrom)->format('d-m-Y');
            $date_to = Carbon::parse($schedule->dto)->format('d-m-Y');
            $_days = '';
            switch ($schedule->type) {
                case 'Weekly': {
                        $_days = 'Weekly (' . str_replace(['[', ']', '"'], ' ', json_encode($schedule->days)) . ')';
                        break;
                    }
                case 'WorkingDays': {
                        $_days = __('dashboard.All working days');
                        break;
                    }
                case 'Period': {
                        $_days = __('dashboard.from') . $date_from . __('dashboard.to') . $date_to;
                        break;
                    }
                default: {
                        $_days = $date_to;
                    }
            }
            $schedule->_days = $_days;
            $schedule->t_from = substr($schedule->tfrom, 0, 5);
            $schedule->t_to = substr($schedule->tto, 0, 5);
            $schedule->notes ? $schedule->notes : '...';
            return $schedule;
        });

        $setting = collect(DB::select("SELECT * FROM settings where `id` = 1 limit 1"))->first();

        if ($company && $cycle_id != 0) {
            $surveyEligible = [];
            foreach ($steps as $step) {
                if ((optional($step)->stype == 3 || optional($step)->stype == 4) && (now() > Carbon::parse($step->to)->format('Y-m-d H:i:s'))) {
                    $surveyEligible[] = [
                        'triggered_on' => $step->stype == 3 ? 'mentorship' : 'training',
                        'triggered_at' => $step->id,
                        'triggered_by' => 'due'
                    ];
                }
            }

            $my_mentorship_requests_q = "SELECT
                    mr.id,
                    mr.period,
                    mr.zoom_date
                FROM
                    mentorship_requests mr
                WHERE
                    mr.user_id = $user->id
                    AND mr.approved = 'Yes'
                    AND mr.cycle_id = $cycle_id
            ";
            $my_mentorship_requests = collect(DB::select($my_mentorship_requests_q))->map(function ($item) {
                $item->end_at = Carbon::parse($item->zoom_date)->addMinutes($item->period)->format('Y-m-d H:i:s');
                return $item;
            })->filter(function ($item) {
                return !empty($item->zoom_date) && !empty($item->period) && now() > $item->end_at;
            })->values();

            $my_trainings_q = "SELECT
                    ts.id,
                    ts.type,
                    ts.duration,
                    ts.datetime
                FROM
                    training_sessions ts
                INNER JOIN
                    steps s ON ts.step_id = s.id
                WHERE
                    s.cycle_id = $cycle_id;
            ";
            $my_trainings = collect(DB::select($my_trainings_q))->filter(function ($item) {
                if ($item->type == 'sessions' && (empty($item->datetime) || empty($item->duration))) {
                    return false;
                } elseif (in_array($item->type, ['online', 'offline', 'sessions']) && empty($item->datetime)) {
                    return false;
                } else {
                    return true;
                }
            })->values()->map(function ($item) {
                $item->end_at = null;
                if ($item->type == 'sessions') {
                    $item->end_at = Carbon::createFromTimestamp($item->datetime)->addMinutes($item->duration)->format('Y-m-d H:i:s');
                } elseif (in_array($item->type, ['online', 'offline', 'sessions'])) {
                    $item->end_at = FormatDate($item->datetime, 'Y-m-d H:i:s');
                }
                return $item;
            })->filter(function ($item) {
                return now() > $item->end_at;
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
                    quiz_results qr ON qr.quiz_id = q.id AND qr.user_id = $user->id
                WHERE
                    q.status = 'published'
                    AND q.organization_id = $organization_id
            ";
            $my_quizzes = collect(DB::select($my_quizzes_q))->groupBy('id')->map(function ($items) {
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
                if ($attempts->count() > 0) {
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

            foreach ($my_mentorship_requests as $mentorship) {
                $surveyEligible[] = [
                    'triggered_on' => 'mentorship',
                    'triggered_at' => $mentorship->id,
                    'triggered_by' => 'session'
                ];
            }

            foreach ($my_trainings as $session) {
                $surveyEligible[] = [
                    'triggered_on' => 'training',
                    'triggered_at' => $session->id,
                    'triggered_by' => 'session'
                ];
            }

            foreach ($my_quizzes as $quiz) {
                $surveyEligible[] = [
                    'triggered_on' => 'training',
                    'triggered_at' => $quiz->id,
                    'triggered_by' => 'quiz'
                ];
            }
            $surveysToAdd = [];
            $surveys = SurveyScopes::where('cycle_id', $cycle_id)->whereIn('type', ['startup', 'both'])->orWhereNull('type')->with(['myResponses'])->get();

            foreach ($surveys as $survey_scope) {
                $my_responses = $survey_scope->myResponses;
                if ($survey_scope->scope == 'mentorship' && array_key_exists('mentorship', $surveyEligible)) {
                    $due = $survey_scope->trigger_by_due;
                    $session = $survey_scope->trigger_by_session;
                    $mentorship = collect($surveyEligible['mentorship']);
                    $triggers = [];
                    if ($due == true) {
                        $triggers[] = 'due';
                    }
                    if ($session == true) {
                        $triggers[] = 'session';
                    }
                    $triggers = array_unique($triggers);
                    if (count($triggers) == 0) {
                        $mentorship = $mentorship->where('triggered_at', null);
                    } else {
                        $mentorship = $mentorship->whereIn('triggered_by', $triggers);
                    }
                    $mentorship = $mentorship->values();
                    foreach ($mentorship as $conditions) {
                        $checkResponse = $my_responses->where('triggered_on', $conditions['triggered_on'])->where('triggered_at', $conditions['triggered_at'])->where('triggered_by', $conditions['triggered_by'])->first();
                        if (empty($checkResponse)) {
                            $surveysToAdd[] = [
                                'user_id' => $user->id,
                                'survey_id' => $survey_scope->survey_id,
                                'cycle_id' => $cycle_id,
                                'scope_id' => $survey_scope->id,
                                'triggered_on' => $conditions['triggered_on'],
                                'triggered_at' => $conditions['triggered_at'],
                                'triggered_by' => $conditions['triggered_by'],
                                'responses' => Null
                            ];
                        }
                    }
                } elseif ($survey_scope->scope == 'training' && array_key_exists('training', $surveyEligible)) {
                    $due = $survey_scope->trigger_by_due;
                    $quiz = $survey_scope->trigger_by_quiz;
                    $session = $survey_scope->trigger_by_session;
                    $training = collect($surveyEligible['training']);
                    if ($due == true) {
                        $triggers[] = 'due';
                    }
                    if ($quiz == true) {
                        $triggers[] = 'quiz';
                    }
                    if ($session == true) {
                        $triggers[] = 'session';
                    }
                    $triggers = array_unique($triggers);
                    if (count($triggers) == 0) {
                        $training = $training->where('triggered_at', null);
                    } else {
                        $training = $training->whereIn('triggered_by', $triggers);
                    }
                    $training = $training->values();
                    foreach ($training as $conditions) {
                        $checkResponse = $my_responses->where('triggered_on', $conditions['triggered_on'])->where('triggered_at', $conditions['triggered_at'])->where('triggered_by', $conditions['triggered_by'])->first();
                        if (empty($checkResponse)) {
                            $surveysToAdd[] = [
                                'user_id' => $user->id,
                                'survey_id' => $survey_scope->survey_id,
                                'cycle_id' => $cycle_id,
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

            if (!empty($surveysToAdd)) {
                SurveyResponses::insert($surveysToAdd);
            }
        }

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
            return true;
        })->values();

        $data['my_schedules'] = $my_schedules;
        $data['upcoming_sessions'] = $upcoming_sessions;
        $data['cType'] = $cType;
        $data['myFinal'] = $myFinal;
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
                'href' => '/dashboard/users#search=Manager',
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
            if(Auth::user()->hasRole('Registered')){
            if(Auth::user()->company?->cycle != null){

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
                'href' => '/dashboard/show/Mentors',
                'icon' => 'ti ti-school',
                'text' => 'Mentors',
                'permissions' => ['mentors.list', 'dashboard.widgets_cards_my_mentors_view'],
                'count' => $my_mentors->count(),
                'isRating' => false,
            ];
            $data['countCards'][] = [
                'color' => 'danger',
                'href' => '#',
                'icon' => 'ti ti-star',
                'text' => 'Rate',
                'permissions' => ['dashboard.widgets_cards_rate_view'],
                'count' => $my_rating,
                'isRating' => true, // Only this card has the isRating key
            ];
        }
        }else{
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
                'href' => '/dashboard/show/Mentors',
                'icon' => 'ti ti-school',
                'text' => 'Mentors',
                'permissions' => ['mentors.list', 'dashboard.widgets_cards_my_mentors_view'],
                'count' => $my_mentors->count(),
                'isRating' => false,
            ];
            $data['countCards'][] = [
                'color' => 'danger',
                'href' => '#',
                'icon' => 'ti ti-star',
                'text' => 'Rate',
                'permissions' => ['dashboard.widgets_cards_rate_view'],
                'count' => $my_rating,
                'isRating' => true, // Only this card has the isRating key
            ];
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
            $data['countCards'][] = [
                'color' => 'danger',
                'href' => '/dashboard/mymsg',
                'icon' => 'ti-network',
                'text' => 'Contact us',
                'permissions' => ['messages.view', 'dashboard.widgets_cards_contact_us_view'],
                'count' => $messages->count(),
                'isRating' => false,
            ];
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
                'color' => 'info',
                'href' => '/dashboard/companies',
                'icon' => 'ti-building',
                'text' => 'Startups',
                'permissions' => ['users.view_companies', 'dashboard.widgets_cards_startups_view'],
                'count' => $companies->count(),
                'isRating' => false,
            ];
            $data['countCards'][] = [
                'color' => 'danger',
                'href' => '/dashboard/show/Mentors',
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
                'count' => $mentorship_requests->sum('period') / 60,
                'isRating' => false,
            ];
            $data['countCards'][] = [
                'color' => 'primary',
                'href' => '/dashboard/users#search=Judger',
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
                'count' => $filtration->count(),
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
        if ($has_org) {
            $country_data_q .= " WHERE u.organization_id = " . $user->organization_id;
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
        if ($has_org) {
            $conditions = " WHERE cy.organization_id = " . $user->organization_id;
        }
        $sector_data_q = "SELECT
                s.name_en AS `sector_name_en`,
                s.name_ar AS `sector_name_ar`,
                count(c.id) AS total
            FROM
                sectors s
            INNER JOIN
                companies c ON c.sector = s.id
            INNER JOIN
                cycles cy on c.cycle = cy.id
            $conditions
            GROUP BY
                s.name_en,
                s.name_ar
        ";

        $sector_data = collect(DB::select($sector_data_q))->map(function ($item) {
            return [
                'label_en' => $item->sector_name_en,
                'label_ar' => $item->sector_name_ar,
                'count' => $item->total,
            ];
        });

        if ($has_org) {
            $conditions = " AND u.organization_id = " . $user->organization_id;
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

        if ($has_org) {
            $conditions = " AND cy.organization_id = " . $user->organization_id;
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

        if ($has_org) {
            $conditions = " WHERE u.organization_id = " . $user->organization_id;
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

        if ($has_org) {
            $conditions = " AND u.organization_id = " . $user->organization_id;
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

        if ($has_org) {
            $conditions = " AND cy.organization_id = " . $user->organization_id;
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

        if ($has_org) {
            $conditions = " AND u.organization_id = " . $user->organization_id;
        }
        $sector_distribution_q = "SELECT
            c.sector,
            s.name_en,
            s.name_ar,
            count(c.id) AS total
        FROM 
            companies c
        INNER JOIN 
            cycles cy ON c.cycle = cy.id
        LEFT JOIN 
            sectors s ON (CAST(c.sector AS CHAR) = CAST(s.id AS CHAR))
                OR (c.sector = s.name_en)   
                OR (c.sector = s.name_ar)                     
        LEFT JOIN 
            users u ON cy.organization_id = u.organization_id
        WHERE
             c.deleted_at IS NULL
             AND c.sector IS NOT NULL
             $conditions
        GROUP BY
             c.sector, s.name_en, s.name_ar
        ";
    $sector_distribution = collect(DB::select($sector_distribution_q))->map(function ($item) {
        return [
            'sector_en' => $item->name_en ?? $item->sector ?? __('admin.undefined', [], 'en'),
            'sector_ar' => $item->name_ar ?? $item->sector ?? __('admin.undefined', [], 'ar'),
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
        if ($has_org) {
            $users_gov_q .= " WHERE u.organization_id = " . $user->organization_id;
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
        if ($has_org) {
            $users_gender_q .= " WHERE u.organization_id = " . $user->organization_id;
        }
        $users_gender_q .= " GROUP BY gender";
        $users_gender = collect(DB::select($users_gender_q));

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
            [
                'div-class' => 'col-12 col-md-6 mt-6',
                'card-title' => __('admin.Project_Status_Over_Time'),
                'type' => 'div',
                'id' => 'projectStatusOverTimeChart',
                'permissions' => ['dashboard.widgets_graphs_program_progress_view']
            ],
        ];

        $dateRate = date("Y-m-d 17:00:00");
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
        $data['notrated_mens'] = $notRated_men;
        $data['initial_screening_steps'] = $filtration;
        $data['final_screening_steps'] = $evaluation;

        if(Auth::user()->hasRole('Registered') || Auth::user()->hasRole('Mentor')){
            
        
                $user = Auth::user();
			if(isset($user->team_member) && $user->team_member->teamFormation->user->company->cycle != null){
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
                            if($cycle != null){
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
        $organization_id = Auth::user()->organization_id;
        $mentorshipRequests = MentorshipRequest::where('organization_id', $organization_id);
        $forms = BuilderForm::where('organization_id', $organization_id);
        $sessions = TrainingSession::whereHas('step', function ($q) use ($organization_id) {
            $q->where('stype', 4)->whereHas('cycle', function ($query) use ($organization_id) {
                $query->where('organization_id', $organization_id);
            });
        });
        $messages = Messenger::where('receiver', auth()->id())->whereNull('read');
        $startups = Company::whereHas('cycledata', function ($q) use ($organization_id) {
            $q->where('organization_id', $organization_id);
        });
        $cycles = Cycle::where('organization_id', $organization_id);

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
            ->where('users.organization_id', $organization_id)
            ->select(
                'states.name_en as state_en',
                'states.name_ar as state_ar',
                DB::raw('count(*) as total')
            )
            ->groupBy('states.name_en', 'states.name_ar')
            ->get();


        // Male and Female Ratio
        $usersgender = User::select('gender', DB::raw('count(*) as total'))->where('organization_id', $organization_id)->groupBy('gender')->get();
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
        $data['mysessions'] = auth()->user()->mentorSessions()->where('zoom_date', '>=', date("Y-m-d 00:00:00"))->where('approved', 'Yes')->get();
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
        $t = $request->input('type');
        $find = $request->input('find');
        $services = Service::whereNotNull('id');
        if ($t) {
            $services = $services->where('service_category_id', $t);
        }
        if ($find) {
            $services = $services->where(function ($query) use ($find) {
                $query->where('services.name_en', 'like', '%' . $find . '%')
                    ->orWhere('services.name_ar', 'like', '%' . $find . '%');
            });
        }
        $services = Service::whereHas('organizations')->get();
        $setting = Settings::where('id', 1)->first();
        $currency = Currency::where('id', $setting->Currency)->first();
        return view('admin.clients.directory')->with('services', $services)->with('setting', $setting)->with('currency', $currency);
    }
    public function rateservice($id, Request $request)
    {
        if ($request && $id) {
            $request->done ? $done = 1 : $done = null;
            ServicesRequests::updateOrInsert(['id' => $id], ['rate' => $request->rate, 'rate_comment' => $request->comment, 'done' => $done]);
            return back();
        }
        return back();
    }
    public function showone($id, Request $request)
    {
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
        if ($request) {
            $serviceRequest = ServicesRequests::create([
                'service_id' => $request->id,
                'user_id' => Auth::id(),
                'comment' => $request->comment,
                'date_time' => $request->datetime
            ]);

            event(new WebMessage('NewServ', Auth::user()->first_name_en . ' ' . Auth::user()->last_name_en));

            $service = Service::find($request->id);

            if ($service && $service->vendor_email) {
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
}
