<?php

use App\User;
use App\Settings;
use App\Expertise;
use Carbon\Carbon;
use App\CyclesMentors;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

if (! function_exists('in_array_2d')) {
    function in_array_2d($key, $array)
    {

        if (!is_array($array))
            return false;

        foreach ($array as $row) {

            if (!is_array($row))
                return false;

            if (in_array($key, $row))
                return true;
        }

        return false;
    }
}

if (! function_exists('lang')) {
    function lang($name = null)
    {

        if (session()->has('locale'))
            \App::setLocale(session()->get('locale'));

        $language = app()->getLocale();

        if (empty($language))
            $language = "en";
        if (empty($name))
            return $language;

        return $name . '_' . $language;
    }
}

if (!function_exists('getPusherConfig')) {
    function getPusherConfig()
    {
        return DB::table('settings')->select('PUSHER_APP_KEY', 'PUSHER_APP_CLUSTER')->first();
    }
}
if (!function_exists('FormatDate')) {
    function FormatDate($stamp, $format)
    {
        try {
            if ($stamp === null) {
                return '';
            }
            if (is_numeric($stamp)) {
                return date($format, $stamp);
            }
            if ($stamp instanceof \DateTimeInterface) {
                return $stamp->format($format);
            }
            return date($format, strtotime($stamp));
        } catch (\Exception $e) {
            return '';
        }
    }

    function showMintors(Request $request)
    {
        $setting = Settings::where('id', 1)->first();
        $search = $request->input('search');
        $rateSearch = $request->input('rateSearch');
        $ExpertiesSearch = $request->input('expertises');
        $linkedin = $request->input('linkedin');
       
        if ($setting->mentor_list == 1) {
           
            if (Auth::user()->hasanyRole('Mentor|Registered|Admin|OrganizationManager')) {
                $l = app()->getLocale();
                $firstNameColumn = $l == 'ar' ? 'first_name_en' : 'first_name_en';
                $lastNameColumn = $l == 'ar' ? 'first_name_en' : 'last_name_en';
                $mentors = User::role('mentor')
                    ->whereRaw("CONCAT($firstNameColumn, ' ', $lastNameColumn) LIKE ?", ["%{$search}%"]);
                if ($rateSearch > 0) {
                    $mentors = $mentors->whereHas('mentorshipRequests', function ($q) use ($rateSearch) {
                        $q->where(function ($q2) {
                            $q2->where('approved', 'Yes')
                                ->orWhereNull('approved');
                        })->havingRaw('AVG(rating) >= ?', [$rateSearch]);
                    });
                }
                if (!empty($ExpertiesSearch)) {
                    $expertiesMentor = Expertise::where('id', '=', $ExpertiesSearch)->first();
                    if ($expertiesMentor) {
                        $expid = $expertiesMentor->id;
                        $mentors = $mentors->whereHas('mentorExpertises', function ($query) use ($expid) {
                            $query->where('expertise_id', $expid); // استخدم where بدلاً من whereIn لأنه لدينا id واحد
                        });
                    }
                }
                if (!empty($linkedin)) {
                    $mentors = $mentors->whereHas('bio', function ($query) use ($linkedin) {
                        $query->whereNotNull('linkedin');
                    });
                }
                if (Auth::user()->company && !empty(Auth::user()->company->cycle)) {
                    $cycleId = Auth::user()->company->cycle;
                    $mentorIds = CyclesMentors::where('cycle_id', $cycleId)
                        ->pluck('user_id')
                        ->toArray();
                    $mentors = $mentors->whereIn('id', $mentorIds);
                }
                
                if (!empty($request->cycleIdHackathon)) {
                    $cycleId = $request->cycleIdHackathon;
                    $mentorIds = CyclesMentors::where('cycle_id', $cycleId)
                        ->pluck('user_id')
                        ->toArray();
                    $mentors = $mentors->whereIn('id', $mentorIds);
                }

                $mentorsLists = $mentors->get();
             
                // $mentors = User::role('mentor')->get();
                return $mentorsLists;
            }
        } else {

            if (Auth::user()->hasanyRole('Admin|OrganizationManager')) {
                $l = app()->getLocale();
                $firstNameColumn = $l == 'ar' ? 'first_name_en' : 'first_name_en';
                $lastNameColumn = $l == 'ar' ? 'first_name_en' : 'last_name_en';
                $mentors = User::role('mentor')
                    ->whereRaw("CONCAT($firstNameColumn, ' ', $lastNameColumn) LIKE ?", ["%{$search}%"]);
                if ($rateSearch > 0) {
                    $mentors = $mentors->whereHas('mentorshipRequests', function ($q) use ($rateSearch) {
                        $q->where(function ($q2) {
                            $q2->where('approved', 'Yes')
                                ->orWhereNull('approved');
                        })->havingRaw('AVG(rating) >= ?', [$rateSearch]);
                    });
                }
                if (!empty($ExpertiesSearch)) {
                    $expertiesMentor = Expertise::where('title', '=', $ExpertiesSearch)->first();
                    if ($expertiesMentor) {
                        $expid = $expertiesMentor->id;
                        $mentors = $mentors->whereHas('mentorExpertises', function ($query) use ($expid) {
                            $query->where('expertise_id', $expid); // استخدم where بدلاً من whereIn لأنه لدينا id واحد
                        });
                    }
                }
                if (!empty($request->cycleIdHackathon)) {
                    $cycleId = $request->cycleIdHackathon;
                    $mentorIds = CyclesMentors::where('cycle_id', $cycleId)
                        ->pluck('user_id')
                        ->toArray();
                    $mentors = $mentors->whereIn('id', $mentorIds);
                }
                $mentorsLists = $mentors->get();
                // $mentors = User::role('mentor')->get();
                return $mentorsLists;
            } else {
               
                return collect();
                // return redirect()->route('core.dashboard_view');
            }

        }
       

    }
}

if (! function_exists('url_slugify'))
{
    function url_slugify( $url )
    {
        $url = strtolower($url);
        $url = preg_replace('/[^\w\s-]/', '', $url);
        $url = preg_replace('/[\s-]+/', ' ', $url);
        $url = preg_replace('/[\s_]/', '-', $url);
        return $url;
    }
}

if( !function_exists('date_ranges') )
{
    function date_ranges( $start, $end )
    {
        $start = Carbon::parse( $start );
        $end = Carbon::parse( $end );

        if( $start->year !== $end->year ) { $formatted = $start->format('F j, Y') . ' – ' . $end->format('F j, Y'); }
        elseif( $start->month !== $end->month ) { $formatted = $start->format('F j') . ' – ' . $end->format('F j, Y'); } 
        elseif( $start->day !== $end->day ) { $formatted = $start->format('F j') . ' – ' . $end->format('j, Y'); }
        else { $formatted = $start->format('F j, Y'); }

        return $formatted;
    }



    if (! function_exists('setting')) {
    function setting($key, $default = null) {
        return app(\App\Repositories\SettingRepository::class)->get($key, $default);
    }

    
}
}

if (! function_exists('route_exists')) {
    function route_exists(string $name): bool {
        return \Illuminate\Support\Facades\Route::has($name);
    }
}