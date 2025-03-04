<?php

namespace App\Notifications;

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

        return (new MailMessage)
            ->subject("[Service Request] New Request for {$service->name_en} from {$requester->first_name_en}")
            ->greeting("Dear {$service->vendor_name},")
            ->line("A new service request has been submitted for your service: **{$service->name_en}**.")
            ->line("**Request Details:**")
            ->line("**Requested by:** {$requester->first_name_en} {$requester->last_name_en}")
            ->line("**Email:** {$requester->email}")
            ->line("**Requested Date:** {$this->request->date_time}")
            ->line("**Service Description:** {$service->description_en}")
            ->line("**Additional Notes:** {$this->request->comment}")
            ->line("Please review the request and respond accordingly.")
            ->cc([$requester->email])
            ->salutation("Best Regards, GrowthLabs Team");
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
