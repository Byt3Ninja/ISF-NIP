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
			$validated = $request->validate([
					'session_type' => 'required',
					'tfrom' => 'required',
					'tto' => 'required',
					'type' => 'required|in:Weekly,Period',
					'days' => 'required_if:type,Weekly',
					'date' => 'required_if:type,Period',
			]);

			// Build base search query
			$query = Schedule::where('user_id', auth()->id())
					->where('type', $validated['type'])
					->where('tfrom', $validated['tfrom'])
					->where('tto', $validated['tto'])
					->where('session_type', $validated['session_type']);

			// Add type-specific conditions
			if ($validated['type'] === 'Weekly') {
					$query->where('days', $validated['days']);
			} else {
					$query->where('date', $validated['date']);
			}

			// Check if duplicate exists
			$exists = $query->exists();

			if ($exists) {
					return response()->json([
							'success' => false,
							'message' => __('mentor.slot_already_exists'),
					], 422);
			}

			// Create slot
			$slotData = [
					'user_id' => auth()->id(),
					'type' => $validated['type'],
					'session_type' => $validated['session_type'],
					'tfrom' => $validated['tfrom'],
					'tto' => $validated['tto'],
					'days' => $validated['type'] === 'Weekly' ? $validated['days'] : null,
					'date' => $validated['type'] !== 'Weekly' ? $validated['date'] : null,
			];

			$schedule = Schedule::create($slotData);

			return response()->json([
					'success' => true,
					'message' => __('mentor.slot_added_success'),
					'data' => [
							'id' => $schedule->id,
							'weekday' => $schedule->days,
							'date' => $schedule->date,
							'start_time' => substr($schedule->tfrom, 0, 5),
							'end_time' => substr($schedule->tto, 0, 5),
							'session_type' => $schedule->session_type,
							'weekly' => $schedule->type === 'Weekly',
					]
			], 200);
	}


 	public function copy(Request $request, $id)
	{
			$validated = $request->validate([
					'selectedDays' => 'required|array',
			]);

			// 1. Find original schedule slot
			$original = Schedule::findOrFail($id);

			$createdSlots = [];

			foreach ($validated['selectedDays'] as $day) {

					// 2. Build new schedule data
					$slotData = $original->replicate()->toArray();

					$slotData['user_id'] = auth()->id(); // always apply current user
					
					// 3. Apply day/date depending on type
					if ($original->type === 'Weekly') {
							$slotData['days'] = $day; // single weekday (0–6)
							unset($slotData['date']); // remove date if exists
					} else {
							// date‐specific slot
							$slotData['date'] = $day; // here $day must be a date (Y-m-d)
							unset($slotData['days']); // remove weekday if exists
					}

					// 4. Create new duplicated schedule
					$newSchedule = Schedule::create($slotData);

					$isWeekly = $newSchedule->type === 'Weekly';

					// 5. Format response slot
					$createdSlots[] = [
							'id'           => $newSchedule->id,
							'weekday'      => $isWeekly ? $newSchedule->days : null,
							'date'         => $isWeekly ? null : ($newSchedule->date ?? $newSchedule->dto),
							'start_time'   => substr($newSchedule->tfrom, 0, 5),
							'end_time'     => substr($newSchedule->tto, 0, 5),
							'session_type' => $newSchedule->session_type,
							'weekly'       => $isWeekly,
					];
			}

			return response()->json([
					'success' => true,
					'message' => __('mentor.slot_copied_success'),
					'data'    => $createdSlots,
			], 200);
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
