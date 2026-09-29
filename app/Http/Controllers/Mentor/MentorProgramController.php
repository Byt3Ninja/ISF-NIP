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

      $cycles = $mentor->mentorCycles()->with(['mentorshipRequests.tasks'])->get();

      $upcomingSessions = MentorshipRequest::where('mentor_id', $mentor->id)
          ->where('zoom_date', '>', now())
          ->get()
          ->each(function ($session) {
              $session->meeting_status = mentorshipMeetingStatus($session->zoom_date, $session->period);
          });

      foreach ($cycles as $cycle) {
          $steps = $cycle->steps;

          $totalSteps = $steps->count();

          $totalStepsFinish = $steps->filter(function ($step) {
              $now = now();
              return $now >= $step->from && $now <= $step->to; 
          })->count();

          $sessionsWithoutTasks = $cycle->mentorshipRequests->where('zoom_date', '>', now())->filter(function ($request) {
            return $request->tasks->where('status', 'pending')->isEmpty();
        })->count();

          $progressPercentage = $totalSteps > 0 ? ($totalStepsFinish / $totalSteps) * 100 : 0;

          $cycle->progressPercentage = $progressPercentage;
          $cycle->totalSteps = $totalSteps;
          $cycle->totalStepsFinish = $totalStepsFinish;
          $cycle->sessionsWithoutTasks = $sessionsWithoutTasks;

      }

      return view('mentor.programs.show', compact('cycles', 'upcomingSessions'));
  }
}
