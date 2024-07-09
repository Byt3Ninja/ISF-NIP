<?php

namespace App\Http\Controllers\Startup;

use App\Http\Controllers\Controller;
use App\MentorshipRequest;
use Illuminate\Http\Request;

class MentorshipTaskController extends Controller
{
    public function index($id){
        $mentorship= MentorshipRequest::findOrFail($id);
        $mentorshipTasks = $mentorship->tasks;
        return view('mentorshipTasks.index',compact('mentorshipTasks','mentorship'));
    }
}
