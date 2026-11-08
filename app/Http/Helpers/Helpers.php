<?php

use App\User;
use App\Settings;
use App\Expertise;
use Carbon\Carbon;
use App\CyclesMentors;
use App\Services\TimezoneService;
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

if (!function_exists('mentorshipMeetingStatus')) {
    // GV-9566: zoom_date is platform wall-clock. A cast MentorshipRequest value is
    // already that instant; a raw row string is read in $timezone (default: the
    // global platform zone). Compared as real instants, never in the viewer's zone.
    function mentorshipMeetingStatus($zoomDate, $period, ?string $timezone = null)
    {
        $at = $zoomDate instanceof Carbon
            ? $zoomDate
            : TimezoneService::wallClock($zoomDate, $timezone ?? TimezoneService::platform());

        if (is_null($at)) return 'before';

        $now = Carbon::now();

        if ($now->lt($at->copy()->subMinutes(15))) return 'before';
        if ($now->gt($at->copy()->addMinutes((int) $period))) return 'expired';
        return 'joinable';
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

if (! function_exists('stepManageUrl')) {
    /**
     * Resolve the "manage" URL for a startup's current step, by step type (GV-3841).
     *
     * The screening / final-screening reports show every startup that belongs to a
     * step's evaluation even after it has advanced to a step of a DIFFERENT type, so
     * the step chip can point at any of the six step types. Each type has its own
     * manage page, gated by its own permission, and — critically — each route's {id}
     * parameter means a different thing:
     *
     *   1 Custom Form    -> forms.render_view          {id} = BuilderForm id (step->form_id ?: step->data)
     *   2 Screening      -> screening.manage           {id} = Step id  (controller requires stype 2)
     *   3 Mentorship     -> mentorships.requests_list  {id} = Cycle id
     *   4 Training       -> trainings.sessions_list    {id} = Step id  (controller requires stype 4)
     *   5 Final          -> screening.manage_final     {id} = Step id  (controller requires stype 5)
     *   6 Team Formation -> (no manage page)           -> null
     *
     * Passing the wrong id (e.g. a step id where a form/cycle id is expected) is what
     * produced the 404s. Returns null when the user lacks the page's permission or the
     * type has no manage page, so the caller renders a plain, non-clickable badge.
     *
     * @param  mixed  $stepInfo    A Step model (needs stype, id, form_id, data, cycle_id).
     * @param  mixed  $user        The acting user ($_user); ableTo() is used for gating.
     * @param  array  $permissions The resolved permissions array passed to ableTo().
     * @return string|null
     */
    function stepManageUrl($stepInfo, $user, array $permissions = []) {
        if (! $stepInfo || ! $user) {
            return null;
        }

        switch ((int) $stepInfo->stype) {
            case 1: // Custom Form
                $formId = $stepInfo->form_id ?: ($stepInfo->data ?: null);
                return ($formId && $user->ableTo('forms.render_view', $permissions))
                    ? route('forms.render_view', $formId) : null;
            case 2: // Screening
                return $user->ableTo('screening.show', $permissions)
                    ? route('screening.manage', $stepInfo->id) : null;
            case 3: // Mentorship
                return ($stepInfo->cycle_id && $user->ableTo('mentorships.requests_list', $permissions))
                    ? route('mentorships.requests_list', $stepInfo->cycle_id) : null;
            case 4: // Training
                return $user->ableTo('trainings.sessions_list', $permissions)
                    ? route('trainings.sessions_list', $stepInfo->id) : null;
            case 5: // Final Screening
                return $user->ableTo('screening.show', $permissions)
                    ? route('screening.manage_final', $stepInfo->id) : null;
            default: // Team Formation (6) and anything else: no manage page
                return null;
        }
    }
}

if( !function_exists('URLSlug') )
{
	function URLSlug( $url )
	{
		return str_replace(' ', '-', $url);
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

if (! function_exists('form_field_placeholder')) {
    /**
     * GV-8348: build the guidance hint shown inside a form-builder input.
     *
     * The builder gives authors no placeholder field, and the previous behaviour
     * — echoing the label back into the box — was redundant with the label
     * rendered directly above it, so it conveyed nothing about what to type.
     *
     * Typed fields therefore get a hint describing the EXPECTED FORMAT, which is
     * the guidance the founder actually needs before hitting a validation error.
     * Free-text fields carry no format to hint at, so their label — the only
     * thing that describes them — becomes an instruction ("Enter <label>").
     *
     * This mirrors the JS placeholder logic on the public apply page (GV-3679)
     * so a question looks the same from every entry point.
     *
     * @param  array  $question  A builder question array (type/label/min/max).
     * @return string            Hint text, or '' when there is nothing to say.
     */
    function form_field_placeholder(array $question)
    {
        $type  = $question['type'] ?? 'short_answer';
        $label = trim((string) ($question['label'] ?? ''));

        switch ($type) {
            case 'email':
                return __('builder.hint_email');
            case 'url':
                return __('builder.hint_url');
            case 'date':
                return __('builder.hint_date');
            case 'time':
                return __('builder.hint_time');
            case 'number':
                return form_number_placeholder($question);
        }

        // No dedicated `phone` type exists; the client-side validator detects
        // phone questions by label, so match that rule here or the two would
        // disagree about which questions are phone questions.
        if ($label !== '' && stripos($label, 'phone') !== false) {
            return __('builder.hint_phone');
        }

        // A label-less free-text field has nothing to say, so leave the box bare
        // rather than showing a dangling verb ("Enter ").
        return $label === '' ? '' : __('builder.hint_enter') . ' ' . $label;
    }
}

if (! function_exists('form_number_placeholder')) {
    /**
     * GV-8348: describe a number field's accepted range so the bound is visible
     * BEFORE the user trips the validation message, not only after.
     */
    function form_number_placeholder(array $question)
    {
        $min = $question['min'] ?? null;
        $max = $question['max'] ?? null;

        $hasMin = $min !== null && $min !== '';
        $hasMax = $max !== null && $max !== '';

        if ($hasMax) {
            // Mirrors the renderers, which fall back to a 0 floor when unset.
            $from = $hasMin ? $min : 0;

            return __('builder.hint_number_between') . ' ' . $from
                . ' ' . __('builder.hint_and') . ' ' . $max;
        }

        // Only worth stating when the floor is a real constraint; a min of 0 or
        // less is the unremarkable default and reads as noise.
        if ($hasMin && is_numeric($min) && $min > 0) {
            return __('builder.hint_number_from') . ' ' . $min;
        }

        return __('builder.hint_number');
    }
}

if (!function_exists('form_field_options')) {
    /**
     * Normalize a builder question's stored choice list into a clean array.
     *
     * `options.values` is persisted as a comma-joined string (older rows may
     * hold a real array). GV-8346: the builder's `Add "Other"` button had no
     * guard, so a question could be saved with several `__OTHER__` entries and
     * every renderer drew one "Other" choice per entry. De-duplicating on read
     * keeps already-corrupted rows displaying correctly without a data fix,
     * and blank entries (a trailing comma) never deserved a choice either.
     *
     * @param  mixed  $values  Raw `options['values']` (string or array).
     * @return array<int, string>
     */
    function form_field_options($values)
    {
        if ($values === null || $values === '') {
            return [];
        }

        return collect(is_array($values) ? $values : explode(',', $values))
            ->map(fn($o) => trim((string) $o))
            ->filter(fn($o) => $o !== '')
            ->unique()
            ->values()
            ->all();
    }
}

if (! function_exists('brandLogo')) {
    /**
     * Resolve the platform logo URL for the auth pages (GV-8956/8957/8958).
     *
     * `Settings.Logo` holds only a FILE NAME, and nothing guarantees that file still
     * exists on disk: a tenant can be seeded with a logo that was never uploaded, or
     * the record can outlive a storage wipe. The old ternary
     *
     *     $setting ? asset('images/logo/' . $setting->Logo) : asset(... fallback ...)
     *
     * only fell back when the whole settings ROW was missing, never when the FILE was,
     * so a stale row emitted a dead URL and the browser painted the <img> alt text
     * ("avatar", "logo") where the brand should be. Checking the file itself is what
     * actually protects the page, so every caller goes through here.
     */
    function brandLogo(): string
    {
        $fallback = asset('images/background/logo.png');
        $name = optional(Settings::first())->Logo;

        if (blank($name)) {
            return $fallback;
        }

        // basename() keeps a crafted "../" value from escaping the logo directory.
        $path = public_path('images/logo/' . basename($name));

        return is_file($path) ? asset('images/logo/' . basename($name)) : $fallback;
    }
}

if (! function_exists('brandName')) {
    /**
     * The platform's display name, used for the logo's alt text on the auth pages.
     *
     * Falls back to the app name so the alt text is never empty: a screen reader
     * announcing "avatar" tells nobody which platform they are signing in to.
     */
    function brandName(): string
    {
        $setting = Settings::first();

        return optional($setting)->Platform_Name
            ?: (optional($setting)->title ?: config('app.name'));
    }
}

if (! function_exists('isIsf')) {
    /**
     * GV-10432: true on the ISF deployment, recognised by its LOCALIZATION_PATH=isf
     * wording overlay. Use it for ISF-only behaviour, not only wording.
     */
    function isIsf(): bool
    {
        return config('app.localization_path') === 'isf';
    }
}
