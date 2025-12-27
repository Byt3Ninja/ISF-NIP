<?php

namespace App\Http\Controllers\Mentor;

use App\User;
use Exception;
use App\Schedule;
use App\Settings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Factories\OnlineMeetingFactory;

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
			$settings = Settings::where('id', 1)->first();
			$offline_allowed = $settings->allow_offline_sessions = 1 ? true : false;			
			
			return view('mentor.availability.index', compact('offline_allowed'));
	}

	private $weekdayMap = [
    'Mon' => 'Monday',
    'Tue' => 'Tuesday',
    'Wed' => 'Wednesday',
    'Thu' => 'Thursday',
    'Fri' => 'Friday',
    'Sat' => 'Saturday',
    'Sun' => 'Sunday',
	];

	private function cleanWeekday($value)
	{
			if (!$value) return [];

			$decoded = json_decode($value, true);

			// If valid JSON
			if (json_last_error() === JSON_ERROR_NONE) {

					// If string: convert to array
					if (is_string($decoded)) {
							return [$this->weekdayMap[$decoded] ?? $decoded];
					}

					// If array: convert each item
					if (is_array($decoded)) {
							return array_map(function ($w) {
									return $this->weekdayMap[$w] ?? $w;
							}, $decoded);
					}
			}

			// Not JSON → treat as raw string
			$value = trim($value, '"');
			return [$this->weekdayMap[$value] ?? $value];
	}


	private function getBlockedIntervals($maxLimit)
	{
		if (!$maxLimit) return [];

		$events = [];
		$now = now();

		// 1. Training Sessions
		$sessions = \DB::table('training_sessions')
			->whereNotNull('zoom_id')
			->where('enddatetime', '>', (string)$now->timestamp)
			->get(['datetime', 'enddatetime']);

		foreach ($sessions as $s) {
			$events[] = ['time' => (int)$s->datetime, 'type' => 1];
			$events[] = ['time' => (int)$s->enddatetime, 'type' => -1];
		}

		// 2. Final Screenings
		$screenings = \DB::table('finalscreenings')
			->whereNotNull('zoom_id')
			->whereRaw('DATE_ADD(datetime, INTERVAL duration MINUTE) > ?', [$now->toDateTimeString()])
			->get(['datetime', 'duration']);

		foreach ($screenings as $s) {
			$start = Carbon::parse($s->datetime);
			$end = (clone $start)->addMinutes($s->duration);
			$events[] = ['time' => $start->timestamp, 'type' => 1];
			$events[] = ['time' => $end->timestamp, 'type' => -1];
		}

		// 3. Mentorship Requests
		$mentorships = \DB::table('mentorship_requests')
			->whereNotNull('zoom_id')
			->whereRaw('DATE_ADD(zoom_date, INTERVAL period MINUTE) > ?', [$now->toDateTimeString()])
			->get(['zoom_date', 'period']);

		foreach ($mentorships as $m) {
			$start = Carbon::parse($m->zoom_date);
			$end = (clone $start)->addMinutes($m->period);
			$events[] = ['time' => $start->timestamp, 'type' => 1];
			$events[] = ['time' => $end->timestamp, 'type' => -1];
		}

		// Sort events
		usort($events, function ($a, $b) {
			if ($a['time'] == $b['time']) {
				return $a['type'] - $b['type']; // -1 (end) comes before 1 (start)
			}
			return ($a['time'] < $b['time']) ? -1 : 1;
		});

		$blocked = [];
		$current = 0;
		$startBlocked = null;

		foreach ($events as $event) {
			$prev = $current;
			$current += $event['type'];

			if ($prev < $maxLimit && $current >= $maxLimit) {
				$startBlocked = $event['time'];
			} else if ($prev >= $maxLimit && $current < $maxLimit) {
				if ($startBlocked !== null) {
					$blocked[] = [
						'start' => $startBlocked,
						'end' => $event['time']
					];
					$startBlocked = null;
				}
			}
		}

		return $blocked;
	}

	public function getSlots(Request $request, $id = null)
	{
		$settings = Settings::where('id', 1)->first();
		$offline_allowed = $settings ? ($settings->allow_offline_sessions = 1 ? true : false) : false;

		$schedules = Schedule::where('user_id', $id ?? auth()->id())->where('type', 'Period')->where('date', '>=', date('Y-m-d'))->get();

		// Active-only filter
		if ($request->has('active_only') && $request->active_only == 'true') {
			$schedules = Schedule::where('active', 1)
				->where('user_id', $id ?? auth()->id())
				->get();
		}

		$slots = collect(); // we will push items manually

		foreach ($schedules as $item) {
			$isWeekly = $item->type == 'Weekly';

			if ($isWeekly) {
				// cleanWeekday() must return an array of weekdays
				$weekdays = $this->cleanWeekday($item->days);

				foreach ($weekdays as $weekday) {
					$slots->push([
						'id'           => $item->id,
						'weekday'      => $weekday,
						'date'         => null,
						'start_time'   => substr($item->tfrom, 0, 5),
						'end_time'     => substr($item->tto, 0, 5),
						'session_type' => $item->session_type,
						'weekly'       => true,
					]);
				}
			} else {
				// Non-weekly: single slot per row
				$slots->push([
					'id'           => $item->id,
					'weekday'      => null,
					'date'         => $item->date ?? $item->dto,
					'start_time'   => substr($item->tfrom, 0, 5),
					'end_time'     => substr($item->tto, 0, 5),
					'session_type' => $item->session_type,
					'weekly'       => false,
				]);
			}
		}

		// Calculate blocked intervals
		$settings = Settings::where('id', 1)->first();
		$maxLimit = $settings ? (int)$settings->max_concurrent_sessions : 0;
		$blockedIntervals = $this->getBlockedIntervals($maxLimit);

		// If fetching for a mentor, include mentor info
		if ($id) {
			$user = User::find($id);
			return response()->json([
				'success' => true,
				'user' => [
					'id' => $user->id,
					'name' => $user->name,
				],
				'data' => $slots,
				'blocked_intervals' => $blockedIntervals,
				'offline_allowed' => $offline_allowed,
			], 200);
		}

		return response()->json(['success' => true, 'data' => $slots, 'blocked_intervals' => $blockedIntervals, 'offline_allowed' => $offline_allowed], 200);
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
			$dayName = Carbon::parse($validated['date'])->format('l');
			// Create slot
			$schedules = [];
			$sessionTypes = $validated['session_type'] === 'Both'
							? ['Online', 'Offline']
							: [$validated['session_type']];
			foreach ($sessionTypes as $sessionType) {
				$slotData = [
					'user_id' => auth()->id(),
					'type' => $validated['type'],
					'session_type' => $sessionType,
					'tfrom' => $validated['tfrom'],
					'tto' => $validated['tto'],
					'days' => $validated['type'] === 'Weekly' ? $validated['days'] : $dayName,
					'date' => $validated['type'] !== 'Weekly' ? $validated['date'] : null,
				];

				$schedules[] = Schedule::create($slotData);
			}

			return response()->json([
				'success' => true,
				'message' => __('mentor.slot_added_success'),
				'data' => collect($schedules)->map(function ($schedule) {
					return [
						'id' => $schedule->id,
						'weekday' => $schedule->days,
						'date' => $schedule->date,
						'start_time' => substr($schedule->tfrom, 0, 5),
						'end_time' => substr($schedule->tto, 0, 5),
						'session_type' => $schedule->session_type,
						'weekly' => $schedule->type === 'Weekly',
					];
				})
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
