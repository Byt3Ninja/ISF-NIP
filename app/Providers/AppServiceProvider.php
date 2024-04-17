<?php

namespace App\Providers;

use App;
use Illuminate\Support\ServiceProvider;

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
        view()->composer('*',function($view) {
            if (session()->has('locale')){\App::setLocale(session()->get('locale'));}
            $l = App::getLocale();
            $organization_id = null;
            if( auth()->check() )
            {
                if( auth()->user()->hasRole('Admin') ) { /***/ } else { $organization_id = auth()->user()->organization_id; }
            }
            $view->with('l', $l)->with('organization_id', $organization_id);
        });
    }
}
