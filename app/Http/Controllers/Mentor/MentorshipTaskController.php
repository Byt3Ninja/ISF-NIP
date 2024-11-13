<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mentor\MentorshipTask\TaskRequest;
use App\Label;
use App\MentorshipRequest;
use App\MentorshipTask;
use App\MentorshipTaskComment;
use Illuminate\Http\Request;
use Exception;
use Log;
use Illuminate\Support\Facades\DB;

class MentorshipTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        
        $mentorship = MentorshipRequest::findOrFail($request->mentorship_id);
$mentorshipTasks = $mentorship->mentorshipTasks;

$labels = Label::all();

// تعديل محتوى المتغير $mentorshipTasks باستخدام map بحيث يتم تمرير التعليقات حتى لو كانت null
$mentorshipTasks = $mentorshipTasks->map(function ($mentorshipTask) {
    // جلب التعليقات الخاصة بكل مهمة
    $comments = MentorshipTaskComment::where('mentorship_task_id', $mentorshipTask->id)->get();
    
    // جلب معرّفات الملصقات المرتبطة بالمهمة
    $labelIds = $mentorshipTask->labels()->pluck('labels.id'); // استخدام pluck لاستخراج المعرفات فقط

    // التحقق من وجود تعليقات، وإذا كانت فارغة، يتم إرجاع مجموعة فارغة
    return [
        'mentorshipTask' => $mentorshipTask,
        'comments' => $comments->isEmpty() ? collect() : $comments,
        'label_ids' => $labelIds, // إضافة label_ids إلى البيانات
    ];
});

