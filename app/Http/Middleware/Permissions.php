<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\PermissionRegistrar;

class Permissions
{
    private $exceptNames = [
        'provider-registration',
        'filemanager*',
        'dashboard',
        'dashboard/dashboard',
        'login*',
        'cities*',
		'LFMGet',
		'utils.locations_list_states_by_country',
        'subcategories.list_by_category',
		'user.profile_email_confirm',
        'verification*',
        'cycles.view',
        'public.join',
        'public.program_join',
        'public.program_apply',
        'chats*',
        'livewire.*',
        'groups*',
        'messages*',
        'users/search',
        'cycles.completion_status_check',
        'trainings.time_tracking',
    ];

    private $exceptControllers = [
        'LoginController',
		'IndexController',
        'ForgotPasswordController',
        'ResetPasswordController',
        'SocialController',
		'MicrosoftController',
        'GoogleCalendarController',
        'MicrosoftCalendarController'
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $cached = app(PermissionRegistrar::class)->getPermissions();
    
        $core_permissions = $cached->where('is_core', true)->values()->pluck('name')->toArray();
    
        if( env('PERMISSION_BYPASS', false) === true ) { return $next($request); }
        $user = auth()->user();
    
        if( auth()->check() )
        {
            $route = $request->route();
            $permission = $route->getName();

            if( !trim( $permission ) || in_array( $permission, ['web', 'admin', 'dashboard']) ) { return $next($request); }
            else
            {
                if( in_array( $permission, $core_permissions ) ) { return $next($request); }
                else
                {
                    if( $this->match( $route ) && !$user->ableTo( $permission ) )
                    {
                        if( $request->expectsJson() )
                        {
                            // Force English locale for JSON responses to ensure consistent API responses
                            $currentLocale = app()->getLocale();
                            app()->setLocale('en');

                            $response = response()->json([
                                'status' => false,
                                'title' => __('auth.unauthorized'),
                                'message' => __('auth.not_authorized_permission') . " ($permission)",
                            ], 401);

                            // Restore original locale
                            app()->setLocale($currentLocale);

                            return $response;
                        }
                        else
                        {
                            abort('401', $permission);
                        }
                    }
                }
            }
        }
        return $next($request);
    }

    private function match(Route $route)
    {
        if( $route->getName() == '' || $route->getName() === null ) { return false; }
        else
        {
            if( in_array(class_basename($route->getController()), $this->exceptControllers) ) { return false; }
            foreach ($this->exceptNames as $except) { if( Str::is($except, $route->getName()) || Str::is($except, $route->uri) ) { return false; } }
        }
        return true;
    }
}
