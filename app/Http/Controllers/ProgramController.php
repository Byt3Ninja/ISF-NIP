<?php

namespace App\Http\Controllers;

use App\Step;
use App\User;
use App\Cycle;
use App\Company;
use App\Organization;
use Illuminate\Http\Request;
use App\Jobs\ExportCycleData;


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
        $search = $request->input('search'); // التحقق من وجود البحث في الطلب

    if (Auth()->user()->hasRole('Admin')) {
        // إذا كان المستخدم Admin
        $cycles = Cycle::when($search, function ($query, $search) {
            return $query->where('title', 'like', '%' . $search . '%'); // بحث بناءً على title
        })->get();
    } else {
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
        $program = Cycle::where('id', $id)->first();
        $steps = Step::where('cycle_id', $program->id)->orderBy('arr','ASC')->get();
        $id = $program->pluck('id')[0];
                $steps = Step::where('cycle_id', $id)->orderBy('arr')->get();

                $judges = User::join('model_has_roles', 'id', 'model_id')->where('role_id', 7)->get();
                
               $stepsData = [];
                foreach ($steps as $step) {
                    $stepsData[] = [
                        'step_id' => $step->id,
                        'total_companies' => Company::where('step', $step->id)->count(),
                    ];
                }




        return view('admin.programs.show', compact('program','steps', 'judges', 'stepsData'));
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
}
