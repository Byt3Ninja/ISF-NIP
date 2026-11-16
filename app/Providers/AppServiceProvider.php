<?php

namespace App\Providers;

use App\Settings;
use App\Enums\MentorshipTextLimit;
use App\SurveyResponses;
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
use App\Services\TenantMailer;
use App\Services\EmailBranding;
use Illuminate\Mail\Mailable;
use App\Services\PlatformBrandingService;
use Resend;
use Resend\Laravel\Transport\ResendTransportFactory;
use Symfony\Component\Mailer\Transport\Smtp\SmtpTransport;
use Symfony\Component\Mailer\Transport\Smtp\Stream\SocketStream;
use App\Repositories\SettingRepository;
use App\AttendanceTrainingOffline;
use App\ServicesRequests;
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingRepository::class, function ($app) {
            return new SettingRepository();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // doctrine/dbal is installed, and Laravel then sends every ->change() and
        // renameColumn through it instead of its own SQL. DBAL cannot change an
        // enum column, which stopped `artisan migrate` on an empty database at
        // 2024_12_01_212635_add_group_to_questions_bank. Keep these native, as
        // they were when those migrations were written and first run.
        Schema::useNativeSchemaOperationsIfPossible();

        Broadcast::routes();

        $settings = null;

        if (Schema::hasTable('settings'))
        {
            try {
                $settings = Settings::find(1);

                // Same platform config TenantMailer falls back to, so a send
                // outside TenantMailer (plain Mail::) uses the same transport.
                if ($settings && $config = TenantMailer::globalConfig($settings)) {
                    MailConfig::set($config);
                }
            } catch (\Exception $e) {
                $settings = null;
                // يمكنك تسجيل الخطأ هنا إذا فشل الاتصال بقاعدة البيانات
                // Log::error('Could not load mail settings from database: ' . $e->getMessage());
            }
        }

        // resend-laravel builds its transport from a container singleton, so a
        // key changed at runtime (MailConfig::set) would never be picked up.
        // Build the client from the current config for every fresh mailer.
        Mail::extend('resend', function (array $config = []) {
            return new ResendTransportFactory(
                Resend::client((string) config('resend.api_key')),
                $config['options'] ?? []
            );
        });

        // Internal relay (SMTP preset "relay"): HELO, never STARTTLS or AUTH.
        // Laravel's smtp transport is EsmtpTransport, which upgrades to TLS on
        // its own whenever the server offers it.
        Mail::extend('relay', function (array $config = []) {
            $stream = (new SocketStream())
                ->setHost((string) $config['host'])
                ->setPort((int) $config['port'])
                ->disableTls();

            if (!empty($config['timeout'])) {
                $stream->setTimeout((float) $config['timeout']);
            }

            return (new SmtpTransport($stream))
                ->setLocalDomain($config['local_domain'] ?? config('mail.mailers.smtp.local_domain', 'localhost'));
        });
        view()->composer('*', function ($view) use (&$settings) {
            $_user = auth()->user();
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

                    if( $user->company )
                    {
                        $program = $user->company->cycledata;
                        if( $program )
                        {
                            if( $program->allow_non_company == 1 )
                            {
                                // $profile_completed = true;
                                $company_completed = true;
                            }
                        }
                    }
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

        // Platform Branding (GV-9673): the theme overrides on every layout, the settings form, and the
        // landing page editors (their button colors start from / reset to the platform's).
        view()->composer(['admin.includes.branding-styles', 'partials.forms.platform-branding-form', 'admin.landing-pages.create', 'admin.landing-pages.edit'], function ($view) use (&$settings) {
            $view->with([
                '_branding' => PlatformBrandingService::palette($settings),
                '_brandingSaved' => PlatformBrandingService::saved($settings),
            ]);
        });

        // New programs (and the wizard's "Reset to Default") start from the platform colors.
        view()->composer(['admin.programs.create', 'admin.programs.edit'], function ($view) use (&$settings) {
            $view->with('_programBrandDefaults', PlatformBrandingService::programDefaults($settings));
        });

        // Email branding (logo, name, footer links) follows the organization
        // the email is about, decided in one place: EmailBranding. Mailables
        // are branded here as they render; notifications by
        // ConfigureTenantMailTransport. 'mail::header' is the header of
        // Laravel's default notification template.
        Mailable::buildViewDataUsing(function (Mailable $mailable) {
            EmailBranding::begin($mailable);

            return [];
        });
        view()->composer(['emails.*', 'admin.emails.updateEmail', 'mail::header'], function ($view) {
            EmailBranding::compose($view);
        });

        // Mentorship note fields share one limit with their server-side validation.
        view()->composer([
            'partials.book-session-modal',
            'admin.clients.startup.partials._session_modal',
            'admin.clients.startup.partials._mutiple_session_modal',
            'admin.settings.mentorship.sessions',
        ], function ($view) {
            $view->with('mentorshipNotesMaxLength', MentorshipTextLimit::NOTES->value);
        });

        view()->composer('partials.menu', function ($view) {
            $pendingRequestsCount = 0;
            if (auth()->check()) {
                $_user = auth()->user();
                if ($_user->hasRole('serviceProvider')) {
                    if ($_user->service_provider) {
                        $provider_id = $_user->service_provider->id;
                        $pendingRequestsCount = ServicesRequests::whereNull('approved')
                            ->whereHas('service', function ($q) use ($provider_id) {
                                $q->where('service_provider_id', $provider_id);
                            })->count();
                    } else {
                        $orgIds = $_user->organizations_ids;
                        $pendingRequestsCount = ServicesRequests::whereNull('approved')
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

        view()->composer('partials.survey_reminder_modal', function ($view) {
            $_user = auth()->user();
            $completed_survey_ids = \App\SurveyResponses::where('user_id', $_user->id)
                ->whereNotNull('responses')
                ->when($_user->hasRole('Registered'), function ($query) use ($_user) {
                    $query->where('cycle_id', $_user->company?->cycle);
                })
                ->pluck('survey_id')
            ->toArray();
            
            $incomplete_responses = \App\SurveyResponses::where('user_id', $_user->id)
                ->whereNull('responses')
                ->when($_user->hasRole('Registered'), function ($query) use ($_user) {
                    $query->where('cycle_id', $_user->company?->cycle);
                })
                ->whereHas('survey', function($query) {
                $query->where('is_required', 1);
                })
                ->with(['survey', 'survey_scope', 'mentorship_session', 'mentorship_step', 'training_session', 'training_step', 'training_quiz', 'mentorship_session.user', 'mentorship_session.mentor']) 
            ->get();

            $allTrainingsAttendance = AttendanceTrainingOffline::where('user_id', $_user->id)->get();
            $incomplete_responses = $incomplete_responses->filter(function ( $response ) use ( $allTrainingsAttendance, $_user ) {
                if( $response->triggered_on === 'training' && ( auth()->check() && $_user->hasAnyRole('registered') ) )
                {
                    return $allTrainingsAttendance->where('training_session_id', $response->triggered_at)->isEmpty();
                } else { return true; }
            })->values();
            /* foreach ($incomplete_responses as $key => $response)
            {
                if ($response->triggered_on === 'training') {
                    if (auth()->check() && $_user->hasAnyRole('registered'))
                    {
                        $existsInTraining = \DB::table('attendance_training_offline')
                            ->where('training_session_id', $response->triggered_at)
                            ->where('user_id', $_user->id)
                            ->exists();

                        // لو مش موجود في الحضور، نصفي الرد من الـ collection
                        if (!$existsInTraining) {
                            $incomplete_responses->forget($key);
                        }
                    }
                }
            } */

            $survey_data = [];
            if( auth()->check() && $_user->hasAnyRole(['registered','mentor']) )
            {
                foreach ($incomplete_responses as $response)
                {
                    $type = $response->triggered_on;
                    $metadata = [];
                    if( $type == 'training' )
                    {
                        $metadata = [
                            'session' => $response->training_session,
                            'step' => $response->training_step,
                            'quiz' => $response->training_quiz
                        ];
                    }
                    if( $type == 'mentorship' )
                    {
                        if( !$response->mentorship_session ) { continue; }
                        $metadata = [
                            'session' => $response->mentorship_session,
                            'step' => $response->mentorship_step,
                            'mentor' => $response->mentorship_session?->mentor,
                            'startup' => $response->mentorship_session?->user,
                        ];
                    }
                    if( $type == 'quiz' )
                    {
                        $metadata = [
                            'session' => $response->training_session,
                            'step' => $response->training_step,
                            'quiz' => $response->training_quiz
                        ];
                    }
                    
                    $survey_data[] = [
                        'id' => $response->id,
                        'survey_id' => $response->survey->id,
                        'title' => $response->survey->title,
                        'description' => $response->survey->description,
                        'is_required' => $response->survey->is_required,
                        'type' => $type,
                        'trigger_id' => $response->triggered_at,
                        'trigger_by' => $response->triggered_by,
                        'metadata' => $metadata
                    ];
                }
            }
            $view->with(['survey_data' => $survey_data]);
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
