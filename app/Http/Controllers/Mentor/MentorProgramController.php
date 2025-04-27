<?php

namespace App\Http\Controllers\Mentor;

use App\User;
use App\Cycle;
use App\MentorshipRequest;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class MentorProgramController extends Controller
{
  public function show()
  {
      $mentor = auth()->user();

      $cycles = $mentor->mentorCycles;

      $upcomingSessions = MentorshipRequest::where('mentor_id', $mentor->id)
          ->where('zoom_date', '>', now())
          ->get();

      foreach ($cycles as $cycle) {
          $steps = $cycle->steps;

          $totalSteps = $steps->count();

          $totalStepsFinish = $steps->filter(function ($step) {
              $now = now();
              return $now >= $step->from && $now <= $step->to; 
          })->count();

          $progressPercentage = $totalSteps > 0 ? ($totalStepsFinish / $totalSteps) * 100 : 0;

          $cycle->progressPercentage = $progressPercentage;
          $cycle->totalSteps = $totalSteps;
          $cycle->totalStepsFinish = $totalStepsFinish;

          $cycle->sessionsWithoutTasks = MentorshipRequest::where('cycle_id', $cycle->id)
             ->whereDoesntHave('tasks') 
             ->count();
      }

      return view('mentor.programs.show', compact('cycles', 'upcomingSessions'));
  }
}
