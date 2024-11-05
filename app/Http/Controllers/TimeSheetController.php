<?php

namespace App\Http\Controllers;

use App\Cycle;
use App\Organization;
use App\TimeSheet;
use App\Category;
use App\User;
use Illuminate\Http\Request;
use App\Jobs\ExportFTSData;
Use App\Settings;

class TimeSheetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $setting = Settings::findOrfail('1');
        if(auth()->user()->hasRole('Admin')){
        $timeSheets= TimeSheet::all();
        $users = User::all();
        $cycles = Cycle::all();

        }else{
            $timeSheets= TimeSheet::where('user_id', auth()->user()->id)->get();
            // جلب المستخدمين بناءً على الدور

        if (auth()->user()->hasRole('organizationManager')) {
            
            // جلب المستخدمين الذين لديهم دور manager أو coordinator
    $orgnizaion = Organization::where('user_id', '=', auth()->user()->id)->first();
    $cycles = Cycle::where('organization_id', '=', $orgnizaion->id)->get();

            $users =User::where('organization_id','=', $orgnizaion->id)->role(['manager', 'coordinator'])->get();
    
    
        } elseif (auth()->user()->hasRole('manager')) {
            // جلب المستخدمين الذين لديهم دور coordinator فقط
            $orgnizaion = Organization::where('user_id', '=', auth()->user()->id)->first();
            $cycles = Cycle::where('organization_id', '=', $orgnizaion->id)->get();
        
            $users =User::where('organization_id','=', $orgnizaion->id)->role('coordinator')->get();
    
        
        } elseif (auth()->user()->hasRole('coordinator')) {
            // لا يتم جلب أي مستخدمين
            $users = collect(); // إرجاع مجموعة فارغة
        }

        }

        // فلترة حسب التاريخ إذا تم إدخاله في النموذج
        if ($request->has('start_date') && $request->has('end_date') && $request->start_date != null && $request->end_date != null) {
            $timeSheets = $timeSheets->whereBetween('date', [$request->input('start_date'), $request->input('end_date')]);
        }

