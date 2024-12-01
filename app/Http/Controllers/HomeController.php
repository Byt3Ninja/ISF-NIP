<?php

namespace App\Http\Controllers;

use App\Step;
use App\User;
use App\Cycle;
use App\Sector;
use App\Company;
use App\Service;
use App\Calendar;
use App\Messages;
use App\Schedule;
use App\Messenger;
use App\Screening;
use App\TimeSheet;
use App\BuilderForm;
use App\Organization;
use App\Documentation;
use App\Finalscreening;
use App\MentorshipTask;

use App\SustainableIcon;
use App\TrainingSession;
use App\ServicesRequests;
use App\Events\WebMessage;
use App\MentorshipRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;


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
        $user = auth()->user();
        if ($user->hasanyRole('Admin|Editor|Manager')) {

            return $this->AdminDashboard();
        } elseif ($user->hasRole('Registered|Page Manager|Pages Editor')) {

            return view('admin.clients.dashboard');
        } elseif ($user->hasRole('organizationManager')) {

            return $this->organizationDashboard();
        } elseif ($user->hasRole('Mentor')) {

            if (!Auth::user()->bio) {
                return redirect('/dashboard/myProfile#bio');
            }
            return $this->mentorDashboard();
        } elseif ($user->hasRole('Judger')) {

            return $this->judgerDashboard();
        } elseif ($user->hasRole('Disabled')) {

            return view('admin.clients.disabled');
        } else
            abort(401);
    }

    // ============> Functions of dashboards
    protected function adminDashboard()
    {
        $l = app()->getLocale();
        $startups =   Company::count();
        $messages = Messenger::where('receiver', Auth::user()->id)->whereNull('read')->count();
        $servrequests = ServicesRequests::whereNull('approved')->count();
        $mentrequests = new MentorshipRequest();
        $managers = User::join('model_has_roles', 'id', '=', 'model_id')->whereIn('role_id', [5, 1])->count();
        $cmessages = Messages::where('read', '<>', 1)->count();
        $mentors = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 6)->count();
        $judgers = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 7)->count();

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
            ->select('countries.name_' . $l . ' as country_name', DB::raw('COUNT(companies.id) as company_count'))
            ->leftJoin('companies', DB::raw('JSON_CONTAINS(companies.targeted_market, CONCAT(\'"\', countries.id, \'"\'), "$")'), '=', DB::raw('1'))
            ->groupBy('countries.id', 'countries.name_' . $l)
            ->orderByDesc('company_count')
            ->get();

        $data['target_market_data'] = $targetMarket->map(function ($item) {
            return [
                'label' => $item->country_name,
                'count' => $item->company_count,
            ];
        });

        // Sector Breakdown
        $sectorBreakdown = Sector::withCount('children')->get();

        $data['sector_data'] = $sectorBreakdown->map(function ($item) use ($l) {
            return [
                'label' => $item->{'name_' . $l},
                'count' => $item->children_count,
            ];
        });

        // Project Type Distribution
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
        $totalJobsCreated = Company::sum('team_num');

        $companyData = Company::select('name_' . $l, 'team_num')
            ->whereNotNull('team_num')
            ->get();

        $companies = $companyData->map(function ($company) use ($l) {
            return [
                'company_name' => $company->{'name_' . $l},
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


        // Companies By Sector
        $sectorData = Company::select('sector', DB::raw('count(*) as total'))
            ->whereNotNull('sector')
            ->groupBy('sector')
            ->get();
        $data['sector_distribution'] = $sectorData->map(function ($item) {
            return [
                'sector' => __('admin.' . $item->sector),
                'count' => $item->total,
            ];
        });


        // Companies by SDG
        $companySdg = Company::select('Sustainable_development', DB::raw('count(*) as total'))
            ->groupBy('Sustainable_development')
            ->get();

        $data['sdg_data'] = $companySdg->flatMap(function ($item) {
            $sdgs = json_decode($item->Sustainable_development);

            return collect($sdgs)->map(function ($sdg) use ($item) {
                return [
                    'sdg' => __('admin.' . $sdg),
                    'total' => $item->total,
                ];
            });
        });

        // Total Startups Per Organization
        $organizationsData = User::join('organizations', 'users.organization_id', '=', 'organizations.id')
            ->join('cycles', 'users.organization_id', '=', 'cycles.organization_id')
            ->join('companies', 'companies.cycle', '=', 'cycles.id')
            ->select('organizations.name_' . $l . ' as organization_name', DB::raw('count(companies.id) as total_startups'))
            ->groupBy('organizations.name_' . $l)
            ->get();

        $data['organization_counts'] = $organizationsData->map(function ($item) {
            return [
                'organization_name' => $item->organization_name,
                'total_startups' => $item->total_startups,
            ];
        });


        // Total Raised Funds
        $totalRaisedFunds = Company::sum('raised_fund');
        $companyData = Company::select('name_' . $l, 'raised_fund')->whereNotNull('raised_fund')
            ->get();

        $companies = $companyData->map(function ($company) use ($l) {
            return [
                'company_name' => $company->{'name_' . $l},
                'raised_fund' => $company->raised_fund,
            ];
        });

        $data['total_raised_funds'] = [
            'total' => $totalRaisedFunds,
            'companies' => $companies,
        ];


        // User Distribution by Country
        $countryData = User::join('countries', 'users.country_id', '=', 'countries.id')
            ->select("countries.name_$l as country_name", DB::raw('count(users.id) as total'))
            ->groupBy("countries.name_$l")
            ->get();

        $data['countries'] = $countryData->map(function ($item) {
            return [
                'label' => $item->country_name,
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
            $count = $developmentStages->where('development_stage', $item->development_stage)->count();  // حساب عدد العناصر في نفس المرحلة
            return [
                // 'label' => __('admin.' . $item->development_stage),
                'label' => $item->development_stage,
                'count' => $count,
            ];
        });


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


        // Development Stage  

        $data['development_stages'] = [];

        foreach ($cycles->get() as $cycle) {
            $developmentStages = Company::where('cycle', $cycle->id)
                ->whereNotNull('development_stage')
                ->select('development_stage', DB::raw('count(*) as total'))
                ->groupBy('development_stage')
                ->get();

            foreach ($developmentStages as $stage) {
                $data['development_stages'][] = [
                    'label' => __('admin.' . $stage->development_stage),
                    'count' => $stage->total,
                ];
            }
        }

        // Target Market Analysis
        $targetMarket = DB::table('countries')
            ->select('countries.name_' . $l . ' as country_name', DB::raw('COUNT(companies.id) as company_count'))
            ->leftJoin('companies', DB::raw('JSON_CONTAINS(companies.targeted_market, CONCAT(\'"\', countries.id, \'"\'), "$")'), '=', DB::raw('1'))
            ->join('cycles', 'companies.cycle', '=', 'cycles.id')
            ->join('users', 'cycles.organization_id', '=', 'users.organization_id')
            ->where('users.organization_id', $organization_id)
            ->groupBy('countries.id', 'countries.name_' . $l)
            ->orderByDesc('company_count')
            ->get();

        $data['target_market_data'] = $targetMarket->map(function ($item) {
            return [
                'label' => $item->country_name,
                'count' => $item->company_count,
            ];
        });

        // Sector Breakdown
        $sectorBreakdown = Sector::join('companies', 'companies.sector', '=', 'sectors.id')
            ->join('cycles', 'companies.cycle', '=', 'cycles.id')
            ->join('users', 'cycles.organization_id', '=', 'users.organization_id')
            ->where('users.organization_id', $organization_id)
            ->select('sectors.name_' . $l, DB::raw('count(companies.id) as total'))
            ->groupBy('sectors.name_' . $l)
            ->get();

        $data['sector_data'] = $sectorBreakdown->map(function ($item) use ($l) {
            return [
                'label' => $item->{'name_' . $l},
                'count' => $item->total,
            ];
        });

        // Project Type Distribution
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
            ->select('companies.name_' . $l, 'companies.team_num')
            ->whereNotNull('companies.team_num')
            ->get();

        $companies = $companyData->map(function ($company) use ($l) {
            return [
                'company_name' => $company->{'name_' . $l},
                'team_members' => $company->team_num,
            ];
        });

        $totalJobsCreated = $companyData->sum('team_num');

        $data['total_jobs_created'] = [
            'total' => $totalJobsCreated,
            'companies' => $companies,
        ];


        // Total Founders
        $companyData = Company::join('cycles', 'companies.cycle', '=', 'cycles.id')
            ->join('users', 'cycles.organization_id', '=', 'users.organization_id')
            ->where('users.organization_id', $organization_id)
            ->whereNotNull('companies.founder')
            ->select('companies.name_' . $l, 'companies.founder')
            ->get();

        $companies = $companyData->map(function ($company) use ($l) {
            return [
                'company_name' => $company->{'name_' . $l},
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
            ->select('Sustainable_development', DB::raw('count(*) as total'))
            ->groupBy('Sustainable_development')
            ->get();

        $data['sdg_data'] = $companySdg->flatMap(function ($item) {
            $sdgs = json_decode($item->Sustainable_development, true);

            return collect($sdgs)->map(function ($sdg) use ($item) {
                return [
                    'sdg' => __('admin.' . $sdg),
                    'total' => $item->total,
                ];
            });
        });

        // Total Startups Per Organization
        $organizationStartups = User::join('organizations', 'users.organization_id', '=', 'organizations.id')
            ->join('cycles', 'users.organization_id', '=', 'cycles.organization_id')
            ->join('companies', 'companies.cycle', '=', 'cycles.id')
            ->where('users.organization_id', $organization_id)
            ->select('organizations.name_' . $l . ' as organization_name', DB::raw('count(companies.id) as total_startups'))
            ->groupBy('organizations.name_' . $l)
            ->first();

        $data['organization_counts'] = [
            'organization_name' => $organizationStartups ? $organizationStartups->organization_name : null,
            'total_startups' => $organizationStartups ? $organizationStartups->total_startups : 0,
        ];


        // Total Raised Funds

        $totalRaisedFunds = Company::join('cycles', 'companies.cycle', '=', 'cycles.id')
            ->join('users', 'cycles.organization_id', '=', 'users.organization_id')
            ->where('users.organization_id', $organization_id)
            ->sum('companies.raised_fund');

        $companyData = Company::join('cycles', 'companies.cycle', '=', 'cycles.id')
            ->join('users', 'cycles.organization_id', '=', 'users.organization_id')
            ->where('users.organization_id', $organization_id)
            ->select('companies.name_' . $l, 'companies.raised_fund')
            ->whereNotNull('companies.raised_fund')
            ->get();

        $companies = $companyData->map(function ($company) use ($l) {
            return [
                'company_name' => $company->{'name_' . $l},
                'raised_fund' => $company->raised_fund,
            ];
        });


        $data['total_raised_funds'] = [
            'total' => $totalRaisedFunds,
            'companies' => $companies,
        ];


        // User Distribution by Country
        $countryData = User::join('countries', 'users.country_id', '=', 'countries.id')
            ->where('organization_id', $organization_id)
            ->select("countries.name_$l as country_name", DB::raw('count(users.id) as total'))
            ->groupBy("countries.name_$l")
            ->get();

        $data['countries'] = $countryData->map(function ($item) {
            return [
                'label' => $item->country_name,
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

        $data['sector_distribution'] =   $sectorData->map(function ($sector) {
            return [
                'sector' => __('admin.' . $sector->sector),
                'count' => $sector->total,
            ];
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
        $services = $services->get();
        $Setting = \App\Settings::where('id', 1)->first();
        return view('admin.clients.directory')->with('services', $services)->with('Setting', $Setting);
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
            ServicesRequests::create(['service_id' => $request->id, 'user_id' => Auth::id(), 'comment' => $request->comment, 'date_time' => $request->datetime]);
            event(new WebMessage('NewServ', Auth::user()->first_name_en . ' ' . Auth::user()->last_name_en));
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
        if (Auth::user()->hasanyRole('Admin|Manager')) {
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
