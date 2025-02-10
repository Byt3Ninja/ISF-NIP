<?php

namespace App\Http\Controllers;

use App\Step;
use App\User;
use App\Cycle;
use App\Company;
use App\Organization;
use App\Finalscreening;
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
            })->get();
        }
        else if (Auth()->user()->hasRole('organizationManager')) {
            $cycles = Cycle::where('organization_id', $user->organization_id)->when($search, function ($query, $search) {
                return $query->where('title', 'like', '%' . $search . '%'); // بحث بناءً على title
            })->get();
        }
        else {
            // إذا كان المستخدم ليس Admin
            $Organization = Organization::where('user_id', auth()->user()->id)->first();

            if (!empty($Organization)) {
                $cycles = Cycle::where('organization_id', $Organization->id)
                    ->when($search, function ($query, $search) {
                        return $query->where('title', 'like', '%' . $search . '%'); // بحث بناءً على title
                    })->get();
            } else {
                $cycles = Cycle::where('id', 0)->get(); // لا توجد دورات إذا لم يكن لدى المستخدم منظمة
            }
        }
        return view('admin.programs.index', compact('cycles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
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
        $cycle = Cycle::where('id', $id)->first();
        if(empty($cycle)) return redirect()->route('programlist.index')->with(['message2' => 'program_not_found', 'class' => 'danger']);
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
    public function edit(string $id)
    {
        //
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
                $user->notify($MSG);
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
}
