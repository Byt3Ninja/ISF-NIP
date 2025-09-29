<?php

namespace App\Http\Controllers;

use App\User;
use App\Cycle;
use Carbon\Carbon;
use App\ReminderLog;
use Illuminate\Http\Request;

class ReminderLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ReminderLog::with(['user','program']);

        if ($request->filled('program_id')) {
            $query->where('cycle_id', $request->program_id);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from') || $request->filled('date_to')) {
            $from = $request->filled('date_from') ? Carbon::parse($request->date_from)->startOfDay() : Carbon::minValue();
            $to = $request->filled('date_to') ? Carbon::parse($request->date_to)->endOfDay() : Carbon::now();
            $query->whereBetween('created_at', [$from, $to]);
        }

        $users = User::orderBy('name')->get();
        $programs = Cycle::orderBy('name')->get();

        return view('admin.reminder_logs.index', compact('logs','users','programs'));
        
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
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(ReminderLog $reminderLog)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ReminderLog $reminderLog)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ReminderLog $reminderLog)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        try {
            $reminderLog = ReminderLog::find($request->id);
            
            if (!$reminderLog) {
                return response()->json([
                    'success' => false,
                    'message' => 'Reminder log not found'
                ], 404);
            }
            
            $reminderLog->delete();
            
            // Check if it's an AJAX request
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Reminder log deleted successfully'
                ]);
            }
            
            // For non-AJAX requests, redirect back with success message
            return back()->with('message', 'Reminder log deleted successfully');
            
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error deleting reminder log: ' . $e->getMessage()
                ], 500);
            }
            
            return back()->with('message2', 'Error deleting reminder log');
        }
    }
}
