<?php

namespace App\Broadcasting;

use App\Services\WhatsAppService;
use App\User;
use Notification;

class WhatsAppChannel
{

    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable)
    {
        if (! $phone = $notifiable->phone) {
            return; // user has no phone
        }

        if (! method_exists($notifiable, 'toWhatsapp')) {
            return;
        }

        $message = $notification->toWhatsapp($notifiable);

        $this->whatsAppService->sendMessage($phone, $message);
    }
    
}
