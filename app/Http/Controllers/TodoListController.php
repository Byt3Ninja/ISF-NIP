<?php

namespace App\Http\Controllers;

use App\User;
use App\TodoList;
use App\Team_member;
use Illuminate\Http\Request;
use App\Mail\TaskAssignedMail;
use App\Notifications\TaskNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Traits\AuthorizesTeamAccess;

class TodoListController extends Controller
{
    use AuthorizesTeamAccess;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
       
        $user = auth()->user();
        $request->validate([
            'title'    => 'required|string|max:255',
            'assignee' => 'required',
            'due_date'   => 'nullable|date',
            'description' => 'nullable|string|max:1000'
        ]);
       
        if($request->assignee == "You"){
            $assignee = $user->id;
        }else{
            $assignee = $request->assignee;
        }
        $team_member_q = "SELECT * FROM team_members WHERE user_id = ? AND acceptInvition = 'accepted' ";
        $team_member = DB::select($team_member_q, [$user->id]);
        $todoList = new TodoList;
        $todoList->title = $request->title;
        $todoList->description = $request->description;
        $todoList->status = 'todo';
        $todoList->assignee = $assignee;
        $todoList->due_date = $request->due_date;
        $todoList->cycle_id = $user->team_member->teamFormation->user->company->cycle;
        $todoList->step_id = $user->team_member->teamFormation->user->company->step;
        $todoList->user_id = $user->id;
        $todoList->team_formation_id = $team_member[0]->team_formation_id;
        $todoList->team_member_id = $team_member[0]->id;
        $todoList->save();

        $assignedUser = User::find($assignee);

        if ($assignedUser && $assignedUser->email) {
            Mail::to($assignedUser->email)->queue(new TaskAssignedMail($assignedUser, $todoList));
            
        }


        return response()->json(['success' => true, 'message' => __("admin.Task_added_successfully.")]);
        
    }

    /**
     * Display the specified resource.
     */
    public function show(TodoList $todoList)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TodoList $todoList)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {   
      
        $user = auth()->user();
        $request->validate([
           'title'    => 'required|string|max:255',
            'assignee' => 'required',
            'due_date'   => 'nullable|date',
            'description' => 'nullable|string|max:1000'
        ]);
        
        if($request->assignee == "You"){
            $assignee = $user->id;
        }else{
            $assignee = $request->assignee;
        }

        $todoList = TodoList::find($request->id);
        // Tasks belong to a team: any accepted member may manage them, but the
        // id is client-supplied, so a non-member (or removed member) must not.
        if (!$todoList || !$this->canViewTeam($user, $todoList->team_formation_id)) {
            return response()->json(['success' => false, 'message' => __("admin.Task_not_found")], 404);
        }
        $todoList->title = $request->title;
        $todoList->description = $request->description;
        $todoList->status = $request->status;
        $todoList->assignee = $assignee;
        $todoList->due_date = $request->due_date;
        $todoList->cycle_id = $user->team_member->teamFormation->user->company->cycle;
        $todoList->step_id = $user->team_member->teamFormation->user->company->step;
        $todoList->user_id = $user->id;
        $todoList->save();

        $assignedUser = User::find($assignee);

        if ($assignedUser && $assignedUser->email) {
            $assignedUser->notify(new TaskNotification([
                'action' => 'task_updated',
                'message' => __('admin.Task_updated_successfully.'),
                'task' => $todoList
            ]));
        }

        return response()->json(['success' => true, 'message' => __("admin.Task_updated_successfully.")]);
         
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $todoList = TodoList::find($id);
        if (!$todoList || !$this->canViewTeam(auth()->user(), $todoList->team_formation_id)) {
            return response()->json(['success' => false, 'message' => __("admin.Task_not_found")], 404);
        }
        $todoList->delete();
        return response()->json(['success' => true, 'message' => __("admin.Task_deleted_successfully.")]);
    }

    public function changeTodoStatus(Request $request)
    {
        $todoList = TodoList::find($request->id);
        if (!$todoList || !$this->canViewTeam(auth()->user(), $todoList->team_formation_id)) {
            return response()->json(['success' => false, 'message' => __("admin.Task_not_found")], 404);
        }

        $todoList->status = $request->status;
        $todoList->save();

        return response()->json(['success' => true, 'message' => __("admin.Task_status_updated_successfully")]);
    }
}
