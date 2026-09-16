<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\PermissionRegistrar;

class Permissions
{
    public $exceptNames = [
        'auth.magic-login',
        'core.dashboard_view',
        'clients.profile_view',
        'cycle.custom_pages.image',
        'cycle.custom_pages.upload_image',
        'screening.guidelines.upload_image',
        'screening.guidelines.image',
        'service_provider.reviews',
        'service_provider.profile',
        'organizations.logo',
        'organization.landing.asset',
        'organization.landing.public',
        'landing-pages.public',
        'landing-pages.about.update',
        'landing-pages.how.update',
        'landing-pages.contact.update',
        'landing-pages.general.update',
        'landing-pages.hero.update',
        'landing-pages.store',
        'landing-pages.programs.update',
        'landing-pages.custom-sections.update',
        'landing-pages.custom-sections.destroy',
        'landing-pages.custom-sections.upload-image',
        'landing-pages.url.check',
        'landing-pages.url.save',
        'landing-pages.programs.reorder',
        'programs.check_public',
        'invite_mentor.attachment',
        'landing-pages.programs.reorder',
        'organization.wizard.store',
        'organization.wizard.update.step1',
        'organization.wizard.update.step2',
        'organization.wizard.update.step3',
        'organization.wizard.update.step4',
        'organization.wizard.update.step5',
        'organization.wizard.landing',
        'organization.wizard.step5.reorder',
        'organization.wizard.step5.delete',
        'organization.wizard.preview',
        'organization.wizard.check.slug',
        'organization.wizard.generate.slug',
        'organization.wizard.managers.list',
        'organization.wizard.managers.create',
        'organization.wizard.programs.landing.update',
        'organization.wizard.programs.reorder',
        'organization.wizard.draft',
        'organization.wizard.draft.update',
        'organization.wizard.publish',
        'provider-registration',
        'hackathon.trackShare',
        'hackathon.teamLogo',
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
        'screening.update_step_form',
        'services_providers.edit_profile',
        'services_providers.clients.*',
        'user.profile_email_resend',
        'user.profile_email_cancel',
    ];

    public $exceptControllers = [
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

            // Block unapproved service providers from accessing non-dashboard routes
            if ($user->hasRole('serviceProvider') && $this->match($route)) {
                $spRegistration = DB::table('service_provider_registration')
                    ->where('user_id', $user->id)
                    ->first(['status']);

                if ( /*!$spRegistration ||*/ ( $spRegistration && $spRegistration->status != 1 )) {
                    $routeName = $permission ?? '';
                    $allowedForUnapproved = Str::startsWith($routeName, 'service_provider_dashboard.')
                        || Str::startsWith($routeName, 'service_provider_request.');

                    if (!$allowedForUnapproved) {
                        if ($request->expectsJson()) {
                            return response()->json(['status' => false, 'message' => 'Not found'], 404);
                        }
                        abort(404);
                    }
                }
            }

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
