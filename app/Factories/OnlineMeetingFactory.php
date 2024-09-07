<?php
namespace App\Factories;

use App\Services\OnlineMeetingServiceInterface;
use App\Services\ZoomService;
use App\Services\TeamsService;

class OnlineMeetingFactory
{
    public static function create(): OnlineMeetingServiceInterface
    {
        $Setting = \App\Settings::where('id', 1)->first();
        $service = $Setting->Meeting;

        switch ($service) {
            case 'Zoom':
                return new ZoomService();
            case 'Teams':
                return new TeamsService();
            default:
                throw new \Exception("Unsupported online meeting service: $service");
        }
    }
}
