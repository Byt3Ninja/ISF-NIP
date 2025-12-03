<?php

namespace App\Http\Controllers\Mentor;

use App\User;
use App\Cycle;
use Exception;
use App\Company;
use App\Surveys;
use App\Schedule;
use Carbon\Carbon;
use App\SurveyScopes;
use App\CyclesMentors;
use App\Events\WebMessage;
use App\MentorshipRequest;
use Illuminate\Http\Request;
use App\Jobs\ExportMentorsData;
use App\Events\RequestMentorship;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Notifications\TemplateEmail;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendMentorshipRequestJob;
use App\Factories\OnlineMeetingFactory;
use App\Events\MentorshipSessionUpdated;
use App\Http\Resources\MentorResource;
use App\Jobs\SendMentorshipNotification;
use App\Notifications\MentorshipSession;
use App\Notifications\MentorshipRequestNotification;
use App\Http\Controllers\Controller;

class SlotController extends Controller
{
	protected $meetingService;
	protected $whatsAppService;
	public function __construct(WhatsAppService $whatsAppService)
	{
		$this->meetingService = OnlineMeetingFactory::create();
		$this->whatsAppService = $whatsAppService;
	}


	public function index()
	{
			return view('mentor.availability.index');
	}

	public function getSlots() {
		$schedules = Schedule::where('user_id', auth()->id())->get();

      $slots = $schedules->map(function ($item) {

				$isWeekly = $item->type == 'Weekly';

          return [
              'id'           => $item->id,
              'weekday'      => $isWeekly ? $item->days : null,
							'date'			   => $isWeekly ? null : ($item->date ?? $item->dto),
              'start_time'   => substr($item->tfrom, 0, 5), // "14:00"
              'end_time'     => substr($item->tto, 0, 5),   // "17:00"
              'session_type' => $item->session_type,
              'weekly'       => $isWeekly,
          ];
      });

			return response()->json(['success' => true, 'data' => $slots], 200);
	}

  public function store(Request $request)
	{
			dd($request->all());
			// return view('mentor.availability.index');
	}

  public function copy()
	{
			// return view('mentor.availability.index');
	}

	public function destroy(Request $request, $id)
	{
		try {
			$schedule = Schedule::where('user_id', Auth::id())->findOrFail($id);
			$schedule->delete();
			return response()->json(['success' => true, 'message' => __('mentor.slot_removed_success')], 200);
		} catch (Exception $e) {
			return response()->json(['success' => false, 'message' => __('mentor.slot_removed_error')], 500);
		}
	}

}
