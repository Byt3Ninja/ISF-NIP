<?php

namespace App\Http\Controllers\API\Mentors;

use Carbon\Carbon;
use App\Services\TimezoneService;
use App\Traits\ApiResponder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Resources\ScheduleResource;
use App\Http\Requests\StoreScheduleRequest;
use App\Http\Requests\UpdateScheduleRequest;

class ScheduleController extends Controller
{
    use ApiResponder;
    /**
 * @OA\Get(
 *     path="/api/mentors/{mentor_id}/weekly-slots",
 *     summary="Get weekly schedule of a mentor",
 *     tags={"Schedules"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="mentor_id",
 *         in="path",
 *         required=true,
 *         description="Mentor ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="start_date",
 *         in="query",
 *         description="Start date for the schedule (Y-m-d)",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Parameter(
 *         name="end_date",
 *         in="query",
 *         description="End date for the schedule (Y-m-d)",
 *         @OA\Schema(type="string", format="date")
 *     ),
 *     @OA\Parameter(
 *         name="timezone",
 *         in="query",
 *         description="Timezone for slot times",
 *         @OA\Schema(type="string", example="Asia/Dubai")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Schedule slots retrieved",
 *         @OA\JsonContent(
 *             @OA\Property(property="mentorId", type="integer", example=1),
 *             @OA\Property(property="timezone", type="string", example="Asia/Dubai"),
 *             @OA\Property(
 *                 property="slots",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="date", type="string", example="2025-07-28"),
 *                     @OA\Property(
 *                         property="slots",
 *                         type="array",
 *                         @OA\Items(
 *                             @OA\Property(property="start", type="string", example="10:00:00"),
 *                             @OA\Property(property="end", type="string", example="11:00:00"),
 *                             @OA\Property(property="status", type="string", example="reserved")
 *                         )
 *                     )
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
    public function index(Request $request, $mentor_id)
 {
     $user = auth()->user();
     if (!$user->hasRole('Mentor') && !$user->hasRole('Registered') && !$user->hasRole('Admin')) {
        return $this->forbidden(__('api.forbidden'));
    }
    
    $request->validate([
        'start_date' => 'nullable|date',
        'end_date'   => 'nullable|date|after_or_equal:start_date',
        'timezone'   => 'nullable|timezone',
    ]);

    // GV-9566: schedules (tfrom/tto) and zoom_date are platform wall-clock, not UTC.
    // Days are walked in the platform zone; times are converted to the requested zone
    // (default: the platform zone, i.e. as stored).
    $platform = TimezoneService::platform();
    $timezone = $request->input('timezone') ?: $platform;

    $startDate = Carbon::parse($request->input('start_date', now($timezone)->startOfWeek()->toDateString()), $platform)
        ->startOfDay();

    $endDate = Carbon::parse($request->input('end_date', $startDate->copy()->addDays(6)->toDateString()), $platform)
        ->endOfDay();

    if ($endDate->diffInDays($startDate) > 30) {
        return $this->unprocessable([
            'date_range' => [__('api.date_range_limit')]
        ]);
    }

    $availableSlots = DB::table('schedules')
        ->where('user_id', $mentor_id)
        ->where('type', 'Weekly')
        ->where('active', 1)
        ->get();

  $reservedSessions = DB::table('mentorship_requests as mr')
    ->join('users as u', 'mr.user_id', '=', 'u.id')
    ->join('companies as c', 'u.company_id', '=', 'c.id')
    ->select(
        'mr.id as session_id',
        'mr.zoom_date as session_date', 
        'mr.organization_id',
        DB::raw("TIME_FORMAT(mr.zoom_date, '%H:%i:%s') as start_time"),
        DB::raw("SEC_TO_TIME(mr.period * 60) as duration"), 
        'c.name_en as startup_name'
    )
    ->where('mr.mentor_id', $mentor_id)
    ->whereBetween(DB::raw('DATE(mr.zoom_date)'), [$startDate->toDateString(), $endDate->toDateString()])
    ->get();

    $reservedByDate = [];
    foreach ($reservedSessions as $r) {
       $at = TimezoneService::wallClock($r->session_date, TimezoneService::platform($r->organization_id ? (int) $r->organization_id : null));
       if (!$at) continue;
       $start = $at->copy()->setTimezone($timezone);
      $end = $start->copy()->addMinutes($r->period ?? 60);

    // Keyed by platform date, like the available slots below.
    $reservedByDate[$at->copy()->setTimezone($platform)->toDateString()][] = [
        'start' => $start->format('H:i:s'),
        'end' => $end->format('H:i:s'),
        'status' => 'reserved',
        'sessionId' => $r->session_id,
        'startupName' => $r->startup_name,
        'reservedAt' => $start->toIso8601String(),
    ];
    }

    $slots = [];
    $current = $startDate->copy();

    while ($current->lte($endDate)) {
        $dateStr = $current->format('Y-m-d');
        $dayName = strtolower($current->format('l'));

        $dayAvailable = $availableSlots->where('days', ucfirst($dayName))->map(function ($slot) use ($timezone, $dateStr, $platform) {
            return [
                'id' => $slot->id,
                'start' => Carbon::parse($dateStr . ' ' . $slot->tfrom, $platform)->setTimezone($timezone)->format('H:i:s'),
                'end' => Carbon::parse($dateStr . ' ' . $slot->tto, $platform)->setTimezone($timezone)->format('H:i:s'),
                'status' => 'available',
            ];
        })->values()->toArray();

        $dayReserved = $reservedByDate[$dateStr] ?? [];

        $mergedSlots = array_merge($dayAvailable, $dayReserved);
        usort($mergedSlots, fn($a, $b) => strcmp($a['start'], $b['start']));

        if (!empty($mergedSlots)) {
            $slots[] = [
                'day' => ucfirst($dayName),
                'slots' => $mergedSlots,
            ];
        }

        $current->addDay();
    }

     return $this->success([
        'mentorId' => (int) $mentor_id,
        'timezone' => $timezone,
        'slots' => $slots,
    ]);

 }

 /**
 * @OA\Post(
 *     path="/api/v1/availability",
 *     summary="Create new weekly schedule for mentor",
 *     tags={"Schedules"},
 *     security={{"sanctum":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"days", "type", "status"},
 *             @OA\Property(property="days", type="string", example="Monday,Tuesday"),
 *             @OA\Property(property="type", type="string", example="Weekly"),
 *             @OA\Property(property="status", type="string", example="Available"),
 *             @OA\Property(property="notes", type="string", example="Morning slots"),
 *             @OA\Property(
 *                 property="time_slots",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="start", type="string", example="09:00:00"),
 *                     @OA\Property(property="end", type="string", example="10:00:00")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(response=201, description="Schedule created successfully"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=409, description="Slot conflict"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */

