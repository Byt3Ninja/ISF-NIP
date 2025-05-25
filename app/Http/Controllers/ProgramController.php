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
use Illuminate\Http\Request;
use App\Jobs\ExportCycleData;
use App\Notifications\TemplateEmail;
use Illuminate\Support\Facades\Session;

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
        $cycles = $cycles->get();
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

        $judges = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 7)->get();


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
        $cycles = $cycles->get();
        return view('admin.programs.edit', compact('cycles'));
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
                $MSG = new TemplateEmail;
                $MSG->Message = ["New judging - VI portal", "Dear " . $rfullname . ",", "Kindly note that we need your experiences to judge New Incubation Cycle registered users, the judging period starting from " . $cstep->from->format('d-m-Y') .
                    " to " . $cstep->to->format('d-m-Y') . ", Check your account for more details.", "/dashboard"];
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
        $cycle = Cycle::withCount(['companies', 'mentors'])->with(['steps'])->findOrFail($id);

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

        $averageFinalScore = $cycle->mentorshipRequests()->avg('rating');

     
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
        ]);
   
    }
}
