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
        $targetMarket = Company::select('targeted_market', DB::raw('count(*) as frequency'))
            ->groupBy('targeted_market')
            ->orderByDesc('frequency')
            ->get();
        $data['target_market_labels'] = $targetMarket->pluck('targeted_market');
        $data['target_market_counts'] = $targetMarket->pluck('frequency');


        // Sector Breakdown
        $sectorBreakdown = Sector::withCount('children')->get();

        $data['sector_labels'] = $sectorBreakdown->pluck('name_en');
        $data['sector_counts'] = $sectorBreakdown->pluck('children_count');


        // Project Type Distribution
        $projectTypes = Company::select('business_type', DB::raw('count(*) as total'))
            ->groupBy('business_type')
            ->get();
        $data['project_type_labels'] = $projectTypes->pluck('business_type');
        $data['project_type_counts'] = $projectTypes->pluck('total');

        // Total Jobs Created
        $data['total_jobs_created'] = Company::sum('team_num');

        // Total Founders
        $data['total_founders'] = Company::whereNotNull('founder')->count();

        // Companies by SDG
        $companySdg = Company::select('Sustainable_development', DB::raw('count(*) as total'))
            ->groupBy('Sustainable_development')
            ->get();

        $data['sdg_labels'] = $companySdg->pluck('Sustainable_development');
        $data['sdg_counts'] = $companySdg->pluck('total');

        // Total Startups Per Organization
        $organizationsData = User::whereNotNull('organization_id')
            ->count();
        $data['organization_counts'] = $organizationsData;

        // Total Raised Funds
        $data['total_raised_funds'] = Company::sum('raised_fund');

        // User Distribution by Country
        $countryData = User::join('countries', 'users.country_id', '=', 'countries.id')
            ->select("countries.name_$l as country_name", DB::raw('count(users.id) as total'))
            ->groupBy("countries.name_$l")
            ->get();

        $data['country_labels'] = $countryData->pluck('country_name');
        $data['country_counts'] = $countryData->pluck('total');

        // Project Status
        $projectStatus = TimeSheet::join('cycles', 'program_id', '=', 'cycles.id')
            ->select('cycles.title as cycle_title', DB::raw('count(program_id) as total'))
            ->groupBy('cycles.title')
            ->get();

        $data['project_status'] = $projectStatus;

        $developmentStages = Company::select('development_stage', DB::raw('count(*) as total'))
            ->groupBy('development_stage')
            ->get();

        $data['development_stage_labels'] = $developmentStages->pluck('development_stage');
        $data['development_stage_counts'] = $developmentStages->pluck('total');

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
        $data['development_stage_labels'] = [];
        $data['development_stage_counts'] = [];
        foreach ($cycles->get() as $cycle) {
            $developmentStages = Company::select('development_stage', DB::raw('count(*) as total'))
                ->where('cycle', $cycle->id)
                ->groupBy('development_stage')
                ->get();

            foreach ($developmentStages as $stage) {
                $data['development_stage_labels'][] = $stage->development_stage;
                $data['development_stage_counts'][] = $stage->total;
            }
        }

        // Target Market Analysis
        $targetMarket = Company::select('targeted_market', DB::raw('count(*) as frequency'))
            ->groupBy('targeted_market')
            ->orderByDesc('frequency')
            ->get();
        $data['target_market_labels'] = $targetMarket->pluck('targeted_market');
        $data['target_market_counts'] = $targetMarket->pluck('frequency');

        // Sector Breakdown
        $sectorBreakdown = Sector::join('companies', 'companies.sector', '=', 'sectors.id')
            ->join('cycles', 'companies.cycle', '=', 'cycles.id')
            ->join('users', 'cycles.organization_id', '=', 'users.organization_id')
            ->where('users.organization_id', $organization_id)
            ->select('sectors.name_' . $l, DB::raw('count(companies.id) as total'))
            ->groupBy('sectors.name_' . $l)
            ->get();

        $data['sector_labels'] = $sectorBreakdown->pluck('name_en');
        $data['sector_counts'] = $sectorBreakdown->pluck('total');

        // Project Type Distribution
        $projectTypes = Company::join('cycles', 'companies.cycle', '=', 'cycles.id')
            ->join('users', 'cycles.organization_id', '=', 'users.organization_id')
            ->where('users.organization_id', $organization_id)
            ->select('companies.business_type', DB::raw('count(companies.id) as total'))
            ->groupBy('companies.business_type')
            ->get();

        $data['project_type_labels'] = $projectTypes->pluck('business_type');
        $data['project_type_counts'] = $projectTypes->pluck('total');


        // Total Jobs Created
        $data['total_jobs_created'] = Company::join('cycles', 'companies.cycle', '=', 'cycles.id')
            ->join('users', 'cycles.organization_id', '=', 'users.organization_id')
            ->where('users.organization_id', $organization_id)
            ->sum('companies.team_num');

        // Total Founders
        $data['total_founders'] = Company::join('cycles', 'companies.cycle', '=', 'cycles.id')
            ->join('users', 'cycles.organization_id', '=', 'users.organization_id')
            ->where('users.organization_id', $organization_id)
            ->whereNotNull('companies.founder')
            ->count();

        // Companies by SDG
        $companySdg = Company::whereHas('cycledata', function ($query) use ($organization_id) {
            $query->where('organization_id', $organization_id);
        })
            ->select('Sustainable_development', DB::raw('count(*) as total'))
            ->groupBy('Sustainable_development')
            ->get();

        $data['total_sdg'] = $companySdg->pluck('total');

        // Total Startups Per Organization
        $organizationsData = User::select('organization_id', DB::raw('count(*) as total'))
            ->whereNotNull('organization_id')
            ->groupBy('organization_id')
            ->get();

        $data['organization_counts'] = $organizationsData->mapWithKeys(function ($item) {
            return [$item->organization_id => $item->total];
        });

        // Total Raised Funds
        $data['total_raised_funds'] = Company::join('cycles', 'companies.cycle', '=', 'cycles.id')
            ->join('users', 'cycles.organization_id', '=', 'users.organization_id')
            ->where('users.organization_id', $organization_id)
            ->sum('companies.raised_fund');


        // User Distribution by Country
        $countryData = User::join('countries', 'users.country_id', '=', 'countries.id')
            ->where('organization_id', $organization_id)
            ->select("countries.name_$l as country_name", DB::raw('count(users.id) as total'))
            ->groupBy("countries.name_$l")
            ->get();

        $data['country_labels'] = $countryData->pluck('country_name');
        $data['country_counts'] = $countryData->pluck('total');

        // Project Status
        $projectStatus = TimeSheet::join('cycles', 'time_sheets.program_id', '=', 'cycles.id')
            ->join('users', 'cycles.organization_id', '=', 'users.organization_id')
            ->where('users.organization_id', $organization_id)
            ->select('cycles.title as cycle_title', DB::raw('count(time_sheets.program_id) as total'))
            ->groupBy('cycles.title')
            ->get();
        $data['project_status'] = $projectStatus;

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
