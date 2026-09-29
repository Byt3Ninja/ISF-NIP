<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Currency;
use App\Organization;
use App\User;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{

    
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => \App\Policies\UserPolicy::class,
        Organization::class => \App\Policies\OrganizationPolicy::class,
        Currency::class => \App\Policies\CurrencyPolicy::class,
        \App\Services\Importer\ImporterSession::class => \App\Policies\ImporterSessionPolicy::class,

    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