  public function store(StoreScheduleRequest $request)
{
    $mentor = auth()->user();

    if (!$mentor->hasRole('Mentor')) {
        return $this->forbidden(__('api.forbidden'));
    }

    $validated = $request->validated();

    $days = array_map('trim', explode(',', $validated['days']));
    $status = $validated['status'] ?? 'Unavailable';
    $type = $validated['type'];
    $timeSlots = $validated['time_slots'] ?? [];
    $notes = $validated['notes'] ?? null;

    if ($status === 'Available') {
        if (count($timeSlots) > 10) {
            return $this->unprocessable([
                'time_slots' => ['You can define a maximum of 10 time slots per day.']
            ]);
        }
    }

    $createdSchedules = [];

    foreach ($days as $day) {
        $existingSchedules = DB::table('schedules')
            ->where('user_id', $mentor->id)
            ->where('days', $day)
            ->select('tfrom', 'tto')
            ->get();

        $seen = [];

        foreach ($timeSlots as $slot) {
            $start = $slot['start'];
            $end = $slot['end'];

            if ($end <= $start) {
                return $this->unprocessable([
                    'time_slots' => ["End time $end must be after start time $start."]
                ]);
            }

            $duration = strtotime($end) - strtotime($start);
            if ($duration < 60) {
                return $this->unprocessable([
                    'time_slots' => ["Slot $start - $end must be at least 1 minute long."]
                ]);
            }

            foreach ($seen as $existing) {
                if (($start < $existing['end']) && ($end > $existing['start'])) {
                    return $this->conflict("Slot $start - $end overlaps with {$existing['start']} - {$existing['end']} (in request)");
                }
            }

            foreach ($existingSchedules as $existing) {
                if ($start === $existing->tfrom && $end === $existing->tto) {
                    return $this->conflict("Slot $start - $end already exists on $day.");
                }

                if (($start < $existing->tto) && ($end > $existing->tfrom)) {
                    return $this->conflict("Slot $start - $end overlaps with existing {$existing->tfrom} - {$existing->tto} on $day.");
                }
            }

            $seen[] = ['start' => $start, 'end' => $end];
        }

        DB::table('schedules')
            ->where('user_id', $mentor->id)
            ->where('days', $day)
            ->delete();

        if ($status === 'Available') {
            foreach ($timeSlots as $slot) {
                $id = DB::table('schedules')->insertGetId([
                    'user_id'    => $mentor->id,
                    'type'       => $type,
                    'days'       => $day,
                    'tfrom'      => $slot['start'],
                    'tto'        => $slot['end'],
                    'notes'      => $notes,
                    'active'     => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $createdSchedules[] = DB::table('schedules')->where('id', $id)->first();
            }
        } else {
            $createdSchedules[] = [
                'user_id' => $mentor->id,
                'day'     => $day,
                'status'  => 'Unavailable',
            ];
        }
    }
    return $this->created(ScheduleResource::collection($createdSchedules), __('api.schedule_created'), [], 201);
}

/**
 * @OA\Put(
 *     path="/api/v1/availability/{user_id}",
 *     summary="Update an existing weekly schedule",
 *     tags={"Schedules"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Schedule ID to update",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"days", "type", "status"},
 *             @OA\Property(property="days", type="string", example="Monday"),
 *             @OA\Property(property="type", type="string", example="Weekly"),
 *             @OA\Property(property="status", type="string", example="Available"),
 *             @OA\Property(property="notes", type="string", example="Updated availability"),
 *             @OA\Property(
 *                 property="time_slots",
 *                 type="array",
 *                 @OA\Items(
 *                     @OA\Property(property="start", type="string", example="13:00:00"),
 *                     @OA\Property(property="end", type="string", example="14:00:00")
 *                 )
 *             )
 *         )
 *     ),
 *     @OA\Response(response=200, description="Schedule updated successfully"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=409, description="Slot conflict"),
 *     @OA\Response(response=422, description="Validation error")
 * )
 */
    public function update(UpdateScheduleRequest $request, $id)
    {
       $mentor = auth()->user();

        if (!$mentor->hasRole('Mentor')) {
          return $this->forbidden(__('api.forbidden'));
        }

    $validated = $request->validated();

    $day = $validated['days'];
    $status = $validated['status'] ?? 'Unavailable';
    $type = $validated['type'] ?? 'Weekly';
    $timeSlots = $validated['time_slots'] ?? [];
    $notes = $validated['notes'] ?? '';

    if ($status === 'Available' && count($timeSlots) > 10) {
        return $this->unprocessable([
            'time_slots' => ['You can define a maximum of 10 time slots per day.']
        ]);
    }

    $seen = [];

    foreach ($timeSlots as $slot) {
        $start = $slot['start'];
        $end = $slot['end'];

        if ($end <= $start) {
            return $this->unprocessable([
                'time_slots' => ["End time $end must be after start time $start."]
            ]);
        }

        $duration = strtotime($end) - strtotime($start);
        if ($duration < 60) {
            return $this->unprocessable([
                'time_slots' => ["Slot $start - $end must be at least 1 minute long."]
            ]);
        }

        foreach ($seen as $existing) {
            if (($start < $existing['end']) && ($end > $existing['start'])) {
                return $this->conflict("Slot $start - $end overlaps with {$existing['start']} - {$existing['end']}");
            }
        }

        $seen[] = ['start' => $start, 'end' => $end];
    }

    DB::table('schedules')
        ->where('user_id', $mentor->id)
        ->where('days', $day)
        ->delete();

    $created = [];

    if ($status === 'Available') {
        foreach ($timeSlots as $slot) {
            $id = DB::table('schedules')->insertGetId([
                'user_id'    => $mentor->id,
                'type'       => $type,
                'days'       => $day,
                'tfrom'      => $slot['start'],
                'tto'        => $slot['end'],
                'active'     => 1,
                'notes'      => $notes,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $created[] = DB::table('schedules')->where('id', $id)->first();
        }
    } else {
        $created[] = [
            'user_id' => $mentor->id,
            'days'    => $day,
            'status'  => 'Unavailable',
            'type'    => $type,
            'active'  => 0
        ];
    }

    return $this->success(ScheduleResource::collection($created),[], __('api.schedule_updated'), 200);
}

/**
 * @OA\Get(
 *     path="/api/slots/{id}",
 *     summary="Get a specific schedule slot",
 *     tags={"Schedules"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Schedule slot ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="timezone",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="string", example="Asia/Dubai")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Slot retrieved",
 *         @OA\JsonContent(
 *             @OA\Property(property="slotId", type="integer", example=5),
 *             @OA\Property(property="mentorId", type="integer", example=3),
 *             @OA\Property(property="day", type="string", example="Tuesday"),
 *             @OA\Property(property="start", type="string", example="09:00:00"),
 *             @OA\Property(property="end", type="string", example="10:00:00"),
 *             @OA\Property(property="timezone", type="string", example="Asia/Dubai"),
 *             @OA\Property(property="status", type="string", example="available")
 *         )
 *     ),
 *     @OA\Response(response=404, description="Slot not found"),
 *     @OA\Response(response=403, description="Forbidden")
 * )
 */
public function show(Request $request, $id)
{
    $user = auth()->user();

    if (!$user->hasRole('Mentor') && !$user->hasRole('Registered')) {
        return $this->forbidden(__('api.forbidden'));
    }
    // GV-9566: tfrom/tto are platform wall-clock (default output: as stored).
    $platform = TimezoneService::platform();
    $timezone = $request->input('timezone') ?: $platform;

    $slot = DB::table('schedules')->where('id', $id)->where('active', 1)->first();

    if (!$slot) {
        return $this->notFound(__('api.slot_not_found'));
    }

    return response()->json([
        'slotId' => $slot->id,
        'mentorId' => $slot->user_id,
        'day' => $slot->days,
        'start' => Carbon::parse($slot->tfrom, $platform)->setTimezone($timezone)->format('H:i:s'),
        'end' => Carbon::parse($slot->tto, $platform)->setTimezone($timezone)->format('H:i:s'),
        'timezone' => $timezone,
        'status' => 'available'
    ]);
}

/**
 * @OA\Delete(
 *     path="/api/v1/availability/{id}",
 *     summary="Delete a specific schedule slot",
 *     tags={"Schedules"},
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Schedule slot ID",
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(response=200, description="Schedule deleted successfully"),
 *     @OA\Response(response=403, description="Forbidden"),
 *     @OA\Response(response=404, description="Schedule not found")
 * )
 */
public function destroy($id)
{
    $mentor = auth()->user();

    if (!$mentor->hasRole('Mentor')) {
        return $this->forbidden(__('api.forbidden'));
    }

    $schedule = DB::table('schedules')
        ->where('id', $id)
        ->where('user_id', $mentor->id)
        ->first();

    if (!$schedule) {
        return $this->notFound(__('api.schedule_not_found'));
    }

    DB::table('schedules')->where('id', $id)->delete();

    return $this->success(null,[] ,__('api.schedule_deleted'));
}

}
