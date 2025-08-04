<?php

namespace App\Http\Controllers;

use App\Step;
use App\User;
use App\Cycle;
use App\Stage;
use App\Sector;
use App\Company;
use App\UserScore;
use App\Organization;
use App\Finalscreening;
use App\SurveyResponses;
use App\ScreeningEvaluation;
use Illuminate\Http\Request;
use App\Jobs\ExportCycleData;
use Illuminate\Support\Facades\DB;
use App\ScreeningEvaluationResults;
use App\Notifications\TemplateEmail;
use Illuminate\Support\Facades\Session;
use URL, stdClass;

class ProgramController extends Controller
{

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
        $search = $request->input('search'); // التحقق من وجود البحث في الطلب

        if (Auth()->user()->hasRole('Admin')) {
            // إذا كان المستخدم Admin
            $cycles = Cycle::when($search, function ($query, $search) {
                return $query->where('title', 'like', '%' . $search . '%'); // بحث بناءً على title
            });
        } else if (Auth()->user()->hasRole('organizationManager')) {
            $cycles = Cycle::where('organization_id', $user->organization_id)->when($search, function ($query, $search) {
                return $query->where('title', 'like', '%' . $search . '%'); // بحث بناءً على title
            });
        } else {
            // إذا كان المستخدم ليس Admin
            $Organization = Organization::where('user_id', auth()->user()->id)->first();

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
        if ($request->filled('program_type')) {
         $cycles->where('type', $request->program_type);
        }
        $cycles = $cycles->orderBy('id', 'desc')->with(['organization'])->get();
        $cycles = $cycles->map(function( $cycle ) {
            $cycle->join_url = null;
            if( $cycle->is_public == 0 ) { return $cycle; }
            else
            {
                $org = $cycle->organization;
                $slug = $cycle->public_slug;
                if( !empty( $slug ) && trim( $slug ) && !empty( $org ) )
                {
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

        if (Auth()->user()->hasRole('Admin')) {
            // إذا كان المستخدم Admin
            $cycles = Cycle::when($search, function ($query, $search) {
                return $query->where('title', 'like', '%' . $search . '%'); // بحث بناءً على title
            });
        } else if (Auth()->user()->hasRole('organizationManager')) {
            $cycles = Cycle::where('organization_id', $user->organization_id)->when($search, function ($query, $search) {
                return $query->where('title', 'like', '%' . $search . '%'); // بحث بناءً على title
            });
        } else {
            // إذا كان المستخدم ليس Admin
            $Organization = Organization::where('user_id', auth()->user()->id)->first();

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
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = Auth()->user();
        $cycle = Cycle::where('id', $id);
        if ($user->hasRole('organizationManager')) {
            $cycle = $cycle->where('organization_id', $user->organization_id);
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




        return view('admin.programs.show', compact('cycle', 'steps', 'judges', 'stepsData'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        $user = auth()->user();
        $search = $request->input('search'); // التحقق من وجود البحث في الطلب

        if (Auth()->user()->hasRole('Admin')) {
            // إذا كان المستخدم Admin
            $cycles = Cycle::when($search, function ($query, $search) {
                return $query->where('title', 'like', '%' . $search . '%'); // بحث بناءً على title
            });
        } else if (Auth()->user()->hasRole('organizationManager')) {
            $cycles = Cycle::where('organization_id', $user->organization_id)->when($search, function ($query, $search) {
                return $query->where('title', 'like', '%' . $search . '%'); // بحث بناءً على title
            });
        } else {
            // إذا كان المستخدم ليس Admin
            $Organization = Organization::where('user_id', auth()->user()->id)->first();

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
                    return in_array($team->user_id, $users_ids);
                });
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
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
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

    public function programReport(string $id , Request $request)
    {
        $cycle = Cycle::withCount(['companies', 'mentors'])->with(['steps' => function ($query) {
          $query->orderBy('arr');
          }])->findOrFail($id);

        $totalWorkshops = $cycle->trainingSessions()->count();
        

        $companyQuery = $cycle->companies()->with(['sectoR', 'stagE']);

        if ($request->filled('sector')) {
               $companyQuery->whereHas('sectoR', function ($q) use ($request) {
                  $q->where('name_en', $request->sector);
           });
        }

        if ($request->filled('stage')) {
               $companyQuery->whereHas('stagE', function ($q) use ($request) {
                  $q->where('name_en', $request->stage);
         });
        }

        $companies = $companyQuery->get();

        $sectors = Sector::all();

        $stages = Stage::all();

            
        // Mentorship Requests Filtering
        $mentorshipRequests = $cycle->mentorshipRequests()
         ->with(['user.company', 'mentor'])
        ->when($request->filled('mentor'), fn($q) => $q->where('mentor_id', $request->mentor))
        ->when($request->filled('company'), fn($q) => $q->whereHas('user', fn($q2) => $q2->where('company_id', $request->company)))
        ->when($request->filled('zoom_date'), fn($q) => $q->whereDate('zoom_date', '>=', $request->zoom_date))
        ->get();

      $mentors = $cycle->mentors;

      $mentorNames = $mentorshipRequests->pluck('mentor.name')->toArray();

      $mentorSessions = $mentorshipRequests
        ->groupBy('mentor_id')
        ->map(fn($group) => $group->count())
        ->toArray();

        $mentorAvgRatings = $mentorshipRequests
        ->groupBy('mentor_id')
        ->map(fn($group) => $group->avg('rating'))
        ->toArray();

     // Top Mentors
        $topMentors = collect($mentorSessions)
        ->sortDesc()
        ->take(5)
        ->keys()
        ->all();

        $topMentorNames = [];
        $topMentorSessionsArray = [];
        foreach ($topMentors as $mentorId) {
             $mentorName = $mentorshipRequests->firstWhere('mentor_id', $mentorId)?->mentor?->name;
            if ($mentorName) {
                $topMentorNames[] = $mentorName;
                $topMentorSessionsArray[] = $mentorSessions[$mentorId] ?? 0;
             }
        }

        $mentorAvgRatingsArray = [];
        foreach ($mentorAvgRatings as $mentorId => $avgRating) {
            $mentor = $mentorshipRequests->firstWhere('mentor_id', $mentorId)?->mentor;
             if ($mentor) {
                $mentorAvgRatingsArray[$mentor->name] = round($avgRating, 2);
            }
        }

     $workshopsQuery = $cycle->trainingSessions();

        if ($request->filled('trainer')) {
            $workshopsQuery->where('trainer_name', $request->trainer);
        }

        if ($request->filled('workshop_name')) {
            $workshopsQuery->where('training_sessions.title', 'like', '%' . $request->workshop_name . '%');
        }

        if ($request->filled('type')) {
            $workshopsQuery->where('type', $request->type);
        }

        $workshops = $workshopsQuery->where(function($query) {
        $query->where('type', 'offline')
          ->orWhere('type', 'online')
          ->orWhere('type', 'sessions');
        })->get();

        $trainerNames = $cycle->trainingSessions()->pluck('trainer_name')->unique()->values();

        $totalWorkshops = $workshops->count();
        $totalMinutes = $workshops->sum('duration');
        $totalSessionHours = round($totalMinutes / 60, 2);
        $zoomWorkshops = $workshops->where('type', 'sessions')->count();
        $inPersonWorkshops = $workshops->where('type', 'offline')->count();
        $uploadedMaterials = $workshops->where('type', 'online')->count();

        $surveyResponses = SurveyResponses::where('cycle_id', $id)->get()->unique('user_id')->WhereNotNull('responses');

        // Final Evaluations
        $finalEvaluations = ScreeningEvaluation::whereIn('step_id', $cycle->steps->pluck('id'))
            ->with([
                'criteria' => fn($q) => $q->select('id', 'evaluation_id', 'title', 'weight', 'parent_id'),
                'customFields.values.user.company'
            ])
            ->get();

        $evaluationResults = ScreeningEvaluationResults::whereIn('evaluation_id', $finalEvaluations->pluck('id'))
            ->with(['criteria' => fn($q) => $q->select('id', 'evaluation_id', 'weight', 'parent_id'), 'user'])
            ->get();

        $averageFinalScore = ScreeningEvaluationResults::whereIn('evaluation_id', $finalEvaluations->pluck('id'))
            ->where('status', 1)
            ->avg('score') ?: 0;
        $averageFinalScore = round((float)$averageFinalScore, 2);

        $companyEvaluations = [];
        foreach ($finalEvaluations as $evaluation) {
            $finalCommentField = $evaluation->customFields->where('name', 'final_comment')->first();
            $userValue = $finalCommentField ? $finalCommentField->values->first() : null;
            $companyUser = $userValue ? $userValue->user : null;
            $company = $companyUser ? $companyUser->company : null;

            if (!$company || !$companies->contains('id', $company->id)) {
                continue;
            }

            $results = $evaluationResults->where('evaluation_id', $evaluation->id)
                ->where('status', 1);

            $totalScore = $results->sum(function ($result) {
                $criterion = $result->criteria;
                if ($criterion && is_null($criterion->parent_id)) {
                    $score = (float)$result->score;
                    return ($criterion->weight * $score) / 100;
                }
                return 0;
            });

            $status = $totalScore >= 90 ? 'Highest Rated' : ($totalScore >= 70 ? 'Good Performance' : 'Needs Improvement');

            $comments = $userValue && $userValue->value ? $userValue->value : $results->pluck('comment')->filter()->join('; ');

            $companyEvaluations[] = [
                'company_id' => $company->id,
                'company_name' => $company->{'name_' . app()->getLocale()},
                'total_score' => round($totalScore, 2),
                'status' => $status,
                'comments' => $comments,
                'evaluation_status' => $evaluation->status,
            ];
        }

        $companyEvaluations = collect($companyEvaluations)
            ->groupBy('company_id')
            ->map(function ($group) {
                return $group->sortByDesc('total_score')->first();
            })
            ->sortByDesc('total_score')
            ->values();

        $totalEvaluations = $finalEvaluations->count();
        $publishedEvaluations = $finalEvaluations->where('status', 1)->count();
        $evaluationCompletion = $totalEvaluations > 0 ? round(($publishedEvaluations / $totalEvaluations) * 100, 2) : 0;
        $statusDistribution = [
            'Highest Rated' => $companyEvaluations->where('status', 'Highest Rated')->count(),
            'Good Performance' => $companyEvaluations->where('status', 'Good Performance')->count(),
            'Needs Improvement' => $companyEvaluations->where('status', 'Needs Improvement')->count(),
        ];

        $evaluatorStatus = $evaluationResults->groupBy('user_id')->map(function ($results, $userId) {
            $user = User::find($userId);
            $name = $user ? ($user->first_name_en . ' ' . $user->last_name_en ?? $user->name ?? "Evaluator $userId") : "Evaluator $userId";
            return [
                'evaluator_id' => $userId,
                'evaluator_name' => $name,
                'total_evaluations' => $results->count(),
                'published_evaluations' => $results->where('status', 1)->count(),
                'draft_evaluations' => $results->where('status', 0)->count(),
            ];
        })->values();
     
        return view('admin.programs.program-report', [
           'cycle' => $cycle,
           'totalStartups' => $cycle->companies_count,
           'totalMentors' => $cycle->mentors_count,
           'totalWorkshops'  => $totalWorkshops,
           'steps' => $cycle->steps,
           'companies' => $companies,
           'sectors' => $sectors,
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
           'averageFinalScore' => $averageFinalScore,
           'mentorNamesChart1' => $topMentorNames,
           'mentorSessionsArray' => $topMentorSessionsArray,
           'mentorAvgRatings' => $mentorAvgRatingsArray,
           'companyEvaluations' => $companyEvaluations,
           'evaluationCompletion' => $evaluationCompletion,
           'statusDistribution' => $statusDistribution,
           'evaluatorStatus' => $evaluatorStatus,
        ]);
   
    }
}
