<?php

namespace App\Http\Controllers;

use App\User;
use App\Activity;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class ActivityController extends Controller
{
   public function index(Request $request)
    {
        abort_unless(auth()->user()->ableTo('activities.index'), 403);
        $authUser = auth()->user();
        $isAdmin = $authUser->hasRole('admin');

        // For non-admin users, restrict activities to their accessible organizations
        $orgUserIds = null;
        if (!$isAdmin) {
            $orgIds = $authUser->organizations_ids;
            $orgUserIds = User::whereIn('organization_id', $orgIds)->pluck('id');
        }

        $query = Activity::with('user');

        if ($orgUserIds !== null) {
            $query->whereIn('user_id', $orgUserIds);
        }

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

        $statsQuery = Activity::query();
        if ($orgUserIds !== null) {
            $statsQuery->whereIn('user_id', $orgUserIds);
        }

        $totalLogins = $statsQuery->count();
        $totalLoginsToday = (clone $statsQuery)->whereDate('activity_time', $today)->count();
        $totalLoginsWeek = (clone $statsQuery)->whereBetween('activity_time', [$thisWeek, Carbon::now()])->count();
        $totalLoginsMonth = (clone $statsQuery)->whereBetween('activity_time', [$thisMonth, Carbon::now()])->count();

        $topDevices = (clone $statsQuery)->select('device', DB::raw('count(*) as count'))
            ->groupBy('device')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $topBrowsers = (clone $statsQuery)->select('browser', DB::raw('count(*) as count'))
            ->groupBy('browser')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $loginsByCountry = (clone $statsQuery)->select('location', DB::raw('count(*) as count'))
            ->groupBy('location')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $peakHours = (clone $statsQuery)->select(DB::raw('HOUR(activity_time) as hour'), DB::raw('count(*) as count'))
            ->groupBy('hour')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        $suspiciousQuery = Activity::select(
                'user_id',
                DB::raw('COUNT(*) as login_count'),
                DB::raw('GROUP_CONCAT(DISTINCT location SEPARATOR ", ") as locations'),
                DB::raw('COUNT(DISTINCT location) as location_count'),
                DB::raw('MAX(activity_time) as last_login'),
                DB::raw('GROUP_CONCAT(DISTINCT ip_address SEPARATOR ", ") as ip_addresses'),
                DB::raw('GROUP_CONCAT(DISTINCT browser SEPARATOR ", ") as browsers'),
                DB::raw('GROUP_CONCAT(DISTINCT device SEPARATOR ", ") as devices')
            )
            ->with(['user']);

        if ($orgUserIds !== null) {
            $suspiciousQuery->whereIn('user_id', $orgUserIds);
        }

        $suspiciousCollection = $suspiciousQuery
            ->groupBy('user_id')
            ->havingRaw('login_count > 5 OR COUNT(DISTINCT location) > 1')
            ->orderByDesc('login_count')
            ->get()
            ->map(function ($activity) use ($orgUserIds) {
                $locations = array_filter(explode(', ', $activity->locations));
                $uniqueLocations = array_unique($locations);
                $loginCount = $activity->login_count;
                $locationCount = count($uniqueLocations);

                // Calculate risk level
                $riskLevel = 'low';
                if ($loginCount > 50 || $locationCount > 3) {
                    $riskLevel = 'high';
                } elseif ($loginCount > 20 || $locationCount > 1) {
                    $riskLevel = 'medium';
                }

                $activity->risk_level = $riskLevel;
                $activity->unique_locations = $uniqueLocations;

                // Get actual location counts for this user
                $locationCounts = Activity::where('user_id', $activity->user_id)
                    ->select('location', DB::raw('COUNT(*) as count'))
                    ->groupBy('location')
                    ->pluck('count', 'location')
                    ->toArray();

                $activity->location_counts = $locationCounts;

                return $activity;
            })
            ->filter(function ($activity) {
                return count($activity->unique_locations) > 1 || $activity->login_count > 10;
            });

        // Add detailed activities for each suspicious user
        $suspiciousCollection->each(function ($suspiciousLogin) {
            $totalActivitiesCount = Activity::where('user_id', $suspiciousLogin->user_id)->count();
            $suspiciousLogin->recent_activities = Activity::where('user_id', $suspiciousLogin->user_id)
                ->orderBy('activity_time', 'desc')
                ->limit(10)
                ->get();
            $suspiciousLogin->has_more_activities = $totalActivitiesCount > 10;
            $suspiciousLogin->total_activities_count = $totalActivitiesCount;
        });

        // Paginate the collection manually
        $perPage = 10;
        $currentPage = $request->input('page', 1);
        $suspiciousLogins = new \Illuminate\Pagination\LengthAwarePaginator(
            $suspiciousCollection->forPage($currentPage, $perPage),
            $suspiciousCollection->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return view('admin.activities.index', compact('activities', 'totalLogins', 'totalLoginsToday', 'totalLoginsWeek', 'totalLoginsMonth', 'topDevices', 'topBrowsers', 'loginsByCountry', 'peakHours', 'suspiciousLogins'));
    }

    public function destroy($id)
    {
        abort_unless(auth()->user()->ableTo('activities.index'), 403);
        $activity = Activity::findOrFail($id);
        // A non-admin may only remove log entries of users in their own organizations.
        if (!auth()->user()->hasRole('admin')) {
            $this->authorizeTargetUser($activity->user_id);
        }
        $activity->delete();

        return to_route('activities.index')->with('success', __('admin.activity_deleted'));
    }

    public function forceLogout(Request $request)
    {
        abort_unless(auth()->user()->ableTo('activities.index'), 403);
        $userId = $request->user_id;
        
        // In a real application, you would invalidate user sessions here
        // For example: DB::table('sessions')->where('user_id', $userId)->delete();        
        return response()->json(['success' => true, 'message' => __('admin.User_forcefully_logged_out')]);
    }

    public function resetPassword(Request $request)
    {
        abort_unless(auth()->user()->ableTo('activities.index'), 403);
        $l = app()->getLocale();

        $userId = $request->user_id;
        $user = $this->authorizeTargetUser($userId);
        
        // Generate a random password
        $newPassword = Str::random(12);
        $user->password = Hash::make($newPassword);
        $user->save();
        
        // In a real application, you would send this password to the user via email
        
        return response()->json(['success' => true, 'message' => __('admin.Password_reset_successfully'), 'new_password' => $newPassword]);
    }

    public function blockIp(Request $request)
    {
        abort_unless(auth()->user()->ableTo('activities.index'), 403);
        $ipAddress = $request->ip_address;
        
        // In a real application, you would add this IP to a blacklist table
        // For example: BlockedIp::create(['ip_address' => $ipAddress]);
        
        return response()->json(['success' => true, 'message' => __('admin.IP_blocked_successfully')]);
    }

    public function loadMoreActivities(Request $request)
    {
        abort_unless(auth()->user()->ableTo('activities.index'), 403);
        $userId = $request->user_id;
        $this->authorizeTargetUser($userId);
        $offset = $request->offset ?? 0;
        $limit = 10;
        
        $totalCount = Activity::where('user_id', $userId)->count();
        
        $activities = Activity::where('user_id', $userId)
            ->orderBy('activity_time', 'desc')
            ->skip($offset)
            ->limit($limit)
            ->get()
            ->map(function ($activity) {
                return [
                    'id' => $activity->id,
                    'formatted_date' => $activity->activity_time->format('d M Y, H:i'),
                    'location' => $activity->location,
                    'ip_address' => $activity->ip_address,
                    'browser' => $activity->browser,
                    'device' => $activity->device,
                ];
            });
        
        $hasMore = ($offset + $limit) < $totalCount;
        
        return response()->json([
            'success' => true,
            'activities' => $activities,
            'has_more' => $hasMore,
            'total_count' => $totalCount
        ]);
    }

    /**
     * Resolve the account an activity action targets and make sure the caller may act on it:
     * platform admins on anyone, everyone else only on users of their own organizations and
     * never on a platform admin (a reset password is returned to the caller in plain text).
     */
    private function authorizeTargetUser($userId): User
    {
        $target = User::findOrFail($userId);
        $actor = auth()->user();

        if (!$actor->hasRole('admin')) {
            // managesOrganization: a founder "owns" their program's org but must never reset its staff's passwords.
            $inScope = $actor->managesOrganization($target->organization_id)
                || $actor->managesOrganization($target->company?->cycledata?->organization_id);
            abort_if($target->hasRole('admin') || !$inScope, 403);
        }

        return $target;
    }
}
