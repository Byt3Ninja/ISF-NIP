<?php

namespace App\Services;

use App\Settings; // تأكد من استخدام المسار الصحيح للموديل

class BroadcastConfigService
{
    public static function getPusherConfig()
    {
        $settings = Settings::where('id', 1)->first();

        if (!$settings) {
            // إذا لم يتم العثور على السجل، استخدم القيم الافتراضية أو قم بإرجاع خطأ أو سجل الحدث
            return [
                'BROADCAST_DRIVER' => 'pusher', // قيمة افتراضية
                'PUSHER_APP_KEY' => '', // ضع قيمة افتراضية هنا
                'PUSHER_APP_SECRET' => '',
                'PUSHER_APP_ID' => '',
                'PUSHER_APP_CLUSTER' => '',
            ];
        }
    
        // إعادة القيم المأخوذة من السجل
        return [
            'BROADCAST_DRIVER' => $settings->BroadCast ?? 'pusher', // قم باستخدام القيمة أو قيمة افتراضية
            'PUSHER_APP_KEY' => $settings->PUSHER_APP_KEY ?? '',
            'PUSHER_APP_SECRET' => $settings->PUSHER_APP_SECRET ?? '',
            'PUSHER_APP_ID' => $settings->PUSHER_APP_ID ?? '',
            'PUSHER_APP_CLUSTER' => $settings->PUSHER_APP_CLUSTER ?? '',
        ];
    }
}
