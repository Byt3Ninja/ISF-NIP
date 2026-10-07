<?php

namespace App\Http\Controllers;

use App\Step;
use App\User;
use App\Cycle;
use App\Stage;
use App\Sector;
use App\Company;
use App\FinalData;
use App\CompanyRelation;
use App\UserScore;
use App\Organization;
use App\Finalscreening;
use App\SurveyResponses;
use App\CyclesMentors;
use App\TrainingSession;
use App\MentorshipRequest;
use App\SessionReport;
use App\ScreeningEvaluation;
use App\Enums\CycleModifyAction;
use App\Services\TimezoneService;
use App\Jobs\ExportCycleData;
use App\Notifications\TemplateEmail;
use App\ScreeningEvaluationResults;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use URL, stdClass;

class ProgramController extends Controller
{
  
    // Keep DESCRIPTION_MAX in sync with the blades ($descMax) and app-edit-program.js
    private const DESCRIPTION_MAX = 100;
    private const LOGO_RULE = 'nullable|image|mimes:jpg,jpeg,png,gif,webp|max:2048';
    private const HEX_COLOR_RULE = 'regex:/^#?[0-9a-fA-F]{6}([0-9a-fA-F]{2})?$/';
    // TODO: derive from the organization / logged-in user instead of a hard-coded incubator
    private const DEFAULT_INCUBATOR_ID = 1;

    public function exportProg(Request $request)
    {
        // تحقق من أن البريد الإلكتروني موجود
        $userEmail = auth()->user()->email;

        // إرسال وظيفة (Job) لتحميل البيانات وإرسال البريد
        ExportCycleData::dispatch($userEmail);

        return response()->json(['message' => 'Export process started. You will receive an email with the download attached.']);
    }


    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('search');

        if ($user->hasRole('Admin')) {
            $cycles = Cycle::when($search, function ($query, $search) {
                return $query->where('title', 'like', '%' . $search . '%');
            });
        } elseif ($user->hasAnyRole(['organizationManager', 'subadmin'])) {
            $orgIds = $user->organizations_ids;
            $cycles = Cycle::when(!empty($orgIds), function ($q) use ($orgIds) {
                return $q->whereIn('organization_id', $orgIds);
            })->when($search, function ($query, $search) {
                return $query->where('title', 'like', '%' . $search . '%');
            });
        } else {
            $Organization = Organization::where('user_id', $user->id)->first();

            if (!empty($Organization)) {
                $cycles = Cycle::where('organization_id', $Organization->id)
                    ->when($search, function ($query, $search) {
                        return $query->where('title', 'like', '%' . $search . '%');
                    });
            } else {
                $cycles = Cycle::where('id', 0);
            }
        }

        if ($request->organization_id != 0) {
            $cycles = $cycles->where('organization_id', $request->organization_id);
        }
        if ($request->start_date != null) {
            $cycles = $cycles->where('start', '>=', $request->start_date);
        }
        if ($request->end_date != null) {
            $cycles = $cycles->where('end', '<=', $request->end_date);
        }
        if ($request->private == 'Yes') {
            $cycles = $cycles->whereNotNull('private');
        }
        if ($request->private == 'No') {
            $cycles = $cycles->whereNull('private');
        }
        if ($request->filled('program_type')) {
            $cycles->where('type', $request->program_type);
        }

        // 📌 Use paginate instead of get()
        $cycles = $cycles->with(['steps', 'companies', 'mentors'])->orderBy('id', 'desc')
            ->with('organization')
            ->paginate(9)                      // ← paginate 9 per page
            ->withQueryString()
            ->through(function ($cycle) {      // ← apply mapping AFTER pagination
                $cycle->join_url = null;

                if ($cycle->is_public != 0) {
                    $org = $cycle->organization;
                    $slug = $cycle->public_slug;

                    if (!empty($slug) && trim($slug) && !empty($org)) {
                        $short_code = $org->short_code;
                        $cycle->join_url = URL::to("/$short_code/program/$slug");
                    }
                }
                return $cycle;
            });

