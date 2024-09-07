<?php

namespace App\Providers;

use App;
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

        $pusherConfig = BroadcastConfigService::getPusherConfig();

        config([
            'broadcasting.connections.pusher.BROADCAST_DRIVER' => $pusherConfig['BROADCAST_DRIVER'],
            'broadcasting.connections.pusher.PUSHER_APP_KEY' => $pusherConfig['PUSHER_APP_KEY'],
            'broadcasting.connections.pusher.PUSHER_APP_SECRET' => $pusherConfig['PUSHER_APP_SECRET'],
            'broadcasting.connections.pusher.PUSHER_APP_ID' => $pusherConfig['PUSHER_APP_ID'],
            'broadcasting.connections.pusher.options.PUSHER_APP_CLUSTER' => $pusherConfig['PUSHER_APP_CLUSTER'],
        ]);

    }
}
