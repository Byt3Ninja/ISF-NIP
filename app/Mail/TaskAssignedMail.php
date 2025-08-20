<?php

namespace App\Mail;

use App\TodoList;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Contracts\Queue\ShouldQueue;

class TaskAssignedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

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
        return $this->subject('تم تعيين مهمة جديدة لك')
                    ->view('emails.task_assigned', ['task' => $this->task, 'assignedUser' => $this->assignedUser]);
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
