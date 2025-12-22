<?php

namespace App\Http\Controllers;

use App\MentorshipSchedule;
use Illuminate\Http\Request;

class MentorshipScheduleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();
        $orgIds = $user->organizations_ids;
        if(auth()->user()->hasRole('organizationManager')) {
            $scahedules = MentorshipSchedule::when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
                return $q->whereIn('organization_id', $orgIds);
            })->get();
        }elseif(auth()->user()->hasRole('Admin')) {
            $scahedules = MentorshipSchedule::all();
        }else{
            $scahedules = [];
        }

        return view('admin.settings.mentorship.schedule', compact('scahedules'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
       $request->validate([
           'value' => 'required|numeric|min:1',
           'unit' => 'required|in:hours,minutes,days,weeks'
       ]);

       // Check for duplicate schedule
       $user = auth()->user();
       $orgIds = $user->organizations_ids;
       $query = MentorshipSchedule::where('value', $request->value)
                                  ->where('unit', $request->unit);

       if(auth()->user()->hasRole('organizationManager')){
           $query->when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
               return $q->whereIn('organization_id', $orgIds);
           });
       }
       
       if($query->exists()) {
           return response()->json([
               'errors' => [
                   'value' => ['A schedule with this value and unit already exists.']
               ]
           ], 422);
       }

       $schedule = new MentorshipSchedule;
       $schedule->value = $request->value;
       $schedule->unit = $request->unit;
       $schedule->is_active = $request->has('is_active') ? 1 : 0;
       if(auth()->user()->hasRole('organizationManager')){
           $schedule->organization_id = auth()->user()->organization_id;
       }
       $schedule->save();

       return response()->json([
           'success' => true,
           'message' => 'Schedule created successfully',
           'schedule' => [
               'id' => $schedule->id,
               'value' => $schedule->value,
               'unit' => $schedule->unit,
               'is_active' => $schedule->is_active,
               'created_at' => $schedule->created_at,
               'updated_at' => $schedule->updated_at
           ]
       ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:mentorship_schedules,id',
            'value' => 'required|numeric|min:1',
            'unit' => 'required|in:hours,minutes,days,weeks',
            'is_active' => 'nullable|boolean'
        ]);

        $schedule = MentorshipSchedule::find($request->id);
        $user = auth()->user();
        $orgIds = $user->organizations_ids;

        // Check authorization
        if(auth()->user()->hasRole('organizationManager') &&
           !empty($orgIds) && !in_array($schedule->organization_id, $orgIds)) {
            return response()->json([
                'error' => 'Unauthorized to update this schedule'
            ], 403);
        }

        // Check for duplicate schedule (excluding current)
        $query = MentorshipSchedule::where('value', $request->value)
                                  ->where('unit', $request->unit)
                                  ->where('id', '!=', $request->id);

        if(auth()->user()->hasRole('organizationManager')){
            $query->when(!empty($orgIds) && !$user->hasRole('admin'), function ($q) use ($orgIds) {
                return $q->whereIn('organization_id', $orgIds);
            });
        }
        
        if($query->exists()) {
            return response()->json([
                'errors' => [
                    'value' => ['A schedule with this value and unit already exists.']
                ]
            ], 422);
        }

        $schedule->value = $request->value;
        $schedule->unit = $request->unit;
        $schedule->is_active = $request->has('is_active') ? ($request->is_active ? 1 : 0) : $schedule->is_active;
        $schedule->save();

        return response()->json([
            'success' => true,
            'message' => 'Schedule updated successfully',
            'schedule' => [
                'id' => $schedule->id,
                'value' => $schedule->value,
                'unit' => $schedule->unit,
                'is_active' => $schedule->is_active,
                'created_at' => $schedule->created_at,
                'updated_at' => $schedule->updated_at
            ]
        ]);
    }
        /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:mentorship_schedules,id'
        ]);

        $schedule = MentorshipSchedule::find($request->id);
        $user = auth()->user();
        $orgIds = $user->organizations_ids;

        // Check authorization
        if(auth()->user()->hasRole('organizationManager') &&
           !empty($orgIds) && !in_array($schedule->organization_id, $orgIds)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized to delete this schedule'
            ], 403);
        }
        
        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Schedule deleted successfully'
        ]);
    }
}
