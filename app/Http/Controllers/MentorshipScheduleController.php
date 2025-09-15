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
        if(auth()->user()->hasRole('organizationManager')) {   
            $scahedules = MentorshipSchedule::where('organization_id', auth()->user()->organization_id)->get();
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
           'value' => 'required',
           'unit' => 'required'
       ]);

       $schedule = new MentorshipSchedule;
       $schedule->value = $request->value;
       $schedule->unit = $request->unit;
       $schedule->is_active = '1';
       if(auth()->user()->hasRole('organizationManager')){
       $schedule->organization_id = auth()->user()->organization_id;
       }
       $schedule->save();


       return response()->json($schedule);
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
            'id' => 'required',
            'value' => 'required',
            'unit' => 'required',
            'is_active' => 'nullable'
        ]);

        $schedule = MentorshipSchedule::find($request->id);
        $schedule->value = $request->value;
        $schedule->unit = $request->unit;
        $schedule->is_active = $request->is_active;
        $schedule->save();

        return response()->json($schedule);

    }
    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $schedule = MentorshipSchedule::find($id);
        $schedule->delete();

        return response()->json($schedule);

    }
}
