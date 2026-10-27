<?php
namespace App\Factories;

use App\Settings;
use App\Services\ZoomService;
use App\Services\TeamsService;
use Illuminate\Support\Facades\Auth;
use App\Services\OnlineMeetingServiceInterface;

class OnlineMeetingFactory
{
    public static function create(): OnlineMeetingServiceInterface
    {
       $platform = null;

        $user = Auth::user();
        if ($user && $user->organization) {
            $platform = $user->organization->meeting_platform ?? 'zoom';
        }else{
            $setting = Settings::find(1);
            $platform = $setting?->Meeting ?? 'zoom';
        }

        // if (!$platform) {
        //     $setting = Settings::find(1);
        //     $platform = $setting?->Meeting ?? 'zoom';
        // }

        $platform = strtolower($platform);

        // Resolved through the container (same objects as `new`) so tests can
        // bind a fake and never reach the real Zoom / Teams APIs.
        switch ($platform) {
            case 'zoom':
                return app(ZoomService::class);
            case 'teams':
                return app(TeamsService::class);
            default:
                throw new \Exception("Unsupported online meeting service: $platform");
        }
    }
}
