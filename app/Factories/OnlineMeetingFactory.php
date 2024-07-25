<?php
namespace App\Factories;

use App\Services\OnlineMeetingServiceInterface;
use App\Services\ZoomService;
use App\Services\TeamsService;

class OnlineMeetingFactory
{
    public static function create(): OnlineMeetingServiceInterface
    {
        $service = env('ONLINE_MEETING_PROVIDER');

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
