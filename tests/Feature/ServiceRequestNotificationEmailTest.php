<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\User;
use App\Service;
use App\ServicesRequests;
use App\Notifications\TemplateEmail;
use Illuminate\Support\Facades\Notification;
use ReflectionClass;

class ServiceRequestNotificationEmailTest extends TestCase
{
    public function test_template_email_contains_correct_locale_mail_and_database_representation()
    {
        $founder = new User();
        $founder->language = 'ar';

        $subject  = 'تمت الموافقة على طلب الخدمة الخاص بك';
        $greeting = 'مرحباً أحمد،';
        $body     = 'تمت الموافقة على طلبك لخدمة "تصميم الموقع" بواسطة شركة الحلول.';
        $url      = '/dashboard/requests/mine';
        $inApp    = 'تمت الموافقة على "تصميم الموقع".';

        $notification = new TemplateEmail([
            $subject,
            $greeting,
            $body,
            $url,
            $inApp,
        ], 'ar');

        $mailData = $notification->toMail($founder);
        $dbData   = $notification->toDatabase($founder);

        $this->assertEquals($subject, $mailData->subject);
        $this->assertEquals($greeting, $mailData->greeting);
        $this->assertEquals($inApp, $dbData['message']);
        $this->assertEquals('ar', $notification->preferredLocale);
    }

    public function test_send_action_notification_approved_dispatches_email_in_founder_preferred_language()
    {
        Notification::fake();

        $founder = new User();
        $founder->id = 201;
        $founder->exists = true;
        $founder->language = 'ar';
        $founder->first_name_ar = 'أحمد';
        $founder->last_name_ar = 'على';
        $founder->first_name_en = 'Ahmed';
        $founder->last_name_en = 'Ali';
        $founder->email = 'founder_ar_action@example.com';
        $founder->accept_email = 1;

        $provider = (object) ['company_name' => 'Tech Solutions'];

        $service = new Service();
        $service->id = 10;
        $service->exists = true;
        $service->name_en = 'Web Development';
        $service->name_ar = 'تطوير المواقع';
        $service->setRelation('provider', $provider);

        $serviceRequest = new ServicesRequests();
        $serviceRequest->id = 55;
        $serviceRequest->user_id = $founder->id;
        $serviceRequest->service_id = $service->id;
        $serviceRequest->exists = true;
        $serviceRequest->admin_comment = 'Looking forward to working together.';
        $serviceRequest->setRelation('user', $founder);
        $serviceRequest->setRelation('service', $service);

        $controller = new \App\Http\Controllers\ServiceProviderDashboardController();
        $reflection = new ReflectionClass($controller);
        $method     = $reflection->getMethod('sendActionNotification');
        $method->setAccessible(true);

        $method->invoke($controller, $serviceRequest, 'approved');

        Notification::assertSentTo(
            $founder,
            TemplateEmail::class,
            function ($notification) {
                return $notification->preferredLocale === 'ar'
                    && str_contains($notification->Message[0], 'تمت الموافقة')
                    && str_contains($notification->Message[1], 'أحمد')
                    && str_contains($notification->Message[2], 'تطوير المواقع');
            }
        );
    }

    public function test_send_action_notification_rejected_dispatches_email_in_english()
    {
        Notification::fake();

        $founder = new User();
        $founder->id = 202;
        $founder->exists = true;
        $founder->language = 'en';
        $founder->first_name_en = 'John';
        $founder->last_name_en = 'Doe';
        $founder->email = 'founder_en_action@example.com';
        $founder->accept_email = 1;

        $provider = (object) ['company_name' => 'Dev Agency'];

        $service = new Service();
        $service->id = 11;
        $service->exists = true;
        $service->name_en = 'Mobile App';
        $service->name_ar = 'تطبيق جوال';
        $service->setRelation('provider', $provider);

        $serviceRequest = new ServicesRequests();
        $serviceRequest->id = 56;
        $serviceRequest->user_id = $founder->id;
        $serviceRequest->service_id = $service->id;
        $serviceRequest->exists = true;
        $serviceRequest->admin_comment = 'Capacity full.';
        $serviceRequest->setRelation('user', $founder);
        $serviceRequest->setRelation('service', $service);

        $controller = new \App\Http\Controllers\ServiceProviderDashboardController();
        $reflection = new ReflectionClass($controller);
        $method     = $reflection->getMethod('sendActionNotification');
        $method->setAccessible(true);

        $method->invoke($controller, $serviceRequest, 'rejected');

        Notification::assertSentTo(
            $founder,
            TemplateEmail::class,
            function ($notification) {
                return $notification->preferredLocale === 'en'
                    && str_contains($notification->Message[0], 'not approved')
                    && str_contains($notification->Message[1], 'John')
                    && str_contains($notification->Message[2], 'Mobile App');
            }
        );
    }

    public function test_send_service_request_notification_in_service_controller()
    {
        Notification::fake();

        $founder = new User();
        $founder->id = 203;
        $founder->exists = true;
        $founder->language = 'ar';
        $founder->first_name_ar = 'سارة';
        $founder->last_name_ar = 'محمد';
        $founder->first_name_en = 'Sarah';
        $founder->last_name_en = 'Mohamed';
        $founder->email = 'sarah_sc@example.com';
        $founder->accept_email = 1;

        $provider = (object) ['company_name' => 'Alpha Cloud'];

        $service = new Service();
        $service->id = 12;
        $service->exists = true;
        $service->name_en = 'Cloud Hosting';
        $service->name_ar = 'استضافة سحابية';
        $service->setRelation('provider', $provider);

        $serviceRequest = new ServicesRequests();
        $serviceRequest->id = 57;
        $serviceRequest->user_id = $founder->id;
        $serviceRequest->service_id = $service->id;
        $serviceRequest->exists = true;
        $serviceRequest->admin_comment = 'تم القبول';
        $serviceRequest->setRelation('user', $founder);
        $serviceRequest->setRelation('service', $service);

        $controller = new \App\Http\Controllers\ServiceController();
        $reflection = new ReflectionClass($controller);
        $method     = $reflection->getMethod('sendServiceRequestNotification');
        $method->setAccessible(true);

        $method->invoke($controller, $serviceRequest, 'approved', 'تم القبول');

        Notification::assertSentTo(
            $founder,
            TemplateEmail::class,
            function ($notification) {
                return $notification->preferredLocale === 'ar'
                    && str_contains($notification->Message[0], 'تمت الموافقة')
                    && str_contains($notification->Message[1], 'سارة');
            }
        );
    }
}