        return view('admin.programs.index', compact('cycles'));
    }

    
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('search'); // التحقق من وجود البحث في الطلب

        if ($user->hasRole('Admin')) {
            // إذا كان المستخدم Admin
            $cycles = Cycle::when($search, function ($query, $search) {
                return $query->where('title', 'like', '%' . $search . '%'); // بحث بناءً على title
            });
        } elseif ($user->hasAnyRole(['organizationManager', 'subadmin'])) {
            $orgIds = $user->organizations_ids;
            $cycles = Cycle::when(!empty($orgIds), function ($q) use ($orgIds) {
                return $q->whereIn('organization_id', $orgIds);
            })->when($search, function ($query, $search) {
                return $query->where('title', 'like', '%' . $search . '%'); // بحث بناءً على title
            });
        } else {
            // إذا كان المستخدم ليس Admin
            $Organization = Organization::where('user_id', $user->id)->first();

            if (!empty($Organization)) {
                $cycles = Cycle::where('organization_id', $Organization->id)
                    ->when($search, function ($query, $search) {
                        return $query->where('title', 'like', '%' . $search . '%'); // بحث بناءً على title
                    });
            } else {
                $cycles = Cycle::where('id', 0); // لا توجد دورات إذا لم يكن لدى المستخدم منظمة
            }
        }
        if ($request->organization_id != 0) {
            $cycles = $cycles->where('organization_id', $request->organization_id);
        }
        if ($request->start_date != null) {
            $cycles = $cycles->where('start', '>=', $request->start_date);
        }
        if ($request->end_date != null) {
            $cycles = $cycles->where('end', '<=', $request->end_date);
        }
        if ($request->private == 'Yes') {
            $cycles = $cycles->whereNotNull('private');
        }
        if ($request->private == 'No') {
            $cycles = $cycles->whereNull('private');
        }
        $cycles = $cycles->get();
        return view('admin.programs.create', compact('cycles'));
    }
 
     /**
     * Wizard steps 1-2 (General Info + optionally Schedule & Media).
     * Routes: programs.general (create) / programs.generalEdit (update).
     * A program is only ever created here, on the first id-less POST — every
     * later call (with an id) edits the same row, never inserts a new one.
     */
    public function store(Request $request)
    {
        $cycle = null;
        if ($request->route('id')) {
            $cycle = Cycle::find($request->route('id'));
            if (!$cycle) {
                return $this->jsonError('Program not found.', 404);
            }
            if (!$this->canManageOrganization($cycle->organization_id)) {
                return $this->jsonError('You are not allowed to manage this program.', 403);
            }
        }

        $scheduleProvided = $request->has('programDateRange') || $request->has('programStepType') || $request->hasFile('programLogo');
        $contentProvided = $request->has('programDescription') || $request->has('programDescriptionAr');

        $rules = [
            'programOrganization' => 'required|integer|exists:organizations,id',
            'programType' => 'required|in:hackathon,general',
            'programTitle' => 'required|string|min:3|max:50',
            'programTitleAr' => 'required|string|min:3|max:50',
            'programCohort' => 'nullable|string|max:20',
        ];
        if ($scheduleProvided) {
            $rules += $this->scheduleRules();
        }
        if ($contentProvided) {
            $rules += $this->contentRules();
        }
        $request->validate($rules);

        $organizationId = (int) $request->input('programOrganization');
        if (!$this->canManageOrganization($organizationId)) {
            return $this->jsonError('You are not allowed to use this organization.', 403);
        }

        $isNewProgram = !$cycle;

        $extra = [];
        DB::transaction(function () use ($request, &$cycle, $organizationId, $scheduleProvided, $contentProvided, &$extra) {
            $general = [
                'organization_id' => $organizationId,
                'type' => $request->input('programType') === 'hackathon' ? 'Hackathon' : 'General_Program',
                'title' => trim($request->input('programTitle')),
                'title_ar' => trim($request->input('programTitleAr')),
                'cohort' => $request->filled('programCohort') ? trim($request->input('programCohort')) : null,
            ];

            if ($cycle) {
                $organizationChanged = (int) $cycle->organization_id !== $organizationId;
                $cycle->forceFill($general);
            } else {
                $organizationChanged = false;
                $cycle = new Cycle();
                $cycle->forceFill($general + ['incubator_id' => self::DEFAULT_INCUBATOR_ID]);
            }

            if ($scheduleProvided) {
                $extra = $this->applyProgramSchedule($request, $cycle);
            }
            if ($contentProvided) {
                $this->applyProgramContent($request, $cycle);
            }
            $cycle->save();

            if ($organizationChanged) {
                DB::table('mentorship_schedules')
                    ->where('cycle_id', $cycle->id)
                    ->update(['organization_id' => $organizationId, 'updated_at' => now()]);
            }
        });

        if ($isNewProgram) {
            app(\App\Services\ProgramCreatedNotifier::class)->notify($cycle);
        }

        return response()->json(array_merge([
            'success' => true,
            'id' => $cycle->id,
            'message' => 'Program saved successfully.',
        ], $extra));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth()->user();
        $orgIds = $user->organizations_ids;
        $cycle = Cycle::where('id', $id);
        if ($user->hasAnyRole(['organizationManager', 'subadmin'])) {
            $cycle = $cycle->when(!empty($orgIds), function ($q) use ($orgIds) {
                return $q->whereIn('organization_id', $orgIds);
            });
        }
        $cycle = $cycle->first();
        if (empty($cycle)) return redirect()->route('programlist.index')->with(['message2' => 'program_not_found', 'class' => 'danger']);
        $steps = Step::where('cycle_id', $cycle->id)->orderBy('arr', 'ASC')->get();

        // $id = $program->pluck('id')[0];
        //         $steps = Step::where('cycle_id', $id)->orderBy('arr','ASC')->get();

        // $judges = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 7)->get();
		$judges = User::join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
			->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
			->where('roles.default_for', 'judger')
			->select('users.*')
			->get();

        $stepsData = [];
        foreach ($steps as $step) {
            $stepsData[] = [
                'step_id' => $step->id,
                'total_companies' => Company::where('step', $step->id)->count(),
            ];
        }

        // --- Step aggregates ---
        $totalSteps       = Step::where('cycle_id', $cycle->id)->count();
        // GV-9566: a step runs through its `to` day in the program's platform zone.
        $totalStepsFinish = Step::where('cycle_id', $cycle->id)
                                ->where('to', '>=', TimezoneService::platformToday(TimezoneService::platform($cycle->organization_id)))->count();
        $presSteps        = $totalSteps > 0
                                ? round(($totalStepsFinish / $totalSteps) * 100, 2)
                                : 0;

        // --- Company / Mentor aggregates ---
        $totalCompany = Company::where('cycle', $cycle->id)->count();
        $mentors      = $cycle->mentors()->distinct()->get();

        // --- Mentorship aggregates ---
        $totalMentorRequest = MentorshipRequest::where('cycle_id', $cycle->id)->count();
        $totalMentorMinutes = MentorshipRequest::where('cycle_id', $cycle->id)->sum('period');
        $totalMentorperiod  = floor($totalMentorMinutes / 60) . ' h ' . ($totalMentorMinutes % 60) . ' m';

        $mentorshipRequests  = MentorshipRequest::where('cycle_id', $cycle->id)->get();
        $sessionReportsCount = SessionReport::whereIn(
            'mentorship_request_id', $mentorshipRequests->pluck('id')
        )->count();
        $totalMentorrating = $mentorshipRequests->count() > 0
            ? ($sessionReportsCount / $mentorshipRequests->count()) * 100
            : 0;

        // --- Training / evaluation aggregates (across all steps) ---
        $totalStepsIdsCount   = 0;
        $totalDurationMinutes = 0;
        $answersTotal         = 0;
        $answersPending       = 0;

        foreach ($steps as $step) {
            $totalStepsIdsCount   += TrainingSession::where('step_id', $step->id)->count();
            $totalDurationMinutes += TrainingSession::where('step_id', $step->id)->sum('duration');
            $answersTotal         += FinalData::where('step_id', $step->id)->count();
            $answersPending       += FinalData::where('step_id', $step->id)->whereNull('answer')->count();
        }

        $totalDurationSum = floor($totalDurationMinutes / 60) . ' h ' . ($totalDurationMinutes % 60) . ' m';

        // --- Summary stat cards ---
        $summaryCards = [
            [__('admin.Startups'),                             'building-skyscraper', $totalCompany,                              'primary'],
            [__('cycles.steps'),                               'stack-2',             $totalSteps,                                'info'],
            [__('admin.Mentors'),                              'user',                $mentors->count(),                          'secondary'],
            [__('cycles.All requests'),                        'report',              $totalMentorRequest,                        'warning'],
            [__('admin.Mentorship_sessions_duration_(hours)'), 'clock-hour-5',        $totalMentorperiod,                         'success'],
            [__('admin.Company_Attendance_Rate'),              'heart-rate-monitor',  number_format($totalMentorrating, 2) . '%', 'danger'],
            [__('admin.Training sessions'),                    'layers-subtract',     $totalStepsIdsCount,                        'secondary'],
            [__('admin.Startups_trainings_Hours'),             'clock-hour-5',        $totalDurationSum,                          'success'],
            [__('admin.Traditional_Evaluation_pending'),       'transform',           $answersPending,                            'warning'],
            [__('admin.Evaluation'),                           'presentation',        $answersTotal,                              'danger'],
        ];

        // --- Per-step aggregates (keyed by step id), computed in step order
        // so that stype 5's cumulative judges list matches each step's position. ---
        $stepsExtra    = [];
        $judgesDetails = collect();

        foreach ($steps as $step) {
            switch ($step->stype) {
                case 1:
                    $stepsExtra[$step->id] = [
                        'companiesInStep' => Company::where('step', $step->id)->count(),
                    ];
                    break;

                case 2:
                    $scopedJudges      = $judges->whereIn('id', json_decode($step->list, true) ?? []);
                    $screenCompanies   = Company::where('step', $step->id)->get();
                    $totalFiltration   = 0;
                    $filtrationPending = 0;
                    $filtrationDone    = 0;

                    foreach ($screenCompanies as $company) {
                        $base               = FinalData::where('step_id', $step->id)
                                                        ->where('company_id', $company->id);
                        $totalFiltration   += (clone $base)->count();
                        $filtrationPending += (clone $base)->whereNull('answer')->count();
                        $filtrationDone    += (clone $base)->whereNotNull('answer')->count();
                    }

                    $stepsExtra[$step->id] = compact(
                        'scopedJudges', 'screenCompanies', 'totalFiltration', 'filtrationPending', 'filtrationDone'
                    );
                    break;

                case 3:
                    $stepsExtra[$step->id] = [
                        'mentorCount'      => CyclesMentors::where('cycle_id', $step->cycle_id)->count(),
                        'sessionMinutes'   => MentorshipRequest::where('cycle_id', $step->cycle_id)
                                                    ->where('approved', 'Yes')->sum('period'),
                        'doneSessions'     => MentorshipRequest::where('cycle_id', $step->cycle_id)
                                                    ->whereNotNull('done')->count(),
                        'allRequests'      => MentorshipRequest::where('cycle_id', $step->cycle_id)->count(),
                        'newRequests'      => MentorshipRequest::where('cycle_id', $step->cycle_id)
                                                    ->whereNull('approved')->count(),
                        'approvedRequests' => MentorshipRequest::where('cycle_id', $step->cycle_id)
                                                    ->where('approved', 'Yes')->count(),
                        'rejectedRequests' => MentorshipRequest::where('cycle_id', $step->cycle_id)
                                                    ->where('approved', 'No')->count(),
                    ];
                    break;

                case 4:
                    $stepsExtra[$step->id] = [
                        'trainingCount'    => TrainingSession::where('step_id', $step->id)->count(),
                        'trainingMinutes'  => TrainingSession::where('step_id', $step->id)->sum('duration'),
                        'sessionTypeCount' => TrainingSession::where('step_id', $step->id)->where('type', 'sessions')->count(),
                        'onlineCount'      => TrainingSession::where('step_id', $step->id)->where('type', 'online')->count(),
                        'offlineCount'     => TrainingSession::where('step_id', $step->id)->where('type', 'offline')->count(),
                    ];
                    break;

                case 5:
                    $finalScreening    = Finalscreening::where('step_id', $step->id)->first();
                    $finalCompanyCount = Finalscreening::where('step_id', $step->id)
                                             ->distinct('company_id')->count('company_id');
                    $finalAnswers      = FinalData::where('step_id', $step->id)
                                             ->whereNotNull('answer')->count();
                    $finalPending      = FinalData::where('step_id', $step->id)
                                             ->whereNull('answer')->count();

                    if ($finalScreening && !empty($finalScreening->judges)) {
                        $judgeIds      = is_string($finalScreening->judges)
                                             ? json_decode($finalScreening->judges, true)
                                             : $finalScreening->judges;
                        $stepJudges    = User::whereIn('id', $judgeIds)->get();
                        $judgesDetails = $judgesDetails->merge($stepJudges)->unique('id');
                    }

                    $stepsExtra[$step->id] = compact(
                        'finalCompanyCount', 'finalAnswers', 'finalPending'
                    ) + ['judgesDetails' => $judgesDetails];
                    break;
            }
        }

        $updateStepAction = CycleModifyAction::UPDATE_STEP;
        $deleteStepAction = CycleModifyAction::DELETE_STEP;
        $fetchStepAction  = CycleModifyAction::FETCH_STEP;

        return view('admin.programs.show', compact(
            'cycle', 'steps', 'judges', 'stepsData',
            'totalSteps', 'totalStepsFinish', 'presSteps',
            'totalCompany', 'mentors',
            'summaryCards', 'stepsExtra',
            'updateStepAction', 'deleteStepAction', 'fetchStepAction'
        ));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
       
        $user = auth()->user();
        $search = $request->input('search'); // التحقق من وجود البحث في الطلب

        if ($user->hasRole('Admin')) {
            // إذا كان المستخدم Admin
            $cycles = Cycle::when($search, function ($query, $search) {
                return $query->where('title', 'like', '%' . $search . '%'); // بحث بناءً على title
            });
        } elseif ($user->hasAnyRole(['organizationManager', 'subadmin'])) {
            $orgIds = $user->organizations_ids;
            $cycles = Cycle::when(!empty($orgIds), function ($q) use ($orgIds) {
                return $q->whereIn('organization_id', $orgIds);
            })->when($search, function ($query, $search) {
                return $query->where('title', 'like', '%' . $search . '%'); // بحث بناءً على title
            });
        } else {
            // إذا كان المستخدم ليس Admin
            $Organization = Organization::where('user_id', $user->id)->first();

            if (!empty($Organization)) {
                $cycles = Cycle::where('organization_id', $Organization->id)
                    ->when($search, function ($query, $search) {
                        return $query->where('title', 'like', '%' . $search . '%'); // بحث بناءً على title
                    });
            } else {
                $cycles = Cycle::where('id', 0); // لا توجد دورات إذا لم يكن لدى المستخدم منظمة
            }
        }
        if ($request->organization_id != 0) {
            $cycles = $cycles->where('organization_id', $request->organization_id);
        }
        if ($request->start_date != null) {
            $cycles = $cycles->where('start', '>=', $request->start_date);
        }
        if ($request->end_date != null) {
            $cycles = $cycles->where('end', '<=', $request->end_date);
        }
        if ($request->private == 'Yes') {
            $cycles = $cycles->whereNotNull('private');
        }
        if ($request->private == 'No') {
            $cycles = $cycles->whereNull('private');
        }
        $cycles = $cycles->with(['custom_pages'])->get();
        $id = request()->route('id');
        $ccycle = $cycles->where('id', $id)->first();
        if( empty($ccycle) ) { return redirect()->route('programlist.index')->with(['message2' => 'program_not_found', 'class' => 'danger']); }
        $final_step = $ccycle->steps->where('stype', 5)->values()->last();
        $results = collect([]);
        if( !empty( $final_step ) )
        {
            $step_id = $final_step->id;
            $evaluationForm = ScreeningEvaluation::getEvaluationData( $step_id );
            $metadata = ScreeningEvaluationResults::metadata( $step_id );

            $judges = $metadata['judges'];
            $evaluation = $metadata['evaluation'];
            $_companies = $metadata['_companies'];
            foreach( $_companies as $index => $company )
            {
                $totalScore = 0;
                $averageScore = 0;
                $hasDraft = false;
                $hasPublished = false;
                $hasNotEvaluated = false;
                $evaluationStatuses = [];
                $publishedEvaluations = 0;

                $filterScore = 0;
                $filterTotalScore = 0;
                $filterEvaluationsCount = 0;
                foreach( $judges as $judge )
                {
                    if( in_array($company->id, $judge->startups->toArray()) )
                    {
                        $scoped_company = collect($judge->companies)->where('id', $company->id)->first();
                        if( $scoped_company )
                        {
                            $status = strtolower($scoped_company->status ?? 'not-evaluated');
                            $evaluationStatuses[] = $status;

                            if ($status === 'published') $hasPublished = true;
                            elseif ($status === 'draft') $hasDraft = true;
                            else $hasNotEvaluated = true;

                            if( strtolower($status) === 'published' && isset( $scoped_company->score ) )
                            {
                                $totalScore += (float) $scoped_company->score;
                                $publishedEvaluations++;
                            }
                            if( strtolower($status) === 'draft' && isset( $scoped_company->score ) )
                            {
                                $filterTotalScore += (float) $scoped_company->score;
                                $filterEvaluationsCount++;
                            }
                        }
                    }
                }
												
                $primaryStatus = 'not-evaluated';
                if ($hasPublished && !$hasDraft && !$hasNotEvaluated) {
                    $primaryStatus = 'published';
                } elseif ($hasDraft) {
                    $primaryStatus = 'draft';
                } elseif ($hasPublished && $hasNotEvaluated) {
                    $primaryStatus = 'mixed'; // Has both published and not evaluated
                }
                if ($publishedEvaluations > 0) {
                    $averageScore = round($totalScore / $publishedEvaluations, 1);
                }
                $_companies[$index]->totalScore = $averageScore;
                $_companies[$index]->primaryStatus = $primaryStatus;
                $_companies[$index]->filterTotalScore = $filterTotalScore;
                $_companies[$index]->filterEvaluationsCount = $filterEvaluationsCount;
                $_companies[$index]->users_ids = $company->users->pluck('id')->toArray();
            }
            $users_ids = $_companies->pluck('users_ids')->flatten()->unique()->toArray();
            $teams = DB::table('team_formations')
                ->whereIn('user_id', $users_ids)
                ->select('id', 'name', 'user_id', 'project_name_ar', 'project_name_en')
                ->get()
                ->groupBy('company_id');


            $l = app()->getLocale();
            $ranks = $results = $_companies->map(function($company) {
                $rank = new stdClass;
                $rank->id = $company->id;
                $rank->score = $company->average_score;
                return $rank;
            })->sortByDesc('score')->values()->map(function($rank, $index) {
                $rank->rank = $index + 1;
                return $rank;
            })->keyBy('id')->toArray();
            $results = $_companies->map(function ($company) use ( $teams, $l, $ranks ) {
                $users_ids = $company->users_ids;
                $matched_team = $teams->first(function ($team) use ($users_ids) {
                    return in_array($team->first()->user_id, $users_ids);
                });
                if( $matched_team ) { $matched_team = $matched_team->first(); }
                $team = new stdClass;
                $team->id = $company->id;
                $team->name =  $matched_team ? $matched_team->name : $company->{'name_' . $l};
                $team->score = $company->average_score;
                $team->status = $company->primaryStatus;                
                $team->project = $matched_team ? $matched_team->{'project_name_' . $l} : '-';
                $team->rank = isset($ranks[$company->id]) ? $ranks[$company->id]->rank : null;
                if( $team->score == 0 ) { $team->rank = null; }
                return $team;
            });
        }
        return view('admin.programs.edit', compact('cycles', 'ccycle', 'id', 'results'));
    }

    /**
     * Wizard steps 2-5 (Schedule / Mentorship / Landing / Publish — one route each, see routes).
     * Requires an existing program; never creates one.
     */
    public function update(Request $request, string $id)
    {
        $cycle = Cycle::find($id);
        if (!$cycle) {
            return $this->jsonError('Program not found.', 404);
        }
        if (!$this->canManageOrganization($cycle->organization_id)) {
            return $this->jsonError('You are not allowed to manage this program.', 403);
        }

        $handlers = [
            'programs.schedule' => 'updateProgramSchedule',
            'programs.mentorship' => 'updateProgramMentorship',
            'programs.landing' => 'updateProgramLanding',
            'programs.publish' => 'updateProgramPublish',
        ];
        $routeName = $request->route()->getName();
        if (!isset($handlers[$routeName])) {
            return $this->jsonError('Unknown program step.', 400);
        }

        $extra = DB::transaction(function () use ($handlers, $routeName, $request, $cycle) {
            return $this->{$handlers[$routeName]}($request, $cycle);
        });

        return response()->json(array_merge([
            'success' => true,
            'message' => 'Program saved successfully.',
        ], (array) $extra));
    }

    // ------------------------------------------------------------------
    // Authorization / responses
    // ------------------------------------------------------------------

    /** Admins may manage everything; everyone else only their own organizations. */
    protected function canManageOrganization($organizationId): bool
    {
        $user = auth()->user();
        if (!$user) {
            return false;
        }
        if ($user->hasRole('Admin')) {
            return true;
        }
        return $user->organizations->contains('id', (int) $organizationId);
    }

    protected function jsonError(string $message, int $status)
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }

    // ------------------------------------------------------------------
    // Step 2: Schedule & Media (also carries the two participation switches)
    // ------------------------------------------------------------------

    protected function scheduleRules(): array
    {
        return [
            'programDateRange' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    if (!$this->parseDateRange($value)) {
                        $fail('Please select a start date and an end date (the end can not be before the start).');
                    }
                },
            ],
            'programStepType' => 'required|in:step,time',
            'programLogo' => self::LOGO_RULE,
        ];
    }

    protected function updateProgramSchedule(Request $request, Cycle $cycle): array
    {
        $request->validate($this->scheduleRules());
        $extra = $this->applyProgramSchedule($request, $cycle);

        // FIX: programNonCompanyParticipation / programPrivateLink are checkboxes in this
        // same step's markup, so they belong to every Schedule-step save — but an unchecked
        // checkbox is simply absent from the payload, so the old `$request->has(...)` guard
        // skipped this call whenever both were being turned OFF, leaving stale values in the
        // DB. applyProgramContentOptions() already uses $request->boolean(), which correctly
        // treats "absent" as false, so just call it unconditionally.
        $this->applyProgramContentOptions($request, $cycle);

        $cycle->save();
        return $extra;
    }

    /** @return array extra payload for the JSON response (e.g. logo_url) */
    protected function applyProgramSchedule(Request $request, Cycle $cycle): array
    {
        $extra = [];

        // Already validated by scheduleRules(); no silent "keep old dates" fallback anymore
        [$start, $end] = $this->parseDateRange($request->input('programDateRange'));
        $cycle->start = $start->format('Y-m-d');
        $cycle->end = $end->format('Y-m-d');
        $cycle->step_type = $request->input('programStepType') === 'step' ? 'cycle' : 'time';

        if ($request->hasFile('programLogo')) {
            $fileManager = app(\App\Services\FileManagerService::class);
            $cycle->img_url = $fileManager->update($request->file('programLogo'), $cycle->img_url, 'programs', 'public_uploads');
            $extra['logo_url'] = Storage::disk('public_uploads')->url($cycle->img_url);
        }

        return $extra;
    }

    /** "2026-01-01 to 2026-02-01" (also accepts " - ", en/em dash and the Arabic "إلى"). */
    protected function parseDateRange($range): ?array
    {
        $parts = preg_split('/\s+(?:to|-|–|—|إلى)\s+/iu', trim((string) $range));
        if (!$parts || count($parts) !== 2) {
            return null;
        }

        $dates = [];
        foreach ($parts as $part) {
            $part = trim($part);
            try {
                $date = Carbon::createFromFormat('Y-m-d', $part);
            } catch (\Exception $e) {
                return null;
            }
            // Rejects overflow such as 2026-02-31
            if (!$date || $date->format('Y-m-d') !== $part) {
                return null;
            }
            $dates[] = $date->startOfDay();
        }

        return $dates[1]->lt($dates[0]) ? null : $dates;
    }

    // ------------------------------------------------------------------
    // Step 1 content (submitted together with General Info, never its own step)
    // ------------------------------------------------------------------

    protected function contentRules(): array
    {
        return [
            'programDescription' => 'required|string|max:' . self::DESCRIPTION_MAX,
            'programDescriptionAr' => 'required|string|max:' . self::DESCRIPTION_MAX,
        ];
    }

    protected function applyProgramContent(Request $request, Cycle $cycle): void
    {
        $cycle->description = $request->input('programDescription');
        $cycle->description_ar = $request->input('programDescriptionAr');
        $this->applyProgramContentOptions($request, $cycle);
    }

    protected function applyProgramContentOptions(Request $request, Cycle $cycle): void
    {
        $cycle->allow_non_company = $request->boolean('programNonCompanyParticipation') ? 1 : 0;

        // Keep the existing token so links that were already shared keep working;
        // only mint a new (unguessable) one when the link is switched on for the first time.
        if ($request->boolean('programPrivateLink')) {
            $cycle->private = $cycle->private ?: sha1(Str::random(40));
        } else {
            $cycle->private = null;
        }
    }

    // ------------------------------------------------------------------
    // Step 3: Mentorship Settings (mentorship + offline session location)
    // ------------------------------------------------------------------

    protected function updateProgramMentorship(Request $request, Cycle $cycle): array
    {
        $remindersOn = $request->boolean('programMentorshipReminders');

        $request->validate([
            'programAddress' => 'nullable|string|max:250',
            // http(s) only: this value ends up in an href on public pages
            'programGoogleMapsUrl' => ['nullable', 'string', 'max:250', 'regex:/^https?:\/\/\S+$/i'],
            'programMentorshipReminderTimes' => [$remindersOn ? 'required' : 'nullable', 'array'],
            'programMentorshipReminderTimes.*' => 'in:1,6,24',
        ], [
            'programGoogleMapsUrl.regex' => 'The Google Maps URL must start with http:// or https://.',
            'programMentorshipReminderTimes.required' => 'Select at least one reminder time.',
        ]);

        $extra = [];
        $cycle->auto_approve_mentorship_requests = $request->boolean('programAutoApproveMentorship') ? 1 : 0;
        $cycle->is_active_Rminder = $remindersOn ? 1 : 0;
        $cycle->offline_location_address = $request->filled('programAddress') ? trim($request->input('programAddress')) : null;
        $cycle->offline_location_map = $request->filled('programGoogleMapsUrl') ? trim($request->input('programGoogleMapsUrl')) : null;

        $cycle->save();
        $this->syncMentorshipReminders($cycle, $remindersOn ? (array) $request->input('programMentorshipReminderTimes', []) : []);

        return $extra;
    }

    /** Diff-based sync so unchanged rows keep their ids (anything referencing them stays valid). */
    protected function syncMentorshipReminders(Cycle $cycle, array $hours): void
    {
        $wanted = array_values(array_unique(array_map('intval', $hours)));

        $base = DB::table('mentorship_schedules')
            ->where('cycle_id', $cycle->id)
            ->where('unit', 'hours');

        $existing = array_map('intval', (clone $base)->pluck('value')->all());

        $stale = array_diff($existing, $wanted);
        if ($stale) {
            (clone $base)->whereIn('value', $stale)->delete();
        }

        $rows = [];
        foreach (array_diff($wanted, $existing) as $value) {
            $rows[] = [
                'cycle_id' => $cycle->id,
                'value' => $value,
                'unit' => 'hours',
                'is_active' => 1,
                'organization_id' => $cycle->organization_id,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        if ($rows) {
            DB::table('mentorship_schedules')->insert($rows);
        }
    }

    // ------------------------------------------------------------------
    // Step 4: Landing & Branding (visibility + branding colors)
    // ------------------------------------------------------------------

    protected function updateProgramLanding(Request $request, Cycle $cycle): array
    {
        $isPublic = $request->input('programVisibility') === 'public';

        $request->validate([
            'programVisibility' => 'required|in:public,private',
            'programPublicUrl' => ['nullable', 'string', 'max:250', Rule::requiredIf($isPublic)],
            // strict hex: these values end up in inline CSS on public pages
            'programColorGradientStartVal' => ['nullable', 'string', self::HEX_COLOR_RULE],
            'programColorGradientEndVal' => ['nullable', 'string', self::HEX_COLOR_RULE],
            'programColorMainVal' => ['nullable', 'string', self::HEX_COLOR_RULE],
        ], [
            'programPublicUrl.required' => 'Public URL is required for a public program.',
        ]);

        $extra = [];
        if ($isPublic) {
            $base = (string) url_slugify(trim($request->input('programPublicUrl')));
            if ($base === '') {
                throw ValidationException::withMessages([
                    'programPublicUrl' => 'The public URL must contain letters or numbers.',
                ]);
            }

            // NOTE: add a unique index on (organization_id, public_slug) to close the
            // race window between this uniqueness check and the save below.
            $taken = Cycle::where('organization_id', $cycle->organization_id)
                ->where('id', '!=', $cycle->id)
                ->whereNotNull('public_slug')
                ->pluck('public_slug')
                ->all();
            $slug = $base;
            $counter = 0;
            while (in_array($slug, $taken, true)) {
                $slug = $base . '-' . (++$counter);
            }

            $cycle->is_public = 1;
            $cycle->public_slug = $slug;
            $extra['public_slug'] = $slug;
        } else {
            $cycle->is_public = 0;
        }

        foreach ([
            'programColorGradientStartVal' => 'start_color',
            'programColorGradientEndVal' => 'end_color',
            'programColorMainVal' => 'main_color',
        ] as $field => $column) {
            if ($request->has($field)) {
                $cycle->{$column} = $this->normalizeProgramColor($request->input($field));
            }
        }

        $cycle->save();
        return $extra;
    }

    /** Normalize a branding color value (single leading '#'). */
    protected function normalizeProgramColor($color)
    {
        if (!is_string($color) || trim($color) === '') {
            return null;
        }
        return preg_replace('/^#*/', '#', trim($color));
    }

    // ------------------------------------------------------------------
    // Step 5: Custom Pages & Publish
    // ------------------------------------------------------------------

    protected function updateProgramPublish(Request $request, Cycle $cycle): array
    {
        $request->validate([
            'programConfirmed' => 'accepted',
            'programPages' => 'nullable|array|max:50',
            'programPages.*.programPageName' => 'nullable|string|max:150',
            'programPages.*.programPageVisibility' => 'nullable|in:public,participants,admins-only',
            'programPages.*.programPageStatus' => 'nullable',
            'programPages.*.programPageDescription' => 'nullable|string',
        ], [
            'programConfirmed.accepted' => 'Please confirm the program details before publishing.',
        ]);

        $visibilityMap = ['public' => 'public', 'participants' => 'private', 'admins-only' => 'admins'];
        $rows = [];
        $errors = [];
        $rowNo = 1;

        foreach ((array) $request->input('programPages', []) as $index => $page) {
            $title = trim($page['programPageName'] ?? '');
            $content = trim($page['programPageDescription'] ?? '');

            if ($title === '') {
                // A page with content but no name used to be dropped silently
                if ($this->hasVisibleContent($content)) {
                    $errors["programPages.{$index}.programPageName"] = 'Every custom page needs a name.';
                }
                continue;
            }

            $rows[] = [
                'cycle_id' => $cycle->id,
                'title' => $title,
                'content' => $content,
                'visibility' => $visibilityMap[$page['programPageVisibility'] ?? 'public'] ?? 'public',
                'is_active' => !empty($page['programPageStatus']) ? 1 : 0,
                'row_no' => $rowNo++,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        // TODO: sanitize page HTML (e.g. mews/purifier) before it is rendered on public program pages.
        // TODO: this step never flags the program as published; add that here if a status column exists.
        DB::table('cycle_custom_pages')->where('cycle_id', $cycle->id)->delete();
        if ($rows) {
            DB::table('cycle_custom_pages')->insert($rows);
        }

        return [];
    }

    /** Summernote sends "<p><br></p>" for an empty editor. */
    protected function hasVisibleContent(string $html): bool
    {
        $text = html_entity_decode(strip_tags($html, '<img><iframe><video>'), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        return preg_replace('/[\s\x{00A0}]+/u', '', $text) !== '';
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function checkProgramPublic(Request $request, string $id)
    {
        $program = Cycle::find($id);

        return response()->json([
            'is_public' => (bool) ($program && !empty($program->is_public) && !empty($program->public_slug)),
        ]);
    }

    public function SentEmail(Request $request)
    {

        // Filter Screening
        try {
            // جلب جميع final screenings المرتبطة بـ step->id
            $finalScreenings = Finalscreening::where('step_id', '=', $request->step)->get();

            // جلب جميع المحكمين المرتبطين
            $allJudges = collect();

            foreach ($finalScreenings as $screening) {
                $finaljudges = is_string($screening->judges) ? json_decode($screening->judges, true) : $screening->judges;

                if (is_array($finaljudges)) {
                    $judgeData = User::whereIn('id', $finaljudges)->get();
                    $allJudges = $allJudges->merge($judgeData);
                }
            }

            // إزالة التكرارات بين المحكمين
            $allJudges = $allJudges->unique('id');

            $cstep = Step::findOrFail($request->step);
            foreach ($allJudges  as $rname) {
                $judgeData = User::whereIn('id', $finaljudges)->get();
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
                if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                    try {
                        $user->notify($MSG);
                    } catch (\Throwable $th) {
                        // throw $th;
                    }
                }
            }
            // تخزين رسالة نجاح في الجلسة
            Session::flash('message', 'Email_sent_notify');
        } catch (\Exception $e) {
            // تخزين رسالة خطأ في الجلسة إذا حدثت مشكلة
            Session::flash('message2', 'An error occurred while sending emails. Please try again.');
        }

        // إعادة التوجيه إلى الصفحة المطلوبة
        return redirect()->back(); // استبدل `your_desired_route` بالمسار المطلوب.

    }

    public function customPages( $id )
    {
        $user = auth()->user();
        if( !$user->ableToAny(['programs.create', 'programs.edit']) ) { return abort(401); }
        // dd( $id );
        $ccycle = Cycle::with('custom_pages')->findOrFail($id);

        return view('admin.programs.customPages', compact('ccycle', 'id'));
    }

    public function programReport(string $id , Request $request)
    {
        $cycle = Cycle::withCount(['companies', 'mentors'])->with(['steps' => function ($query) {
            $query->orderBy('arr');
        }])->findOrFail($id);

        $totalWorkshops = $cycle->trainingSessions()->count();
        $companyQuery = $cycle->companies()->with(['sectoR', 'stagE', 'companyRelations', 'companyRelations.sector']);

        if( $request->filled('sector') ) {
            $companyQuery->whereHas('sectoR', function ($q) use ($request) {
                $q->where('name_en', $request->sector);
            });
        }

        if( $request->filled('stage') ) {
            $companyQuery->whereHas('stagE', function ($q) use ($request) {
                $q->where('name_en', $request->stage);
            });
        }

        $companies = $companyQuery->get();
        $sectorsLabel = Sector::select('id', 'name_en', 'name_ar')->get();

        // Build sector distribution from the cycle's actual companies
        // A company can belong to multiple sectors via company_relations
        $cycleCompanies = $cycle->companies()->get();
        $companyIds = $cycleCompanies->pluck('id')->toArray();

        // Get all sector assignments from company_relations (newer system, supports multiple sectors)
        $relationSectors = CompanyRelation::whereIn('company_id', $companyIds)
            ->whereNotNull('sector_id')
            ->select('company_id', 'sector_id')
            ->distinct()
            ->get()
            ->groupBy('company_id');

        // Count each company once per sector it belongs to; "Other" for companies with no valid sector
        $sectorDistribution = [];
        foreach ($cycleCompanies as $company) {
            $assignedSectors = [];

            // Source 1: company_relations table (multiple sectors possible)
            if ($relationSectors->has($company->id)) {
                foreach ($relationSectors->get($company->id) as $rel) {
                    if ($sectorsLabel->firstWhere('id', $rel->sector_id)) {
                        $assignedSectors[] = $rel->sector_id;
                    }
                }
            }

            // Source 2: direct companies.sector column (legacy fallback)
            if (empty($assignedSectors) && $company->sector && $sectorsLabel->firstWhere('id', $company->sector)) {
                $assignedSectors[] = $company->sector;
            }

            if (empty($assignedSectors)) {
                $sectorDistribution['unassigned'] = ($sectorDistribution['unassigned'] ?? 0) + 1;
            } else {
                foreach ($assignedSectors as $sid) {
                    $sectorDistribution[$sid] = ($sectorDistribution[$sid] ?? 0) + 1;
                }
            }
        }

        $sectorsCount = collect();
        foreach ($sectorDistribution as $sectorId => $count) {
            if ($sectorId === 'unassigned') {
                $sectorsCount->push((object) [
                    'name_en' => 'Not Assigned',
                    'name_ar' => 'غير محدد',
                    'companies_count' => $count,
                ]);
            } else {
                $sector = $sectorsLabel->firstWhere('id', $sectorId);
                $sectorsCount->push((object) [
                    'name_en' => $sector->name_en,
                    'name_ar' => $sector->name_ar,
                    'companies_count' => $count,
                ]);
            }
        }

        $stages = Stage::select('id', 'name_en', 'name_ar')->get();
        $progress = $cycle->getProgress();

        // Mentorship Requests Filtering
        $mentorshipRequests = $cycle->mentorshipRequests()
            ->with(['user.company', 'mentor'])
            ->when($request->filled('mentor'), fn($q) => $q->where('mentor_id', $request->mentor))
            ->when($request->filled('company'), fn($q) => $q->whereHas('user', fn($q2) => $q2->where('company_id', $request->company)))
            ->when($request->filled('zoom_date'), fn($q) => $q->whereDate('zoom_date', '>=', $request->zoom_date))
            ->get();

        $mentors = $cycle->mentors;
        $mentorNames = $mentorshipRequests->pluck('mentor.name')->toArray();
        $mentorSessions = $mentorshipRequests->groupBy('mentor_id')->map(fn($group) => $group->count())->toArray();

        $mentorAvgRatings = $mentorshipRequests->groupBy('mentor_id')->map(fn($group) => $group->avg('rating'))->toArray();

        // Top Mentors
        $topMentors = collect($mentorSessions)->sortDesc()->take(5)->keys()->all();

        $topMentorNames = [];
        $topMentorSessionsArray = [];
        foreach( $topMentors as $mentorId )
        {
            $mentorName = $mentorshipRequests->firstWhere('mentor_id', $mentorId)?->mentor?->name;
            if( $mentorName )
            {
                $topMentorNames[] = $mentorName;
                $topMentorSessionsArray[] = $mentorSessions[$mentorId] ?? 0;
            }
        }

        $mentorAvgRatingsArray = [];
        foreach( $mentorAvgRatings as $mentorId => $avgRating )
        {
            $mentor = $mentorshipRequests->firstWhere('mentor_id', $mentorId)?->mentor;
            if( $mentor ) { $mentorAvgRatingsArray[$mentor->name] = round($avgRating, 2); }
        }

        $workshopsQuery = $cycle->trainingSessions();

        if( $request->filled('trainer') ) { $workshopsQuery->where('trainer_name', $request->trainer); }

        if( $request->filled('workshop_name') ) { $workshopsQuery->where('training_sessions.title', 'like', '%' . $request->workshop_name . '%'); }

        if( $request->filled('type') ) { $workshopsQuery->where('type', $request->type); }

        $workshops = $workshopsQuery->where(function($query) {
            $query->where('type', 'offline')->orWhere('type', 'online')->orWhere('type', 'sessions');
        })->get();

        $trainerNames = $cycle->trainingSessions()->pluck('trainer_name')->unique()->values();

        $totalWorkshops = $workshops->count();
        $totalMinutes = $workshops->sum('duration');
        $totalSessionHours = round($totalMinutes / 60, 2);
        $zoomWorkshops = $workshops->where('type', 'sessions')->count();
        $inPersonWorkshops = $workshops->where('type', 'offline')->count();
        $uploadedMaterials = $workshops->where('type', 'online')->count();

        $surveyResponses = SurveyResponses::where('cycle_id', $id)->where('triggered_on', 'training')->WhereNotNull('responses')->get();

        // Fetch final evaluations
        $finalStepIds = $cycle->steps->where('stype', 5)->pluck('id');

        // Get metadata for each step and merge company scores
        $allCompaniesData = collect();
        $allJudges = collect();

        foreach( $finalStepIds as $stepId )
        {
            $check = ScreeningEvaluation::where('step_id', $stepId)->first();
            if( empty( $check ) ) 
            {
                $evaluation = new ScreeningEvaluation();
                $evaluation->step_id = $stepId; $evaluation->save(); 
            }
            $evaluationForm = ScreeningEvaluation::getEvaluationData( $stepId );
            $meta = ScreeningEvaluationResults::metadata($stepId);
            $comments = $meta['comments'];

            $allJudges = $allJudges->merge( $meta['judges'] );
            if( !empty( $meta['_companies'] ) && !is_null( $meta['_companies'] ) )
            {
                foreach( $meta['_companies'] as $index => $company )
                {
                    $teamJudges = collect([]);
                    $company_id = $company->id;
                    $totalScore = 0;
                    $publishedEvaluations = 0;
                    $averageScore = 0;
                    $evaluation_status = 'not_evaluated';
                    $company_comments = $comments->where('company_id', $company_id)->values();
                    foreach( $meta['judges'] as $judge )
                    {
                        if( in_array($company_id, $judge->startups->toArray()) )
                        {
                            $scoped_company = collect($judge->companies)->where('id', $company_id)->first();
                            if( $scoped_company && strtolower($scoped_company->status) === 'published' && isset($scoped_company->score) )
                            {
                                $comment = $company_comments->where('user_id', $judge->id)->first();
                                $comment = $comment?->value;
                                $judge->comments = $comment;
                                $teamJudges->push($judge);
                                $evaluation_status = 'published';
                                $totalScore += (float) $scoped_company->score;
                                $publishedEvaluations++;
                            }
                        }
                    }
    
                    if( $publishedEvaluations > 0 ) { $averageScore = round($totalScore / $publishedEvaluations, 1); }
                    $meta['_companies'][$index]->score = $averageScore;
                    $meta['_companies'][$index]->evaluation_status = $evaluation_status;
                    $meta['_companies'][$index]->company_judges = $teamJudges;
                }
                $allCompaniesData = $allCompaniesData->merge($meta['_companies']);
            }
        }


        $companyEvaluations = $allCompaniesData->groupBy('id')->map(function ($group) {
            return $group->sortByDesc('score')->first();
        })->values();

        // Evaluation completion
        $totalCompanies = $companyEvaluations->count();
        $publishedCompanies = $companyEvaluations->where('evaluation_status', 'published')->values();
        $evaluationCompletion = $totalCompanies > 0 ? round(($publishedCompanies->count() / $totalCompanies) * 100, 2) : 0;

        // Calculate average final score
        $averageFinalScore = $publishedCompanies->avg('score') ?: 0;
        $averageFinalScore = round($averageFinalScore, 2);

        // Status classification
        $companyEvaluations = $companyEvaluations->map(function ($company) {
            if( $company->evaluation_status == 'not_evaluated' ) { $status = 'Not Evaluated'; }
            else
            {
                $status = $company['average_score'] >= 90 ? 'Highest Rated' : ($company['average_score'] >= 70 ? 'Good Performance' : 'Needs Improvement');
            }
            $company['status'] = $status;
            return $company;
        });

        // Final Status distribution
        $statusDistribution = [
            'Highest Rated' => $companyEvaluations->where('status', 'Highest Rated')->count(),
            'Good Performance' => $companyEvaluations->where('status', 'Good Performance')->count(),
            'Needs Improvement' => $companyEvaluations->where('status', 'Needs Improvement')->count(),
            'Not Evaluated' => $companyEvaluations->where('status', 'Not Evaluated')->count(),
        ];

        // Evaluator status
        $evaluatorStatus = $allJudges->map(function ($judge) {
            $published = collect($judge->companies)->where('status', 'Published')->count();
            $draft = collect($judge->companies)->where('status', 'Draft')->count();
            $total = $published + $draft;
            return [
                'evaluator_id' => $judge->id,
                'evaluator_name' => $judge->name,
                'total_evaluations' => $total,
                'published_evaluations' => $published,
                'draft_evaluations' => $draft,
            ];
        })->values();

        $scoreDistribution = [
            ['label' => '90-100', 'min' => 90, 'max' => 100, 'count' =>  $publishedCompanies->whereBetween('score', [90, 100])->count()],
            ['label' => '80-89', 'min' => 80, 'max' => 89.9, 'count' =>  $publishedCompanies->whereBetween('score', [80, 89.9])->count()],
            ['label' => '60-79', 'min' => 60, 'max' => 79.9, 'count' =>  $publishedCompanies->whereBetween('score', [60, 79.9])->count()],
            ['label' => '< 60', 'min' => 0, 'max' => 59.9, 'count' =>  $publishedCompanies->whereBetween('score', [0, 59.9])->count()],
        ];
        /* $scoreDistribution = [
            '0-10'   => $publishedCompanies->whereBetween('score', [0, 10])->count(),
            '11-20'  => $publishedCompanies->whereBetween('score', [11, 20])->count(),
            '21-30'  => $publishedCompanies->whereBetween('score', [21, 30])->count(),
            '31-40'  => $publishedCompanies->whereBetween('score', [31, 40])->count(),
            '41-50'  => $publishedCompanies->whereBetween('score', [41, 50])->count(),
            '51-60'  => $publishedCompanies->whereBetween('score', [51, 60])->count(),
            '61-70'  => $publishedCompanies->whereBetween('score', [61, 70])->count(),
            '71-80'  => $publishedCompanies->whereBetween('score', [71, 80])->count(),
            '81-90'  => $publishedCompanies->whereBetween('score', [81, 90])->count(),
            '91-100' => $publishedCompanies->whereBetween('score', [91, 100])->count(),
        ];*/

        $companyIds = $companies->pluck('id')->toArray();
        if (!empty($companyIds))
        {
            $quizzesResults_q = "SELECT 
                    qr.id AS qr_result_id,
                    qr.user_id AS qr_id,
                    qr.quiz_id AS qr_quiz_id,
                    qr.score AS qr_result_score,
                    qr.status AS qr_result_status,
                    qr.started_at AS qr_result_started_at,
                    qr.ended_at AS qr_result_ended_at,
                    q.id AS q_quiz_id,
                    q.title AS q_quiz_title,
                    q.organization_id AS q_quiz_organization_id,
                    q.training_id AS q_quiz_training_id,
                    q.description AS q_quiz_description,
                    q.start AS q_quiz_start,
                    q.end AS q_quiz_end,
                    q.status AS q_quiz_status, 
                    q.created_at AS q_quiz_created_at, 
                    o.name_ar as o_org_name_ar,
                    o.name_en as o_org_name_en,
                    c.id as c_company_id,
                    c.name_en as c_company_name_en,
                    c.name_ar as c_company_name_ar,
                    c.logo as c_company_logo
                FROM
                    quiz_results qr
                LEFT JOIN
                    quizzes q ON qr.quiz_id = q.id
                LEFT JOIN
                    trainings t ON t.id = q.training_id
                LEFT JOIN
                    organizations o ON o.id = q.organization_id
                LEFT JOIN
                    users u ON u.id = qr.user_id
                LEFT JOIN
                    companies c ON c.id = u.company_id
                WHERE
                    c.id IN (" . implode(',', $companyIds) . ")
            ";
            $quizzesResults = collect(DB::select($quizzesResults_q));    
	    } else {
            $quizzesResults = collect();
        }

        return view('admin.programs.program-report', [
           'cycle' => $cycle,
           'totalStartups' => $cycle->companies_count,
           'totalMentors' => $cycle->mentors_count,
           'totalWorkshops'  => $totalWorkshops,
           'steps' => $cycle->steps,
           'companies' => $companies,
           'sectorsLabel' => $sectorsLabel,
           'sectorsData' => $sectorsCount,
           'stages' => $stages,
           'mentors' => $mentors,
           'mentorshipRequests' => $mentorshipRequests,
           'mentorNames' => $mentorNames,
           'mentorSessions' => $mentorSessions,
           'mentorAvgRatings' => $mentorAvgRatings,
           'trainerNames' => $trainerNames,
           'totalSessionHours' => $totalSessionHours,
           'zoomWorkshops' => $zoomWorkshops,
           'inPersonWorkshops' => $inPersonWorkshops,
           'uploadedMaterials' => $uploadedMaterials,
           'workshops' => $workshops,
           'surveyResponses' => $surveyResponses,
           'mentorNamesChart1' => $topMentorNames,
           'mentorSessionsArray' => $topMentorSessionsArray,
           'mentorAvgRatings' => $mentorAvgRatingsArray,
           'companyEvaluations' => $companyEvaluations,
           'averageFinalScore' => $averageFinalScore,
           'evaluationCompletion' => $evaluationCompletion,
           'statusDistribution' => $statusDistribution,
           'evaluatorStatus' => $evaluatorStatus,
           'scoreDistribution' => $scoreDistribution,
           'progress' => $progress, 
           'publishedCompanies' => $publishedCompanies,
           'quizzesResults' => $quizzesResults
        ]);
    }
}
