<?php

namespace App\Jobs;

use App\Step;
use App\User;
use App\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use App\Notifications\TrainerEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewTrainingNotification;

class SendTrainingNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $step;
    protected $trainingSession;
    protected $title;
    protected $datetime;
    protected $join_url;
    protected $address;
    protected $type;
    protected $lang;
    protected $trainer_name;
    protected $trainer_email;
    protected $addOrEdit;

    public function __construct($step, $trainingSession, $title, $datetime, $join_url, $address, $type, $lang = 'en', $trainer_name = null, $trainer_email = null, $addOrEdit)
    {
        $this->step = $step;
        $this->trainingSession = $trainingSession;
        $this->title = $title;
        $this->datetime = $datetime;
        $this->join_url = $join_url;
        $this->address = $address;
        $this->lang = $lang;
        $this->type = $type;
        $this->addOrEdit = $addOrEdit;
        $this->trainer_name = $trainer_name;
        $this->trainer_email = $trainer_email;
    }

     public function handle()
    {
        $step = Step::find($this->step);
        if (!$step || !$step->cycle_id) {
            Log::error("SendTrainingNotificationJob failed: Step with ID {$this->step} not found or has no cycle_id.");
            $this->fail(new \Exception("Step with ID {$this->step} not found or has no cycle_id."));
            return;
        }

        $organization = optional($step->cycle)->organization;
        $org_name = $organization->{'name_' . $this->lang} ?? 'Unknown Organization';
        Log::info("SendTrainingNotificationJob: org_name: " . $org_name);
        if ($this->type == 'offline') {
            // Notify Trainer
            if ($this->trainer_email) {
                $mail = [
                    'trainer_name' => $this->trainer_name ?? 'Unknown Trainer',
                    'title' => $this->title ?? 'Unknown Title',
                    'date' => $this->datetime ?? 'Unknown Date',
                    'type' => $this->type ?? 'Unknown Type',
                    'join_url' => $this->join_url ?? 'Unknown Join URL',
                    'organization_name_en' => $organization->name_en ?? 'Unknown Organization',
                    'organization_name_ar' => $organization->name_ar ?? 'Unknown Organization',
                    'Address' => $this->address ?? 'Unknown Address',
                ];

                try {
                    Notification::route('mail', $this->trainer_email)->notify(new TrainerEmail($mail, $organization));
                    Log::info('Trainer notified: ' . $this->trainer_email);
                } catch (\Throwable $th) {
                    Log::error('Failed to notify trainer: ' . $th->getMessage());
                }
            }

            $companies = Company::where('cycle', $step->cycle_id)->where('step', $step->id)
                ->with(['user', 'cycledata', 'stepdata'])
                ->get();

            foreach ($companies as $company) {
                $user = $company->user;
                if (!$user) {
                    Log::warning("No user found for company ID {$company->id}. Skipping notification.");
                    continue;
                }

                $data = [
                    'name' => $user->first_name_en . " " . $user->last_name_en ?? 'No Name',
                    'email' => $user->email ?? 'No Email',
                    'cycle' => optional($company->cycledata)->title ?? 'No Cycle',
                    'step' => optional($company->stepdata)->title ?? 'No Step',
                    'title' => $this->title ?? 'No Title',
                    'date' => $this->datetime ?? 'No Date',
                    'type' => 'offline',
                    'join_url' => $this->join_url ?? 'No Location',
                    'Address' => $this->address ?? 'No Address',
                ];

                try {
                    $user->notify(new NewTrainingNotification($data));
                    Log::info('Sent New Training notification to ' . $user->email);
                } catch (\Throwable $th) {
                    Log::error('Failed to send notification to ' . $user->email . ': ' . $th->getMessage());
                }

                $phoneNumber = $user->country_code_w_a . $user->whatsapp;
                $message = $this->addOrEdit == 'add' ?
                    "Dear " . $user->name . ",\nNew Training Session has been created: " . $data['title'] . ",\nGoogle Map Link: " . $data['join_url'] . ",\nAddress: " . $data['Address'] . "\nThanks from " . $org_name :
                    "Dear " . $user->name . ",\nTraining Session has been Updated: " . $data['title'] . ",\nGoogle Map Link: " . $data['join_url'] . ",\nAddress: " . $data['Address'] . "\nThanks from " . $org_name;

                SendWhatsAppMessageJob::dispatch($phoneNumber, $message);
            }
        } elseif ($this->type == 'online') {
            $companies = Company::where('cycle', $step->cycle_id)->where('step', $step->id)
                ->with(['user', 'cycledata', 'stepdata'])
                ->get();

            foreach ($companies as $company) {
                $user = $company->user;
                if (!$user) {
                    Log::warning("No user found for company ID {$company->id}. Skipping notification.");
                    continue;
                }

                $data = [
                    'name' => $user->first_name_en . " " . $user->last_name_en ?? 'Unknown User',
                    'email' => $user->email ?? 'Unknown Email',
                    'cycle' => optional($company->cycledata)->title ?? 'Unknown Cycle',
                    'step' => optional($company->stepdata)->title ?? 'Unknown Step',
                    'join_url' => '',
                    'type' => 'online',
                    'title' => $this->title ?? 'Unknown Title',
                    'date' => $this->datetime ?? 'Unknown Date',
                ];

                try {
                    $user->notify(new NewTrainingNotification($data));
                    Log::info('Sent New Training notification to ' . $user->email);
                } catch (\Throwable $th) {
                    Log::error('Failed to send notification to ' . $user->email . ': ' . $th->getMessage());
                }

                $phoneNumber = $user->country_code_w_a . $user->whatsapp;
                $message = $this->addOrEdit == 'add' ?
                    "Dear " . $user->name . ",\nNew Training Session has been created: " . $data['title'] . "\nThanks from " . $org_name :
                    "Dear " . $user->name . ",\nTraining Session has been Updated: " . $data['title'] . "\nThanks from " . $org_name;

                SendWhatsAppMessageJob::dispatch($phoneNumber, $message);
            }
        } elseif ($this->type == 'sessions') {
            // Notify Trainer
            if ($this->trainer_email) {
                $mail = [
                    'trainer_name' => $this->trainer_name ?? 'Unknown Trainer',
                    'title' => $this->title ?? 'Unknown Title',
                    'date' => $this->datetime ?? 'Unknown Date',
                    'type' => $this->type ?? 'Unknown Type',
                    'join_url' => $this->join_url ?? 'Unknown Join URL',
                    'organization_name_en' => $organization->name_en ?? 'Unknown Organization',
                    'organization_name_ar' => $organization->name_ar ?? 'Unknown Organization',
                ];

                try {
                    Notification::route('mail', $this->trainer_email)->notify(new TrainerEmail($mail, $organization));
                    Log::info('Trainer notified: ' . $this->trainer_email);
                } catch (\Throwable $th) {
                    Log::error('Failed to notify trainer: ' . $th->getMessage());
                }
            }

            $companies = Company::where('cycle', $step->cycle_id)->where('step', $step->id)
                ->with(['user', 'cycledata', 'stepdata'])
                ->get();

            foreach ($companies as $company) {
                $user = $company->user;
                if (!$user) {
                    Log::warning("No user found for company ID {$company->id}. Skipping notification.");
                    continue;
                }

                $data = [
                    'name' => $user->first_name_en . " " . $user->last_name_en ?? 'Unknown Name',
                    'email' => $user->email ?? 'Unknown Email',
                    'cycle' => optional($company->cycledata)->title ?? 'Unknown Cycle',
                    'step' => optional($company->stepdata)->title ?? 'Unknown Step',
                    'type' => 'sessions',
                    'join_url' => $this->join_url ?? 'Unknown Join URL',
                    'title' => $this->title ?? 'Unknown Title',
                    'date' => $this->datetime ?? 'Unknown Date',
                ];

                try {
                    $user->notify(new NewTrainingNotification($data));
                    Log::info('Sent New Training notification to ' . $user->email);
                } catch (\Throwable $th) {
                    Log::error('Failed to send notification to ' . $user->email . ': ' . $th->getMessage());
                }

                $phoneNumber = $user->country_code_w_a . $user->whatsapp;
                $message = $this->addOrEdit == 'add' ?
                    "Dear " . $user->name . ",\nNew Training Session has been created: " . $data['title'] . ",\nJoin Link: " . $data['join_url'] . "\nThanks from " . $org_name :
                    "Dear " . $user->name . ",\nTraining Session has been Updated: " . $data['title'] . ",\nJoin Link: " . $data['join_url'] . "\nThanks from " . $org_name;

                SendWhatsAppMessageJob::dispatch($phoneNumber, $message);
            }
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Job failed: ' . $exception->getMessage());
    }
}
