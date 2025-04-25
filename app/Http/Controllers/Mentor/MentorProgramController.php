<?php

namespace App\Http\Controllers\Mentor;

use App\User;
use App\Company;
use App\MentorshipTask;
use App\SurveyResponses;
use App\MentorshipRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Traits\HasRoles;

class MentorProgramController extends Controller
{
    public function index(){

    }



    public function show()
    {
      

      return view('mentor.programs.show');
    }
}
