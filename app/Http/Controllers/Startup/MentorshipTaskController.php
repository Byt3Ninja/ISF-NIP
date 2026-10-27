<?php

namespace App\Http\Controllers\Startup;

use App\Company;
use App\Http\Controllers\Controller;
use App\MentorshipRequest;
use App\MentorshipTaskComment;
use App\MentorshipTask;
use Illuminate\Http\Request;
use Exception;
use Log;

class MentorshipTaskController extends Controller
{
    public function index($id){
        $mentorship= MentorshipRequest::findOrFail($id);
        $this->authorizeMentorship($mentorship);
        $mentorshipTasks = $mentorship->mentorshipTasks;

        
        return view('startup.mentorshipTask.index',compact('mentorshipTasks','mentorship'));
    }
    public function home(){
    
        $user = auth()->user();
        if ($user && $user->company) {
            $companyId = $user->company->id;
            $mentorshipTasks = MentorshipTask::where('company_id', $companyId)->with(['labels', 'comments.user', 'user', 'mentor'])->get();


            // إذا كانت المهام فارغة، يمكنك أيضًا إرجاع الرسالة كعنصر في المصفوفة
            if ($mentorshipTasks->isEmpty()) {
                $mentorshipTasks = ["NoCompany"];
            }
        } else {
            // إذا لم يكن هناك شركة مرتبطة بالمستخدم
            $mentorshipTasks = ["NoCompany"];
        }
        
        return view('startup.mentorshipTask.home',compact('mentorshipTasks'));
        
    }

    public function show($id){
        $task= MentorshipTask::findOrFail($id);
        $this->authorizeTask($task);
        return view('startup.mentorshipTask.show',compact('task'));
    }

    public function complete(Request $request,$id){
        // Outside the try: its catch would turn the 403/404 into a 500.
        $task= MentorshipTask::findOrFail($id);
        $this->authorizeTask($task);
        try{

            $result=$task->update(['status'=>'completed',/*'done_date'=>now()->format('Y-m-d'),'reply'=>$request->reply*/]);

            if($result)
                return redirect(route('startup.startup.mentorshipTasks.index',['mentorship_id'=>$task->mentorship_request_id]))->with( ['message' => 'StoredSuccessfully'] );
            else
                return back()->withInput()->with( ['message2' => 'FailedStore'] );

        }catch (Exception $e){
            //log what happend
            Log::channel('system_exceptions')->info('Exceptions:', [$e]);
            abort(500);

        }

    }
    public function sendComment(Request $request){
        // Outside the try: its catch would turn the 403/404 into a 500.
        $task= MentorshipTask::findOrFail($request->mentorship_task_id);
        $this->authorizeTask($task);
        try{

            if ($task->status === 'completed') {
                return back()->with(['message2' => 'TaskAlreadyCompleted']);
            }
            if ( $task->comments()->where('user_id',auth()->id())->first()){
                $task->update(['status'=>'completed','done_date'=>now()->format('Y-m-d')]);
            }
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

    /** H3: the startup's own team, the task's mentor, or an admin. */
    private function authorizeTask(MentorshipTask $task): void
    {
        $user = auth()->user();
        abort_unless(
            $user->hasRole('admin')
                || ($user->company_id && (int) $task->company_id === (int) $user->company_id)
                || (int) $task->mentor_id === (int) $user->id,
            403
        );
    }

    /** H3: the founder who made the request, their teammates, its mentor, or an admin. */
    private function authorizeMentorship(MentorshipRequest $mentorship): void
    {
        $user = auth()->user();
        $requesterCompanyId = $mentorship->user?->company_id;
        abort_unless(
            $user->hasRole('admin')
                || (int) $mentorship->user_id === (int) $user->id
                || ($user->company_id && $requesterCompanyId && (int) $requesterCompanyId === (int) $user->company_id)
                || (int) $mentorship->mentor_id === (int) $user->id,
            403
        );
    }

}
