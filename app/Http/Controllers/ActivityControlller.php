<?php

namespace App\Http\Controllers;

use App\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class ActivityControlller extends Controller
{
    public function index(Request $request)
    {
     $query = Activity::with('user');

     if ($request->user) {
        $query->whereHas('user', function ($q) use ($request) {
            $q->whereRaw('lower(first_name_en) like ?', [strtolower("%{$request->user}%")])
              ->orWhereRaw('lower(last_name_en) like ?', [strtolower("%{$request->user}%")]);
        });
     }

    if ($request->date_from && $request->date_to) {
        $query->whereBetween('activity_time', [$request->date_from, $request->date_to]);
    }

    if ($request->device) {
        $query->where('device', 'like', "%{$request->device}%");
    }

    if ($request->location) {
        $query->where('location', 'like', "%{$request->location}%");
    }

    if ($request->ip) {
        $query->where('ip_address', 'like', "%{$request->ip}%");
    }

        $activities = $query->orderBy('activity_time', 'desc')->get();
       
        // Dashboard Statistics
        $today = Carbon::today();
        $thisWeek = Carbon::now()->startOfWeek();
        $thisMonth = Carbon::now()->startOfMonth();

        $totalLoginsToday = Activity::whereDate('activity_time', $today)->count();
        $totalLoginsWeek = Activity::whereBetween('activity_time', [$thisWeek, Carbon::now()])->count();
        $totalLoginsMonth = Activity::whereBetween('activity_time', [$thisMonth, Carbon::now()])->count();

        $topDevices = Activity::select('device', DB::raw('count(*) as count'))
            ->groupBy('device')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $topBrowsers = Activity::select('browser', DB::raw('count(*) as count'))
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $loginsByCountry = Activity::select('location', DB::raw('count(*) as count'))
            ->groupBy('location')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $peakHours = Activity::select(DB::raw('HOUR(activity_time) as hour'), DB::raw('count(*) as count'))
            ->groupBy('hour')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $suspiciousLogins = Activity::select('user_id', DB::raw('COUNT(*) as login_count'), DB::raw('GROUP_CONCAT(location SEPARATOR ", ") as locations'))
            ->groupBy('user_id')
            ->havingRaw('login_count > 2')
            ->get()
            ->filter(function ($activity) {
                $locations = array_filter(explode(', ', $activity->locations));
                return count(array_unique($locations)) > 1;
            });
       return view('admin.activities.index', compact('activities', 'totalLoginsToday', 'totalLoginsWeek', 'totalLoginsMonth', 'topDevices', 'topBrowsers', 'loginsByCountry', 'peakHours', 'suspiciousLogins'));
    }


    public function destroy($id)
    {
        $activity = Activity::findOrFail($id);
        $activity->delete();

        return to_route('activities.index')->with('success', __('admin.activity_deleted'));
    }

}
