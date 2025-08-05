<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use App\Settings; // تأكد من استخدام المسار الصحيح للموديل

class BroadcastConfigService
{
    public static function getPusherConfig()
    {
        try{
            $settings = Settings::where('id', 1)->first();

            if (!$settings) {
                // إذا لم يتم العثور على السجل، استخدم القيم الافتراضية أو قم بإرجاع خطأ أو سجل الحدث
                return [
                    'BROADCAST_DRIVER' => 'pusher', // قيمة افتراضية
                    'PUSHER_APP_KEY' => '', // ضع قيمة افتراضية هنا
                    'PUSHER_APP_SECRET' => '',
                    'PUSHER_APP_ID' => '',
                    'PUSHER_APP_CLUSTER' => '',

                    'GOOGLE_CLIENT_ID' => '',
                    'GOOGLE_CLIENT_SECRET' => '',
                    'GOOGLE_REDIRECT' => '',

                    
                    'LINKEDIN_CLIENT_ID' => '',
                    'LINKEDIN_CLIENT_SECRET' => '',
                    'LINKEDIN_REDIRECT_URI' => '',

                    'GOOGLE_CALENDAR_CLIENT_ID' => '',
                    'GOOGLE_CALENDAR_CLIENT_SECRET' => '',
                    'GOOGLE_CALENDAR_REDIRECT' => '',

                    'mail.mailers.smtp.host' => '',
                    'mail.mailers.smtp.username' => '',
                    'mail.mailers.smtp.password' => '',
                    'mail.mailers.smtp.port' => '',
                    'mail.from.address' => '',
                   

                    'locale' => 'en',

                ];
            }
        }catch(\Exception $e){
            Log::error('BroadcastConfigService: Error retrieving settings.', ['error' => $e->getMessage()]);
        }
        // إعادة القيم المأخوذة من السجل
        return [
            'BROADCAST_DRIVER' => $settings->BroadCast ?? 'pusher', // قم باستخدام القيمة أو قيمة افتراضية
            'PUSHER_APP_KEY' => $settings->PUSHER_APP_KEY ?? '',
            'PUSHER_APP_SECRET' => $settings->PUSHER_APP_SECRET ?? '',
            'PUSHER_APP_ID' => $settings->PUSHER_APP_ID ?? '',
            'PUSHER_APP_CLUSTER' => $settings->PUSHER_APP_CLUSTER ?? '',

            'GOOGLE_CLIENT_ID' => $settings->GOOGLE_CLIENT_ID ?? '',
            'GOOGLE_CLIENT_SECRET' => $settings->GOOGLE_CLIENT_SECRET ?? '',
            'GOOGLE_REDIRECT' => $settings->GOOGLE_REDIRECT ?? '',

            'LINKEDIN_CLIENT_ID' => $settings->LINKEDIN_CLIENT_ID ?? '',
            'LINKEDIN_CLIENT_SECRET' => $settings->LINKEDIN_CLIENT_SECRET ?? '',
            'LINKEDIN_REDIRECT_URI' => $settings->LINKEDIN_REDIRECT_URI ?? '',

            'MICROSOFT_CLIENT_ID' => $settings->MICROSOFT_CLIENT_ID ?? '0',
            'MICROSOFT_CLIENT_SECRET_VALUE' => $settings->MICROSOFT_CLIENT_SECRET_VALUE ?? '',
            'MICROSOFT_TENANT_ID' => $settings->MICROSOFT_TENANT_ID ?? '',
            'MICROSOFT_CLIENT_SECRET_ID' => $settings->MICROSOFT_CLIENT_SECRET_ID ?? '',
            'MICROSOFT_APP_EMAIL' => $settings->MICROSOFT_APP_EMAIL ?? '',
            'MICROSOFT_REDIRECT_URI' => $settings->MICROSOFT_REDIRECT_URI ?? '',

            'GOOGLE_CALENDAR_CLIENT_ID' => $settings->GOOGLE_CALENDAR_CLIENT_ID ?? '',
            'GOOGLE_CALENDAR_CLIENT_SECRET' => $settings->GOOGLE_CALENDAR_CLIENT_SECRET ?? '',
            'GOOGLE_CALENDAR_REDIRECT' => $settings->GOOGLE_CALENDAR_REDIRECT ?? '',

            'host' => $settings->host ?? '',
            'Mail' => $settings->Mail,
            'Mailpassword' => $settings->Mailpassword,
            'Port' => $settings->Port,

            'locale' => $settings->Default_Language ?? 'en',

        ];
    }
}
