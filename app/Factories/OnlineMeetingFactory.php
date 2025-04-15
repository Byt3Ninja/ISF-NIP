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
        if($Setting->Meeting == null){
            $service = 'Zoom';
        }else{
            $service = $Setting->Meeting;
        }

        $service = strtolower( $service );

        switch ($service) {
            case 'zoom':
                return new ZoomService();
            case 'teams':
                return new TeamsService();
            default:
              throw new \Exception("Unsupported online meeting service: $service");
        }
    }
}
