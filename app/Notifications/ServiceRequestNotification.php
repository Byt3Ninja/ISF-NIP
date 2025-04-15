<?php

namespace App\Notifications;

use App\User;
use App\ServicesRequests;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ServiceRequestNotification extends Notification
{
    use Queueable;

    protected $request;

    public function __construct(ServicesRequests $request)
    {
        $this->request = $request;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $service = $this->request->service;
        $requester = $this->request->user;

        $adminEmail = User::role('Admin')->orderBy('id')->value('email'); 
        $orgManagerEmail = User::role('organizationManager')->orderBy('id')->value('email'); 
        $ccEmails = array_filter([$requester->email, $adminEmail, $orgManagerEmail]);

        return (new MailMessage)
            ->subject("[Service Request] New Request for {$service->name_en} from {$requester->first_name_en}")
            ->markdown('emails.service_request', [
                'service' => $service,
                'requester' => $requester,
                'request' => $this->request,
            ])
            ->cc($ccEmails); 
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
