<?php

use App\User;
use App\Settings;
use App\Expertise;
use Carbon\Carbon;
use App\CyclesMentors;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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

if( !function_exists('URLSlug') )
{
	function URLSlug( $url )
	{
		return str_replace(' ', '-', $url);
	}
}

if( !function_exists('uploader') )
{
	function uploader( $disk, $file, $target, $get_path = false )
	{
        $original_name = $file->getClientOriginalName();
        $file_extension = $file->getClientOriginalExtension();
		$location = trim( $target ) ? $target . '/' .  $original_name : $original_name;
        $check_file = Storage::disk($disk)->missing( $location );
		if( !$check_file )
		{
			$counter = 1;
			do {
				$file_name = str_replace( '.' . $file_extension, '', $original_name ) . '-' . $counter++ . '.' . $file_extension;
				$file_location = trim( $target ) ? $target . '/' .  $file_name : $file_name;
				$check_file = Storage::disk($disk)->missing( $file_location );
			} while ( !$check_file );
		} else { $file_name = $original_name; }
		$raw_name = str_replace( '.' . $file_extension, '', $file_name );
		$file_name = URLSlug( $file_name );
		$file_name = strtolower( $file_name );
		$path = Storage::disk($disk)->putFileAs($target, $file, $file_name);
		if( $get_path === true )
		{
			$url = str_replace( '/index.php', '', URL::to("provider-registration/files/$path") );
			$url = strtolower( $url );
			return ['url' => $url, 'raw' => $raw_name, 'file' => $file_name ];
		}
		return $file_name;
	}
}

/**
 * Get section background CSS value for landing pages.
 *
 * @param object $landingPage The landing page model
 * @param string $section The section name (e.g., 'hero', 'how_it_works')
 * @param string $defaultColor1 Default color if no custom color is set
 * @param string $defaultColor2 Unused, kept for backward compatibility
 * @return string CSS background value (color or gradient)
 */
if (!function_exists('getSectionBackground')) {
    function getSectionBackground($landingPage, $section, $defaultColor1, $defaultColor2 = null)
    {
        $bgType = $landingPage->{$section . '_bg_type'} ?? 'solid';
        $bgColor = $landingPage->{$section . '_bg_color'};
        $gradientStart = $landingPage->{$section . '_bg_gradient_start'};
        $gradientEnd = $landingPage->{$section . '_bg_gradient_end'};

        if ($bgType === 'gradient' && $gradientStart && $gradientEnd) {
            return "linear-gradient(135deg, {$gradientStart} 0%, {$gradientEnd} 100%)";
        } elseif ($bgColor) {
            return $bgColor;
        } else {
            return $defaultColor1;
        }
    }
}

/**
 * Get appropriate icon class for an industry based on its name.
 *
 * @param string $industryName The industry name to match
 * @return string Tabler icon class
 */
if (!function_exists('getIndustryIcon')) {
    function getIndustryIcon($industryName)
    {
        $industryIcons = [
            'ai' => 'ti-brain',
            'ml' => 'ti-robot',
            'artificial intelligence' => 'ti-brain',
            'machine learning' => 'ti-robot',
            'website' => 'ti-world',
            'fintech' => 'ti-coin',
            'financial' => 'ti-coin',
            'finance' => 'ti-coin',
            'banking' => 'ti-building-bank',
            'health' => 'ti-heart-rate-monitor',
            'fitness' => 'ti-heart-rate-monitor',
            'healthtech' => 'ti-heart-rate-monitor',
            'healthcare' => 'ti-heart-rate-monitor',
            'medical' => 'ti-heart-rate-monitor',
            'ecommerce' => 'ti-shopping-cart',
            'e-commerce' => 'ti-shopping-cart',
            'retail' => 'ti-shopping-cart',
            'clean' => 'ti-leaf',
            'cleantech' => 'ti-leaf',
            'green' => 'ti-leaf',
            'sustainability' => 'ti-leaf',
            'environment' => 'ti-leaf',
            'edtech' => 'ti-school',
            'education' => 'ti-school',
            'learning' => 'ti-school',
            'beauty' => 'ti-palette',
            'music' => 'ti-music',
            'pets' => 'ti-paw',
            'holiday' => 'ti-beach',
            'clothing' => 'ti-shirt',
            'fashion' => 'ti-shirt',
            'children' => 'ti-device-gamepad-2',
            'games' => 'ti-device-gamepad-2',
            'toys' => 'ti-device-gamepad-2',
            'biotech' => 'ti-dna',
            'biotechnology' => 'ti-dna',
            'foodtech' => 'ti-tools-kitchen-2',
            'food' => 'ti-tools-kitchen-2',
            'beverage' => 'ti-tools-kitchen-2',
            'technology' => 'ti-laptop',
            'sports' => 'ti-basketball',
            'agtech' => 'ti-plant',
            'agriculture' => 'ti-plant',
            'proptech' => 'ti-home',
            'housewares home design' => 'ti-home',
            'real estate' => 'ti-home',
            'property' => 'ti-home',
            'logistics' => 'ti-truck',
            'transport' => 'ti-truck',
            'mobility' => 'ti-car',
            'saas' => 'ti-cloud',
            'cloud' => 'ti-cloud',
            'software' => 'ti-code',
            'cyber' => 'ti-shield-lock',
            'security' => 'ti-shield-lock',
            'iot' => 'ti-cpu',
            'hardware' => 'ti-cpu',
            'blockchain' => 'ti-link',
            'crypto' => 'ti-currency-bitcoin',
            'gaming' => 'ti-device-gamepad-2',
            'media' => 'ti-movie',
            'entertainment' => 'ti-movie',
            'travel' => 'ti-plane',
            'tourism' => 'ti-plane',
            'hr' => 'ti-users',
            'human resources' => 'ti-users',
            'recruitment' => 'ti-users',
            'legal' => 'ti-gavel',
            'legaltech' => 'ti-gavel',
            'insurance' => 'ti-umbrella',
            'insurtech' => 'ti-umbrella',
        ];

        $name = strtolower($industryName);
        foreach ($industryIcons as $keyword => $icon) {
            if (strpos($name, $keyword) !== false) {
                return $icon;
            }
        }

        return 'ti-tag'; // Default icon
    }

    if (!function_exists('active')) {
        function active(...$patterns) {
            foreach ($patterns as $pattern) {
                if (request()->is($pattern)) {
                    return 'active';
                }
            }
            return '';
        }
    }
}
