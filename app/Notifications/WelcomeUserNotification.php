<?php

namespace App\Notifications;

use App\Broadcasting\WhatsAppChannel;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class WelcomeUserNotification extends Notification
{
    use Queueable;
 
    public $user;
    public $password;

    public WhatsAppService $whatsAppService;



    
    /**
     * Create a new notification instance.
     */
    public function __construct($user, $password)
    {
        $this->user = $user;
        $this->password = $password;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
         $channels = [];
    //check if user have a email 
        if($notifiable->accept_email)
        {
            $channels [] = "mail";
        }

    if ($notifiable->accept_whatsapp) {
        $channels[] = WhatsAppChannel::class; 
    }
        if($notifiable->accept_pusher)
        {
            $channels [] = 'broadcast';
        }
        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
       
        return (new MailMessage)->subject(__('email.welcome_email.subject'))
            ->view('emails.welcome-email',[
                'user'     => $this->user,
                'password' => $this->password,  
            ]);

                    
    }

    /**
     * Summary of toWhatsapp
     * @param object $notifiable
     * @return array
     */
    public function toWhatsapp(object $notifiable): array
    {
    return " " . __('email.welcome_email.dear') . ' ' . $this->notifiable->first_name_en .
           ' ' . $this->notifiable->last_name_en . "\n" .
           __('email.welcome_email.m1') . "\n" .
           __('email.welcome_email.m2') . ' ' . $this->notifiable->email;
    }


    /**
     * Summary of toBroadcast
     * @param object $notifiable
     * @return BroadcastMessage
     */
    public function toBroadcast(object $notifiable) 
    {
     return new BroadcastMessage([
        'subject' => __('emails.welcome_email.subject'),
        'dear'    => __('emails.welcome_email.dear') . ' ' . $notifiable->name,
        'm1'      => __('emails.welcome_email.m1'),
        'm2'      => __('emails.welcome_email.m2') . ' ' . $notifiable->email,
        'm3'      => __('emails.welcome_email.m3'),
        'best'    => __('emails.welcome_email.best_regards'),
        'time'    => now()->toDateTimeString(),
    ]);
    }
    
}
