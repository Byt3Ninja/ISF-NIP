<?php

namespace App\Providers;


use App\Settings;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;
use App\Services\BroadcastConfigService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        view()->composer('*', function ($view) {
            if (session()->has('locale')) {
                \App::setLocale(session()->get('locale'));
            } elseif (auth()->check()) {
                $userLanguage = auth()->user()->language;
                if ($userLanguage) {
                    App::setLocale($userLanguage);
                }
            }
            $permissions = [];
            $_user = auth()->user();
            if( auth()->check() )
            {
                $permissions = $_user->myPermissions();
            }
            $l = App::getLocale();
            $organization_id = null;
            if (auth()->check()) {
                if (auth()->user()->hasRole('Admin')) {
                    /***/
                } else {
                    $organization_id = auth()->user()->organization_id;
                }
            }
            $view->with(['l' => $l, 'organization_id' => $organization_id, '_user' => $_user, 'permissions' => $permissions]);
        });


        $pusherConfig = BroadcastConfigService::getPusherConfig();

        config([
            'broadcasting.default' => $pusherConfig['BROADCAST_DRIVER'],
            'broadcasting.connections.pusher.key' => $pusherConfig['PUSHER_APP_KEY'],
            'broadcasting.connections.pusher.secret' => $pusherConfig['PUSHER_APP_SECRET'],
            'broadcasting.connections.pusher.app_id' => $pusherConfig['PUSHER_APP_ID'],
            'broadcasting.connections.pusher.options.cluster' => $pusherConfig['PUSHER_APP_CLUSTER'],

            'services.google.client_id' => $pusherConfig['GOOGLE_CLIENT_ID'],
            'services.google.client_secret' => $pusherConfig['GOOGLE_CLIENT_SECRET'],
            'services.google.redirect' => $pusherConfig['GOOGLE_REDIRECT'],

            'services.google_calendar.client_id' => $pusherConfig['GOOGLE_CALENDAR_CLIENT_ID'],
            'services.google_calendar.client_secret' => $pusherConfig['GOOGLE_CALENDAR_CLIENT_SECRET'],
            'services.google_calendar.redirect' => $pusherConfig['GOOGLE_CALENDAR_REDIRECT'],

            'services.linkedin.client_id' => $pusherConfig['LINKEDIN_CLIENT_ID'],
            'services.linkedin.client_secret' => $pusherConfig['LINKEDIN_CLIENT_SECRET'],
            'services.linkedin.redirect' => $pusherConfig['LINKEDIN_REDIRECT_URI'],

            'mail.mailers.smtp.host' => $pusherConfig['host'],
            'mail.mailers.smtp.username' => $pusherConfig['Mail'],
            'mail.mailers.smtp.password' => $pusherConfig['Mailpassword'],
            'mail.mailers.smtp.port' => $pusherConfig['Port'],
            'mail.mailers.smtp.encryption' => 'tls',
            'mail.from.address' => $pusherConfig['Mail'],
            'mail.from.name' => 'Incubation',


            'app.locale' => $pusherConfig['locale'],

            // 'broadcasting.connections.pusher.MICROSOFT_CLIENT_ID' => $pusherConfig['MICROSOFT_CLIENT_ID'],
            // 'broadcasting.connections.pusher.MICROSOFT_CLIENT_SECRET_VALUE' => $pusherConfig['MICROSOFT_CLIENT_SECRET_VALUE'],
            // 'broadcasting.connections.pusher.options.MICROSOFT_TENANT_ID' => $pusherConfig['MICROSOFT_TENANT_ID'],
            // 'broadcasting.connections.pusher.options.MICROSOFT_CLIENT_SECRET_ID' => $pusherConfig['MICROSOFT_CLIENT_SECRET_ID'],
            // 'broadcasting.connections.pusher.options.MICROSOFT_APP_EMAIL' => $pusherConfig['MICROSOFT_APP_EMAIL'],
            // 'broadcasting.connections.pusher.options.MICROSOFT_REDIRECT_URI' => $pusherConfig['MICROSOFT_REDIRECT_URI'],
        ]);


        if( request()->is('api/*') )
        {
            $lang = 'en';
            if( request()->headers->has('lang') )
			{
                $lang = ( in_array( strtolower( request()->header('lang') ), ['ar', 'en'] ) ) ? strtolower( request()->header('lang') ) : 'en';
				app()->setLocale( $lang );
			}
        }
    }
}
