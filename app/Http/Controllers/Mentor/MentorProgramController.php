<?php

namespace App\Http\Controllers\Mentor;

use App\User;
use App\Cycle;
use App\MentorshipRequest;
use App\Services\TimezoneService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class MentorProgramController extends Controller
{
  public function show()
  {
      $mentor = auth()->user();

      $cycles = $mentor->mentorCycles()->with(['mentorshipRequests.tasks'])->get();

      $upcomingSessions = MentorshipRequest::where('mentor_id', $mentor->id)
          // GV-9566: zoom_date is platform wall-clock; bind the platform clock.
          ->where('zoom_date', '>', TimezoneService::platformNow()->format('Y-m-d H:i:s'))
          ->get()
          ->each(function ($session) {
              $session->meeting_status = mentorshipMeetingStatus($session->zoom_date, $session->period);
          });

      foreach ($cycles as $cycle) {
          $steps = $cycle->steps;

          $totalSteps = $steps->count();

          // GV-9566: steps.from/to are inclusive DATEs in the organization's platform zone.
          $today = TimezoneService::platformToday(TimezoneService::platform($cycle->organization_id ? (int) $cycle->organization_id : null));
          $totalStepsFinish = $steps->filter(function ($step) use ($today) {
              return $step->from && $step->to
                  && $step->from->toDateString() <= $today && $today <= $step->to->toDateString();
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
