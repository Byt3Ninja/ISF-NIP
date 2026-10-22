<?php

namespace App\Mail;

use App\Traits\RoutesTenantMail;
use App\TodoList;
use App\Traits\ConfiguresSmtp;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaskAssignedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels, ConfiguresSmtp, RoutesTenantMail;

    public $task;
    public $assignedUser;
    /**
     * Create a new message instance.
     */
    public function __construct($assignedUser, $task)
    {
        $this->task = $task;
        $this->assignedUser = $assignedUser;
    }

    /**
     * Get the message envelope.
     */
    // public function envelope(): Envelope
    // {
    //     return new Envelope(
    //         subject: 'Task Assigned Mail',
    //     );
    // }

     public function build()
    {
        $org = $this->assignedUser->organization ?? null;
        $orgData = $this->emailBrandingData();

        app()->setLocale($this->assignedUser->language ?? 'en');
        $subject = app()->getLocale() === 'ar'
            ? 'تم تعيين مهمة جديدة لك'
            : 'You have been assigned a new task';

        return $this->subject($subject)
                    ->view('emails.task_assigned', array_merge(['task' => $this->task, 'assignedUser' => $this->assignedUser], $orgData));
    }

    /**
     * Get the message content definition.
     */
    // public function content(): Content
    // {
    //     return new Content(
    //         view: 'view.name',
    //     );
    // }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    // public function attachments(): array
    // {
    //     return [];
    // }
}