// فلترة حسب اسم الدورة إذا تم إدخاله في النموذج
if ($request->has('cycle_name') && $request->cycle_name != null) {
    
        $timeSheets = $timeSheets->where('program_id', $request->cycle_name);
    
}
       
        $categories = Category::all();
    
        return view('admin.TimeSheets.index', compact('timeSheets','categories','users','cycles', 'setting'));
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
        $request->validate([
            'title' => 'required|string|max:255',
            'TimeSheet_date' => 'required|date',
            'hours_worked' => 'required|numeric',
            'description' => 'required|string',
            'category' => 'required|exists:categories,id',
            'program' => 'required|exists:cycles,id',
            'status' => 'required|numeric',
        ]);

        if($request->user_id == ""){
            $usedId = Auth()->user()->id;
        }else{
            $usedId = $request->user_id;
        }

        $timesheet = new TimeSheet();
        $timesheet->title = $request->title;
        $timesheet->user_id = $usedId;
        $timesheet->date = $request->TimeSheet_date;
        $timesheet->hours_worked = $request->hours_worked;
        $timesheet->description = $request->description;
        $timesheet->category_id = $request->category;
        $timesheet->program_id = $request->program;
        $timesheet->status = $request->status;
        $timesheet->save();
    
        // إرجاع استجابة ناجحة (يمكن تعديلها حسب الحاجة)
        return back()->with(['class'=>'success','message'=>__('StoredSuccessfully')]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
        
        $request->validate([
            'title' => 'required|string|max:255',
            'TimeSheet_date' => 'required|date',
            'hours_worked' => 'required|numeric',
            'description' => 'required|string',
            'category' => 'required|exists:categories,id',
            'program' => 'required|exists:cycles,id',
            'user_id' => 'required|exists:users,id',
            'status' => 'required|numeric',
        ]);
        
        $timesheet = TimeSheet::find($id);
        $timesheet->title = $request->title;
        $timesheet->user_id = $request->user_id;
        $timesheet->date = $request->TimeSheet_date;
        $timesheet->hours_worked = $request->hours_worked;
        $timesheet->description = $request->description;
        $timesheet->category_id = $request->category;
        $timesheet->program_id = $request->program;
        $timesheet->status = $request->status;
        $timesheet->save();
        // إرجاع استجابة ناجحة (يمكن تعديلها حسب الحاجة)
        return back()->with(['class'=>'success','message'=>__('StoredSuccessfully')]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $timesheet = TimeSheet::find($id);
        // التأكد من وجود الـ Tag
        if (!$timesheet) {
            return back()->with(['class' => 'danger', 'message' => __('timesheetNotFound')]);
        }
        // حذف السجل بشكل دائم
        $timesheet->forceDelete();
        // إرجاع استجابة ناجحة
        return back()->with(['class' => 'success', 'message' => __('DeletedSuccessfully')]);
    }


    public function Finance(Request $request)
{
    $setting = Settings::findOrfail('1');
    $timeSheets = collect(); // إنشاء مجموعة فارغة
    $orgnization = collect(); // إنشاء مجموعة فارغة للمؤسسات

    if (auth()->user()->hasRole('Admin')) {
        $users = User::all();
        $timeSheets = TimeSheet::all();
        $cycles = Cycle::all();
        $orgnization = Organization::all(); // جلب جميع المؤسسات
    } else {
        $timeSheets = TimeSheet::where('user_id', auth()->user()->id)->get(); // جلب الساعات للمستخدم الحالي
        
        if (auth()->user()->hasRole('organizationManager')) {
            $organization = Organization::where('user_id', auth()->user()->id)->first();
            if ($organization) {
                $cycles = Cycle::where('organization_id', $organization->id)->get();
                $users = User::where('organization_id', $organization->id)->role(['manager', 'coordinator'])->get();
                $orgnization = Organization::where('id', $organization->id)->get(); // جلب المؤسسة المرتبطة
            }
        } elseif (auth()->user()->hasRole('manager')) {
            $organization = Organization::where('user_id', auth()->user()->id)->first();
            if ($organization) {
                $cycles = Cycle::where('organization_id', $organization->id)->get();
                $users = User::where('organization_id', $organization->id)->role('coordinator')->get();
                $orgnization = Organization::where('id', $organization->id)->get(); // جلب المؤسسة المرتبطة
            }
        } elseif (auth()->user()->hasRole('coordinator')) {
            $users = collect(); // إرجاع مجموعة فارغة
        }
    }

    // فلترة حسب user_id إذا تم إدخاله في النموذج
    if ($request->has('user_id') && $request->user_id != null) {
        $timeSheets = $timeSheets->filter(function($timesheet) use ($request) {
            return $timesheet->user_id == $request->input('user_id');
        });
    }

    // فلترة حسب org_id (المؤسسة) إذا تم إدخاله في النموذج
    if ($request->has('org_id') && $request->org_id != null) {
        $userOrg = User::where('organization_id', $request->org_id)->pluck('id'); // جلب IDs للمستخدمين في المؤسسة
        $timeSheets = $timeSheets->filter(function($timesheet) use ($userOrg) {
            return $userOrg->contains($timesheet->user_id); // التحقق من أن user_id موجود ضمن المستخدمين في المؤسسة
        });
    }

    // فلترة حسب اسم الدورة إذا تم إدخاله في النموذج
    if ($request->has('cycle_name') && $request->cycle_name != null) {
        $timeSheets = $timeSheets->where('program_id', $request->cycle_name);
    }

    if ($request->has('start_date') && $request->start_date != null) {
        $timeSheets = $timeSheets->where('date', '>=', $request->start_date);
    }
    
    if ($request->has('end_date') && $request->end_date != null) {
        $timeSheets = $timeSheets->where('date', '<=', $request->end_date);
    }

    $categories = Category::all();

    // جلب جميع الساعات
    $groupedTimeSheets = $timeSheets->load('user')->groupBy('user_id')->map(function ($timesheets) {
        return [
            'user' => $timesheets->first()->user, // أول مستخدم في المجموعة
            'total_hours' => $timesheets->sum('hours_worked'), // مجموع الساعات
        ];
    });

    return view('admin.TimeSheets.finance', compact('timeSheets', 'orgnization', 'categories', 'users', 'cycles', 'groupedTimeSheets', 'setting'));
}

public function exportFTS(Request $request)
    {
        $Tsheet = [
            'hours' => $request->input('hours'),
            'condition' => $request->input('condition'),
            'cycle_name' => $request->input('cycle_name'),
            'user_id' => $request->input('user_id'),
            'org_id' => $request->input(key: 'org_id'),
            'userEmail' => auth()->user()->email
        ];
        // إرسال وظيفة (Job) لتحميل البيانات وإرسال البريد
        ExportFTSData::dispatch($Tsheet);

        return response()->json(['message' => 'Export process started. You will receive an email with the download attached.']);
    }

    public function ClockUsers(Request $request)
    {
        $setting = Settings::findOrfail('1');
        $timeSheets = collect(); // إنشاء مجموعة فارغة
    $orgnization = collect(); // إنشاء مجموعة فارغة للمؤسسات

    if (auth()->user()->hasRole('Admin')) {
        $users = User::all();
        $timeSheets = TimeSheet::all();
        $cycles = Cycle::all();
        $orgnization = Organization::all(); // جلب جميع المؤسسات
    } else {
        $timeSheets = TimeSheet::where('user_id', auth()->user()->id)->get(); // جلب الساعات للمستخدم الحالي
        
        if (auth()->user()->hasRole('organizationManager')) {
            $organization = Organization::where('user_id', auth()->user()->id)->first();
            if ($organization) {
                $cycles = Cycle::where('organization_id', $organization->id)->get();
                $users = User::where('organization_id', $organization->id)->role(['manager', 'coordinator'])->get();
                $orgnization = Organization::where('id', $organization->id)->get(); // جلب المؤسسة المرتبطة
            }
        } elseif (auth()->user()->hasRole('manager')) {
            $organization = Organization::where('user_id', auth()->user()->id)->first();
            if ($organization) {
                $cycles = Cycle::where('organization_id', $organization->id)->get();
                $users = User::where('organization_id', $organization->id)->role('coordinator')->get();
                $orgnization = Organization::where('id', $organization->id)->get(); // جلب المؤسسة المرتبطة
            }
        } elseif (auth()->user()->hasRole('coordinator')) {
            $users = collect(); // إرجاع مجموعة فارغة
        }
    }

    // فلترة حسب user_id إذا تم إدخاله في النموذج
    if ($request->has('user_id') && $request->user_id != null) {
        $timeSheets = $timeSheets->filter(function($timesheet) use ($request) {
            return $timesheet->user_id == $request->input('user_id');
        });
    }

    // فلترة حسب org_id (المؤسسة) إذا تم إدخاله في النموذج
    if ($request->has('org_id') && $request->org_id != null) {
        $userOrg = User::where('organization_id', $request->org_id)->pluck('id'); // جلب IDs للمستخدمين في المؤسسة
        $timeSheets = $timeSheets->filter(function($timesheet) use ($userOrg) {
            return $userOrg->contains($timesheet->user_id); // التحقق من أن user_id موجود ضمن المستخدمين في المؤسسة
        });
    }

    // فلترة حسب اسم الدورة إذا تم إدخاله في النموذج
    if ($request->has('cycle_name') && $request->cycle_name != null) {
        $timeSheets = $timeSheets->where('program_id', $request->cycle_name);
    }

    if ($request->has('start_date') && $request->start_date != null) {
        $timeSheets = $timeSheets->where('date', '>=', $request->start_date);
    }
    
    if ($request->has('end_date') && $request->end_date != null) {
        $timeSheets = $timeSheets->where('date', '<=', $request->end_date);
    }

    $categories = Category::all();

    // جلب جميع الساعات
    $groupedTimeSheets = $timeSheets->load('user')->groupBy('user_id')->map(function ($timesheets) {
        return [
            'user' => $timesheets->first()->user, // أول مستخدم في المجموعة
            'total_hours' => $timesheets->sum('hours_worked'), // مجموع الساعات
        ];
    });

        return view('admin.TimeSheets.priceclock', compact('timeSheets', 'orgnization', 'categories', 'users', 'cycles', 'groupedTimeSheets','setting'));
    }

    public function updateClockUser(Request $request, string $id)
    {
        $request->validate([
            'pricetime' => 'required',
        ]);
        
        $user = User::find($id);
        $user->houre_rate = $request->pricetime;
        $user->save();
        // إرجاع استجابة ناجحة (يمكن تعديلها حسب الحاجة)
        return back()->with(['class'=>'success','message'=>__('StoredSuccessfully')]);
    }










}