// تمرير $mentorshipTasks حتى لو كانت التعليقات فارغة
return view('mentor.mentorshipTasks.index', compact('mentorshipTasks', 'mentorship', 'labels'));

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $mentorship = MentorshipRequest::findOrFail($request->mentorship_id);
        return view('mentor.mentorshipTasks.create',compact('mentorship'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TaskRequest $request)
    {
        
        try{

            $mentorship = MentorshipRequest::find($request->mentorship_request_id);

            $labelNames = $request->label; // أسماء الـ labels المدخلة من الطلب

            // تحويل إلى مصفوفة إذا لم يكن كذلك
            if (!is_array($labelNames)) {
                $labelNames = [$labelNames];
            }
            
            $labelIds = []; // مصفوفة لتخزين معرفات الـ labels
            
            if ($labelNames) {
                $user = auth()->user();
                foreach ($labelNames as $labelName) {
                    // تحقق إذا كانت الـ label موجودة في جدول labels
                    $label = Label::where('name', $labelName)->first();
            
                    // إذا لم تكن موجودة، قم بإنشائها في جدول labels
                    if (!$label) {
                        $label = new Label();
                        $label->name = $labelName;
                        $label->user_id = $user->id;
                        $label->save(); // حفظ الـ label في جدول labels
                    }
            
                    // أضف معرف الـ label إلى المصفوفة
                    $labelIds[] = $label->id;
                }
            }

// إنشاء المهمة في جدول mentorship_tasks
$result = MentorshipTask::create($request->validated() + [
    'mentor_id' => $mentorship->mentor_id,
    'company_id' => $mentorship->user->company->id,
    'user_id' => $mentorship->user_id,
    'title' => $request->title,
    'description' => $request->description,
    'due_date' => $request->due_date,
    'status' => $request->status,
    'attached' => $request->attachments,
]);

// ربط المعرفات من جدول label_tasks يدويًا
if (!empty($labelIds)) {
    foreach ($labelIds as $labelId) {
        // إضافة السجل إلى جدول label_tasks يدويًا
        DB::table('label_taks')->insert([
            'mentorship_tasks_id' => $result->id,
            'label_id' => $labelId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}


            if($result)
                return  redirect(route('mentorshipTasks.index',['mentorship_id'=>$request->mentorship_request_id]))->with( [
            'message' => 'StoredSuccessfully'] );
            else
                return back()->withInput()->with( ['message2' => 'FailedStore'] );

        }catch (Exception $e){
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);

        }

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $task = MentorshipTask::findOrFail($id);
        $mentorship=$task->mentorshipRequest;
        return view('mentor.mentorshipTasks.show',compact('task','mentorship'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $task=MentorshipTask::findOrFail($id);
        return view('mentor.mentorshipTasks.edit',compact('task'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TaskRequest $request, string $id)
    {

        try{

            $task= MentorshipTask::findOrFail($id);
            $result = $task->update($request->validated());

            if($result)
                return redirect(route('mentorshipTasks.index',['mentorship_id'=>$task->mentorship_request_id]))->with( ['message' => 'StoredSuccessfully'] );
            else
                return back()->withInput()->with( ['message2' => 'FailedStore'] );

        }catch (Exception $e){
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);

        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if (!$task = MentorshipTask::find($id)) {
            return back()->with('class', 'alert alert-danger')->with('message', trans('dash.try_2_access_not_found_content'));
        }
        $task->delete();
        return back()->with('class', 'alert alert-success')->with('message', trans('dash.deleted_successfully'));

    }

    public function sendComment(Request $request){
        try{

            $task= MentorshipTask::findOrFail($request->mentorship_task_id);
            $result = $task->comments()->create(['user_id'=>auth()->id(),'comment'=>$request->comment]);

            if($result)
                return redirect()->back()->with( ['message' => 'StoredSuccessfully'] );
            else
                return back()->withInput()->with( ['message2' => 'FailedStore'] );

        }catch (Exception $e){
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);

        }
    }


    public function updateWith(TaskRequest $request)
    {
        
        try{
            $mentorship = MentorshipRequest::find($request->mentorship_request_id);
            $mentorshipTask = MentorshipTask::findOrFail($request->MTaskId);
            
            $labelName = $request->label; // اسم الـ label المدخل من الطلب
            
            // إذا تم تمرير اسم الـ label، تحقق من وجوده أو قم بإنشائه
            if ($labelName) {
                $user = auth()->user();
                // تحقق إذا كانت الـ label موجودة بالفعل
                $label = Label::where('name', $labelName)->first();
            
                // إذا لم تكن موجودة، قم بإنشائها
                if (!$label) {
                    $label = new Label();
                    $label->name = $labelName;
                    $label->user_id = $user->id;
                    $label->save();
                }
            }
            
            // تحديث معلومات المهمة
            $result = $mentorshipTask->update($request->validated() + [
                'mentor_id' => $mentorship->mentor_id,
                'company_id' => $mentorship->user->company->id,
                'user_id' => $mentorship->user_id,
                'title' => $request->title,
                'description' => $request->description,
                'due_date' => $request->due_date,
                'status' => $request->status,
                'label_id' => $label->id, // ربط الـ label بالمهمة
            ]);
            
            // ربط المعرفات من جدول label_tasks
            if ($request->has('label_ids')) { // تأكد من أن `label_ids` هي مصفوفة المعرفات المرسلة من الطلب
                $labelIds = $request->label_ids; // افترض أن `label_ids` تحتوي على المعرفات
                $mentorshipTask->labels()->attach($labelIds); // ربط الملصقات بالمهمة باستخدام الجدول المحوري
            }
            
            // إضافة تعليق إذا كان موجودًا
            if (!empty($request->comment)) {
                $mentorshipTask->comments()->create([
                    'user_id' => auth()->id(),
                    'comment' => $request->comment,
                    'attached' => $request->attachments, // حفظ المرفقات إذا كانت موجودة
                ]);
            }
            
            // إضافة تعليق إذا كانت المرفقات موجودة فقط
            if (!empty($request->attachments) && empty($request->comment)) {
                $mentorshipTask->comments()->create([
                    'user_id' => auth()->id(),
                    'comment' => 'Attached', // إذا لم يكن هناك تعليق، يمكن وضع نص ثابت
                    'attached' => $request->attachments,
                ]);
            }
            

            if($result)
                return  redirect(route('mentorshipTasks.index',['mentorship_id'=>$request->mentorship_request_id]))->with( [
            'message' => 'StoredSuccessfully'] );
            else
                return back()->withInput()->with( ['message2' => 'FailedStore'] );

        }catch (Exception $e){
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);

        }

    }


}
