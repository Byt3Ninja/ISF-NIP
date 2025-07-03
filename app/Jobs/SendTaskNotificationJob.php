<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\TaskNotification;

class SendTaskNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mentor, $startup, $task, $data;
    /**
     * Create a new job instance.
     */
    public function __construct($mentor, $startup, $task, $data)
    {
        $this->mentor = $mentor;
        $this->startup = $startup;
        $this->task = $task;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
                Notification::send([$this->mentor, $this->startup], new TaskNotification($this->data));
            } catch (\Throwable $th) {
                Log::error('Notification sending failed: ' . $th->getMessage());
            }

            // رسالة للمستخدم
            if ($this->task->user->country_code_w_a && $this->task->user->whatsapp) {
                $phoneNumber = $this->task->user->country_code_w_a . $this->task->user->whatsapp;
                $message = "Dear " . $this->task->user->name . ", This Is notification from " . $this->task->company->cycledata->title . ", the mentor " . $this->task->mentor->name . " has created a new Task ( " . $this->task->title ." )";
                $result = app('App\Services\WhatsAppService')->sendMessage($phoneNumber, $message);
                Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
            }

            // رسالة للمنتور
            if ($this->task->mentor->country_code_w_a && $this->task->mentor->whatsapp) {
                $phoneNumber = $this->task->mentor->country_code_w_a . $this->task->mentor->whatsapp;
                $message = "Dear " . $this->task->mentor->name . ", This Is notification from " . $this->task->company->cycledata->title . ", the startup " . $this->task->user->name . " has created a new Task ( " . $this->task->title ." )";
                $result = app('App\Services\WhatsAppService')->sendMessage($phoneNumber, $message);
                Log::info('Message sent to mentor: ' . $phoneNumber . ' Result: ' . json_encode($result));
            }
        
    }
}
