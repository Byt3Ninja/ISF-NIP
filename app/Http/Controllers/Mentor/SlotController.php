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
			$offline_allowed = $settings->allow_offline_sessions == 1 ? true : false;			
			
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
		$offline_allowed = $settings ? ($settings->allow_offline_sessions == 1 ? true : false) : false;
		$mentorId = $id ?? auth()->id();

		$isOwner = $mentorId == auth()->id();

		$query = Schedule::where('user_id', $mentorId)
						->where(function ($query) use ($isOwner) {$query
								->where(function ($weekly) use ($isOwner) {
									$weekly->whereNull('date');
									// dfrom filter: only hide not-yet-active Weekly slots for non-owners (booking view)
									if (!$isOwner) {
										$weekly->where(function ($q) {
											$q->whereNull('dfrom')
											  ->orWhere('dfrom', '<=', date('Y-m-d'));
										});
									}
								})
								//->orWhere('date', '>=', date('Y-m-d'))
								->orWhere(function ($q) {
									$q->where('date', '>', date('Y-m-d'))
									  ->orWhere(function ($q2) {
										$q2->where('date', date('Y-m-d'))
										   ->where('tto', '>', date('H:i:s'));
									  });
								});
						});
						// Booked Period slots are intentionally NOT filtered out here: they are
						// returned and rendered as locked/disabled on the frontend via
						// blocked_intervals (see mentor bookings block below), so a manager sees
						// why a slot is unavailable instead of it silently vanishing.

		// Active-only filter
		if ($request->has('active_only') && $request->active_only == 'true') {
			// placeholder for future active-state filtering
		}

		$schedules = $query->get();
			
		if($offline_allowed == false){
			$schedules = $schedules->where('session_type', '!=','Offline');
		}

		// Resolve location and address based on cycle/program or organization
		$cycleId = $request->input('cycle_id');
		$organizationId = $request->input('organization_id');
		
		$cycle = null;
		if ($cycleId) {
			$cycle = \App\Cycle::with('organization')->find($cycleId);
		}
		if (!$cycle) {
			$user = auth()->user();
			if ($user && $user->company) {
				$cycle = $user->company->cycledata;
			}
		}

		$location = null;
		$address = null;
		if ($cycle) {
			$location = $cycle->offline_location_map ?: ($cycle->organization->google_map_url ?: null);
			$address = $cycle->offline_location_address ?: ($cycle->organization->address ?: null);
		} elseif ($organizationId) {
			$org = \App\Organization::find($organizationId);
			if ($org) {
				$location = $org->google_map_url;
				$address = $org->address;
			}
		}

		$slots = collect(); // we will push items manually

		foreach ($schedules as $item) {
			$isWeekly = $item->type == 'Weekly';

			$slotLoc = null;
			$slotAddr = null;
			if ($item->session_type === 'Offline') {
				$slotLoc = $location;
				$slotAddr = $address;
			}

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
						'location'     => $slotLoc,
						'Address'      => $slotAddr,
						'starts_from'  => $item->dfrom ? $item->dfrom->format('Y-m-d') : null,
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
					'location'     => $slotLoc,
					'Address'      => $slotAddr,
				]);
			}
			
		}
		// Calculate blocked intervals (max concurrent sessions)
		$settings = Settings::where('id', 1)->first();
		$maxLimit = $settings ? (int)$settings->max_concurrent_sessions : 0;
		$blockedIntervals = $this->getBlockedIntervals($maxLimit);

		// اضافة حجوزات المنتور كـ blocked intervals عشان الـ weekly slots تتفلتر per-date فى الـ frontend
		$mentorBookings = \DB::table('mentorship_requests')
			->where('mentor_id', $mentorId)
			->where(function ($q) {
				$q->where('approved', 'Yes')->orWhereNull('approved');
			})
			->where('zoom_date', '>=', now()->toDateString())
			->get(['zoom_date', 'period']);

		foreach ($mentorBookings as $booking) {
			$start = Carbon::parse($booking->zoom_date);
			$end = (clone $start)->addMinutes($booking->period ?: 60);
			$blockedIntervals[] = [
				'start' => $start->timestamp,
				'end' => $end->timestamp,
			];
		}
		
		// If user is admin/subadmin/orgManager, return list of startups in that cycle
		$startups = [];
		if ($cycleId && auth()->user()->hasAnyRole(['admin', 'organizationManager', 'subadmin'])) {
			$startups = \App\Company::where('cycle', $cycleId)
				->with('user')
				->get()
				->map(function ($c) {
					return [
						'id' => $c->user->id ?? 0,
						'name' => $c->name . ' (' . ($c->user->name ?? '') . ')',
					];
				})
				->filter(fn($item) => $item['id'] > 0)
				->values();
		}

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
				'startups' => $startups,
			], 200);
		}

		return response()->json(['success' => true, 'data' => $slots, 'blocked_intervals' => $blockedIntervals, 'offline_allowed' => $offline_allowed, 'startups' => $startups], 200);
	}


	public function store(Request $request)
	{
		
		$settings = Settings::where('id', 1)->first();
			$validated = $request->validate([
					'session_type' => 'required',
					'tfrom' => 'required',
					'tto' => 'required',
					'type' => 'required|in:Weekly,Period',
					'days' => 'required_if:type,Weekly',
					'date' => 'required_if:type,Period',
			]);
			
			$schedules = [];
			$sessionTypes = $validated['session_type'] === 'Both'
							? ['Online', 'Offline']
							: [$validated['session_type']];
			foreach ($sessionTypes as $sessionType) {

				
				$offline_allowed = $settings->allow_offline_sessions;
				if ($sessionType == 'Offline'){
					if ($offline_allowed == 0) {
							return response()->json([
								'success' => false,
								'message' => __('mentor.Offline_not_allowed'),
							], 422);
					}
				}
				
				if($validated['type'] == 'Period' && $validated['date'].' '.$validated['tfrom'] <= date('Y-m-d H:i')){
					return response()->json([
							'success' => false,
							'message' => __('mentor.this_time_before_now'),
					], 422);
				}
				$dayName = $validated['type'] !== 'Weekly'
					? Carbon::parse($validated['date'])->format('l')
					: ($this->weekdayMap[$validated['days']] ?? $validated['days']);

				// Build base search query with proper overlap detection
				$query = Schedule::where('user_id', auth()->id())
						->where('type', $validated['type'])
						->where('tfrom', '<=', $validated['tto'].':00')
						->where('tto', '>=', $validated['tfrom'].':00')
						->where('session_type', $sessionType);

				// Add type-specific conditions
				if ($validated['type'] === 'Weekly') {
						$query->where('days', $dayName);
				} else {
						$query->where('date', $validated['date']);
				}
			
				// Check if duplicate exists
				$getschedule = $query->orderBy('created_at', 'desc')->first();
				//dd($validated, $sessionType, $getschedule);
				if ($getschedule != null) {
					// If existing slot hasn't started yet (dfrom in future), replace it
					if ($getschedule->dfrom && $getschedule->dfrom > now()) {
						return response()->json([
								'success' => false,
								// message includes between time and date of existing slot for better UX
								'message' =>  __('mentor.overlap', ['date' => $validated['date'],'from' => $getschedule->tfrom, 'to' => $getschedule->tto]),
						], 422);
						// $getschedule->delete();
					} elseif ($getschedule->tfrom == $validated['tfrom'].':00' && $validated['tto'] >= $getschedule->tto) {
						return response()->json([
							'success' => false,
							'message' =>  __('mentor.overlap', ['date' => $validated['date'],'from' => $getschedule->tfrom, 'to' => $getschedule->tto]),
						], 422);
					} else {
						return response()->json([
								'success' => false,
								'message' =>  __('mentor.overlap', ['date' => $validated['date'],'from' => $getschedule->tfrom, 'to' => $getschedule->tto]),
						], 422);
					}
				}

				// Cross-type check: Weekly blocks Period
				if ($validated['type'] === 'Period') {
					$weeklySlots = Schedule::where('user_id', auth()->id())
						->where('type', 'Weekly')
						->where('session_type', $sessionType)
						->where('tfrom', '<', $validated['tto'].':00')
						->where('tto', '>', $validated['tfrom'].':00')
						->get();

					foreach ($weeklySlots as $weekly) {
						$weekdays = $this->cleanWeekday($weekly->days);
						if (in_array($dayName, $weekdays)) {
							return response()->json([
								'success' => false,
								'message' =>  __('mentor.between', ['from' => $weekly->tfrom, 'to' => $weekly->tto]),
							], 422);
						}
					}
				}

				// Cross-type check: Period exists → Weekly starts after it
				$dfrom = null;
				if ($validated['type'] === 'Weekly') {
					$conflictingPeriods = Schedule::where('user_id', auth()->id())
						->where('type', 'Period')
						->where('session_type', $sessionType)
						->where('tfrom', '<', $validated['tto'].':00')
						->where('tto', '>', $validated['tfrom'].':00')
						->orderBy('date', 'desc')
						->get();

					foreach ($conflictingPeriods as $period) {
						$periodDay = $this->cleanWeekday($period->days);
						if (in_array($dayName, $periodDay)) {
							$dfrom = Carbon::parse($period->date)
								->addDay()
								->toDateString();
							break;
						}
					}
				}

				// Create slot
				$slotData = [
					'user_id' => auth()->id(),
					'type' => $validated['type'],
					'session_type' => $sessionType,
					'tfrom' => $validated['tfrom'],
					'tto' => $validated['tto'],
					'days' => $dayName,
					'date' => $validated['type'] !== 'Weekly' ? $validated['date'] : null,
					'dfrom' => $dfrom,
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
						'starts_from' => $schedule->dfrom ? $schedule->dfrom->format('Y-m-d') : null,
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
							$slotData['date'] = Carbon::parse($day)->toDateString(); // here $day must be a date (Y-m-d)
							$slotData['dfrom'] = Carbon::parse($day)->toDateString(); // set dfrom same as date to prevent immediate availability of copied Weekly slots
							$slotData['days'] = $day;
							unset($slotData['days']); // remove weekday if exists
					}
					// if the original slot equals new slot, skip creation to prevent duplicates
					if(isset($slotData['date'])) {
						$found = Schedule::where('date', $slotData['date'])->where('days', $slotData['days'])->first();
					} else {
						$found = null;
					}
					if ($found && $found->tfrom == $slotData['tfrom'] && $found->tto == $slotData['tto'] && $found->session_type == $slotData['session_type']) {
						return response()->json([
								'success' => false,
								'message' => __('mentor.slot_already_exists') . ' ' . __('mentor.between', ['from' => $found->tfrom, 'to' => $found->tto]),
						], 422);
					}
					// 4. Create new duplicated schedule
					$newSchedule = Schedule::create($slotData);
	
					$isWeekly = $newSchedule->type === 'Weekly' ?? 'period';
					
					// 5. Format response slot
					$createdSlots[] = [
							'id'           => $newSchedule->id,
							'weekday'      => $isWeekly ? $newSchedule->days : null,
							'date'         => $isWeekly ? null : ($newSchedule->date ?? $newSchedule->tto),
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
