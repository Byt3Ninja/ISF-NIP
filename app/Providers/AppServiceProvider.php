<?php

namespace App\Providers;


use App\Settings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Services\BroadcastConfigService;
use App\Services\MailConfig;
use Illuminate\Support\Facades\Broadcast;
use App\Observers\WireChatMessageObserver;
use Namu\WireChat\Models\Message;
use App\Services\BreadcrumbService;
use Illuminate\Support\Facades\Mail;
use App\Mail\GmailTransport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
    $this->app->singleton(\App\Repositories\SettingRepository::class, function ($app) {
        return new \App\Repositories\SettingRepository();
    });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Broadcast::routes();

        $settings = null;

        if (Schema::hasTable('settings'))
        {
            try {
                $settings = Settings::find(1);

                if ($settings) {
                    if ($settings->email_provider === 'resend' && $settings->resend_api_key) {
                        $data = [
                            'transport' => 'resend',
                            'api_key' => $settings->resend_api_key,
                            'from_address' => $settings->Mail ?? config('mail.from.address'),
                            'from_name' => $settings->title ?? config('mail.from.name', 'Your App Name'),
                        ];
                        MailConfig::set($data);
                    } else {
                        Config::set('mail.default', $settings?->email_provider ?? 'smtp');
                        Config::set('mail.mailers.smtp.host', $settings->host ?? env('MAIL_HOST'));
                        Config::set('mail.mailers.smtp.port', $settings->Port ?? env('MAIL_PORT'));
                        Config::set('mail.mailers.smtp.username', $settings->Mail ?? env('MAIL_USERNAME'));
                        Config::set('mail.mailers.smtp.password', $settings->Mailpassword ?? env('MAIL_PASSWORD'));
                        Config::set('mail.mailers.smtp.encryption', $settings->encryption ?? env('MAIL_ENCRYPTION'));
                        Config::set('mail.from.address',
                            ( !empty( $settings->mail_from_address ) && filter_var($settings->Mail, FILTER_VALIDATE_EMAIL ) )
                            ? $settings->mail_from_address
                            : (
                                !empty($settings->Mail) && filter_var($settings->Mail, FILTER_VALIDATE_EMAIL)
                                ? $settings->Mail
                                : env('MAIL_FROM_ADDRESS')
                            )
                        );
                        Config::set('mail.from.name', 
                            $settings->mail_from_name ?? ( $settings->title ?? env('MAIL_FROM_NAME', config('app.name')) )
                        );
                    }
                }
            } catch (\Exception $e) {
                $settings = null;
                // يمكنك تسجيل الخطأ هنا إذا فشل الاتصال بقاعدة البيانات
                // Log::error('Could not load mail settings from database: ' . $e->getMessage());
            }
        }

        Mail::extend('gmail', function () {
            return new GmailTransport(
                config('mail.mailers.smtp.username') ?? env('MAIL_USERNAME'),
                config('mail.mailers.smtp.password') ?? env('MAIL_PASSWORD')
            );
        });
        view()->composer('*', function ($view) use (&$settings) {
            if (!$settings && Schema::hasTable('settings')) {
                $settings = Settings::find(1);
            }
            $profile_completed = true;
            $company_completed = true;
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
            $organization_ids = [];
            if (auth()->check()) {
                $user = auth()->user();
                if ($user->hasRole('Admin')) {
                    /***/
                } elseif ($user->hasAnyRole(['organizationManager', 'subadmin'])) {
                    $organization_ids = $user->organizations_ids ?? [];
                    $organization_id = $organization_ids[0] ?? null;
                } else {
                    $organization_id = $user->organization_id;
                    $organization_ids = $organization_id ? [$organization_id] : [];
                }
                if( $user->hasRole('registered') )
                {
                    $profile_completed = method_exists($user, 'hasCompletedProfile') ? $user->hasCompletedProfile() : (!empty($user->phone) && !empty($user->country_id) && !empty($user->address) && !empty($user->timezone));
                    $company_completed = method_exists($user, 'hasCompletedStartup') ? $user->hasCompletedStartup() : ($user->company && !empty($user->company->name_en) && !empty($user->company->address_en) && !empty($user->company->sector) && !empty($user->company->stage));

                    if( $view->getName() === 'admin.clients.profile.editProfile' ) { $profile_completed = true; $company_completed = true; }
                    if( $view->getName() === 'admin.clients.company' ) { $profile_completed = true; $company_completed = true; }
                }
            }
            $platformName = config('app.name');
            if( $settings )
            {
                $platformName = $l == 'ar' ? $settings->ar_title : $settings->title;
                $platformName = $platformName ?? config('app.name');
            }
            $view->with(['l' => $l, 'organization_id' => $organization_id, 'organization_ids' => $organization_ids, '_user' => $_user, 'permissions' => $permissions, '_settings' => $settings, 'platformName' => $platformName, 'is_profile_completed' => $profile_completed, 'is_company_completed' => $company_completed]);
        });

        view()->composer('partials.menu', function ($view) {
            $pendingRequestsCount = 0;
            if (auth()->check()) {
                $_user = auth()->user();
                if ($_user->hasRole('serviceProvider')) {
                    if ($_user->service_provider) {
                        $provider_id = $_user->service_provider->id;
                        $pendingRequestsCount = \App\ServicesRequests::whereNull('approved')
                            ->whereHas('service', function ($q) use ($provider_id) {
                                $q->where('service_provider_id', $provider_id);
                            })->count();
                    } else {
                        $orgIds = $_user->organizations_ids;
                        $pendingRequestsCount = \App\ServicesRequests::whereNull('approved')
                            ->whereHas('service', function ($q) use ($orgIds) {
                                $q->whereExists(function ($sub) use ($orgIds) {
                                    $sub->select(\DB::raw(1))
                                        ->from('organization_service')
                                        ->whereColumn('organization_service.service_id', 'services.id')
                                        ->whereIn('organization_service.organization_id', $orgIds);
                                });
                            })->count();
                    }
                }
            }
            $view->with('pendingRequestsCount', $pendingRequestsCount);
        });

        // Breadcrumb view composer
        view()->composer('components.breadcrumbs', function ($view) {
            if (auth()->check() && !request()->is('api/*') && !request()->expectsJson()) {
                $breadcrumbService = app(BreadcrumbService::class);
                $view->with([
                    'breadcrumbs' => $breadcrumbService->getTrail(),
                    'backUrl' => $breadcrumbService->getPreviousUrl(),
                ]);
            } else {
                $view->with([
                    'breadcrumbs' => [],
                    'backUrl' => null,
                ]);
            }
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

            // 'mail.mailers.smtp.host' => $pusherConfig['host'],
            // 'mail.mailers.smtp.username' => $pusherConfig['Mail'],
            // 'mail.mailers.smtp.password' => $pusherConfig['Mailpassword'],
            // 'mail.mailers.smtp.port' => $pusherConfig['Port'],
            // 'mail.mailers.smtp.encryption' => $pusherConfig['encryption'],
            // 'mail.from.address' => $pusherConfig['Mail'],


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

        // Register the WireChat Message observer
        Message::observe(WireChatMessageObserver::class);
    
    
    }
}
