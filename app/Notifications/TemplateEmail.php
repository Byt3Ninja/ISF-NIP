<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class TemplateEmail extends Notification implements ShouldQueue
{
    use Queueable;
    public $Message;
    public $ActionText= 'Open VI portal';
    public $ActionUrl=null;
    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url= is_null($this->ActionUrl)?url($this->Message[3]):$this->ActionUrl;
        return (new MailMessage)
        ->subject($this->Message[0]) // it will use this class name if you don't specify
        ->greeting($this->Message[1]) // example: Dear Sir, Hello Madam, etc ...
        ->level('info')// It is kind of email. Available options: info, success, error. Default: info
        ->line($this->Message[2])
        ->action($this->ActionText, $url)
        ->line('Thank you for using our system!')
        ->salutation('Kind regards,');  // example: best regards, thanks, etc ...
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }
}
