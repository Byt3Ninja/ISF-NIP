<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ProgramCreatedNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $cycle;
    public $admin; 

    /**
     * Create a new message instance.
     */
    public function __construct($cycle, $admin)
    {
        $this->cycle = $cycle;
        $this->admin = $admin;
    }
    public function build()
    {
        return $this->subject('A New Program has been Created')
                    ->view('emails.program-created');
    }

    

}
