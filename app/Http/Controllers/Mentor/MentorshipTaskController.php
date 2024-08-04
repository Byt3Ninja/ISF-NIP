<?php

namespace App\Http\Controllers\Mentor;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mentor\MentorshipTask\TaskRequest;
use App\MentorshipRequest;
use App\MentorshipTask;
use Illuminate\Http\Request;
use Exception;
use Log;

class MentorshipTaskController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $mentorship = MentorshipRequest::findOrFail($request->mentorship_id);
        $mentorshipTasks = $mentorship->mentorshipTasks;
        return view('mentor.mentorshipTasks.index',compact('mentorshipTasks','mentorship'));
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
            $result = MentorshipTask::create($request->validated()+[
                    'mentor_id' => $mentorship->mentor_id,
                    'company_id' => $mentorship->user->company->id,
                    'user_id' => $mentorship->user_id,
                ]);

            if($result)
                return  redirect(route('mentorshipTasks.index',['mentorship_id'=>$request->mentorship_request_id]))->with( ['message' => 'StoredSuccessfully'] );
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
}
