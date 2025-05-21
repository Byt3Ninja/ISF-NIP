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
        if($this->type == 'offline'){
           
              // ======> Notify Trainer <==========
        if ($this->trainer_email) {
            $mail = [];
            $mail['trainer_name'] = $this->trainer_name ?? 'Unknown Trainer';
            $mail['title'] = $this->title ?? 'Unknown Title';
            $mail['date'] = $this->datetime ?? 'Unknown Date';
            $mail['type'] = $this->type ?? 'Unknown Type';
            $mail['join_url'] = $this->join_url ?? 'Unknown Join URL';
            $mail['organization_name_en'] = optional(optional(optional(Step::find($this->step))->cycle)->organization)->name_en ?? 'Unknown Organization';
            $mail['organization_name_ar'] = optional(optional(optional(Step::find($this->step))->cycle)->organization)->name_ar ?? 'Unknown Organization';

            try {
                Notification::route('mail', $this->trainer_email)->notify(new TrainerEmail($mail));
                Log::info('Trainer notified: ' . $this->trainer_email);
            } catch (\Throwable $th) {
                Log::error('Failed to notify trainer: ' . $th->getMessage());
            }
        }
        
        $cycleId = Step::find($this->step)->cycle_id;
        $companies = Company::where('cycle', $cycleId)->get();

        foreach ($companies as $company) {
            $user = User::where('company_id', $company->id)->first();
            if (!$user) continue;


            $data = [
                'name' => $user->first_name_en . " " . $user->last_name_en ?? 'No Name',
                'email' => $user->email ?? 'No Email',
                'cycle' => $company->cycledata->title ?? 'No Cycle',
                'step' => $company->stepdata->title ?? 'No Step',
                'title' => $this->title ?? 'No Title',
                'date' => $this->datetime ?? 'No Date',
                'type' => 'offline',
                'join_url' => $join_url ?? 'No Location',
                'Address' => $address ?? 'No Address',
            ];

            try {
                $user->notify(new NewTrainingNotification($data));
                Log::info('Sent New Training notification to ' . $user->email);
            } catch (\Throwable $th) {
                Log::error('Failed to send notification: ' . $th->getMessage());
            }

            $org_name = optional(optional(optional(Step::find($this->step))->cycle)->organization)->{'name_' . $this->lang} ?? 'Unknown Organization';
            $phoneNumber = $user->country_code_w_a . $user->whatsapp;
            if ($this->addOrEdit == 'add') {
                
$message = "Dear " . $user->name . ",
New Training Session has been created: ". $data['title'] .",
Google Map Link: " . $data['join_url'] . ",
Address: " . $data['Address'] . "
Thanks from " . $org_name;
            }else{
$Massage = "Dear " . $company->user->name . ",
Training Session has been Updated: ". $data['title'] .",
Google Map Link: " . $data['join_url'] . ",
Address: " . $data['Address'] . "
Thanks from " . $org_name;
            }
           SendWhatsAppMessageJob::dispatch($phoneNumber, $message);
        }

    }
    elseif($this->type == 'online'){

        $cycleId = Step::find($this->step)->cycle_id;
			$companies = Company::where('cycle', $cycleId)->get();
			// ======> notify Trainer <=================

			foreach ($companies as $company) {
				$data['name'] = $company->user->first_name_en . " " . $company->user->last_name_en ?? 'Unknown User';
				$data['email'] = $company->user->email ?? 'Unknown Email';
				$data['cycle'] = $company->cycledata->title ?? 'Unknown Cycle';
				$data['step'] = $company->stepdata->title ?? 'Unknown Step';
				$data['join_url'] = '';
				$data['type'] = 'online';
				$data['title'] = $title ?? 'Unknown Title';
				$data['date'] = $datetime ?? 'Unknown Date';
				try {
					$company->user->notify(new NewTrainingNotification($data));
					log::info('Sent New Training notification to ' . $company->user->email);
				} catch (\Throwable $th) {
					// throw $th;
				}
				$org_name = optional(optional(optional(Step::find($this->step))->cycle)->organization)->{'name_'. $this->lang} ?? 'Unknown Organization';
				$phoneNumber = $company->user->country_code_w_a . $company->user->whatsapp;
                if($this->addOrEdit == 'add'){
$Massage = "Dear " . $company->user->name . ",
New Training Session has been created: ". $data['title'] ."
Thanks from " . $org_name;
                }else{
$Massage = "Dear " . $company->user->name . ",
Training Session has been Updated: ". $data['title'] ."
Thanks from " . $org_name;
                }
                SendWhatsAppMessageJob::dispatch($phoneNumber, $Massage);
			}
    }elseif($this->type == 'sessions'){
          // ======> Notify Trainer <==========
        if ($this->trainer_email) {
            $mail = [];
            $mail['trainer_name'] = $this->trainer_name ?? 'Unknown Trainer';
            $mail['title'] = $this->title ?? 'Unknown Title';
            $mail['date'] = $this->datetime ?? 'Unknown Date';
            $mail['type'] = $this->type ?? 'Unknown Type';
            $mail['join_url'] = $this->join_url ?? 'Unknown Join URL';
            $mail['organization_name_en'] = optional(optional(optional(Step::find($this->step))->cycle)->organization)->name_en ?? 'Unknown Organization';
            $mail['organization_name_ar'] = optional(optional(optional(Step::find($this->step))->cycle)->organization)->name_ar ?? 'Unknown Organization';

            try {
                Notification::route('mail', $this->trainer_email)->notify(new TrainerEmail($mail));
                Log::info('Trainer notified: ' . $this->trainer_email);
            } catch (\Throwable $th) {
                Log::error('Failed to notify trainer: ' . $th->getMessage());
            }
        }

        $data = [];
				$cycleId = Step::find($this->step)->cycle_id;
				$companies = Company::where('cycle', $cycleId)->get();
				foreach ($companies as $company) {
					$data['name'] = $company->user->first_name_en . " " . $company->user->last_name_en ?? 'Unknown Name';
					$data['email'] = $company->user->email ?? 'Unknown Email';
					$data['cycle'] = $company->cycledata->title ?? 'Unknown Cycle';
					$data['step'] = $company->stepdata->title ?? 'Unknown Step';
					$data['type'] = 'sessions';
					$data['join_url'] = $join ?? 'Unknown Join URL';
					$data['title'] = $topic ?? 'Unknown Title';
					$data['date'] = $datetime ?? 'Unknown Date';
					
					$org_name = optional(optional(optional(Step::find($this->step))->cycle)->organization)->{'name_'. $this->lang} ?? 'Unknown Organization';
					$phoneNumber = $company->user->country_code_w_a . $company->user->whatsapp;
                    if($this->addOrEdit == 'add'){
				$Massage = "Dear " . $company->user->name . ",
New Training Session has been created: ". $data['title'] .",
Join Link : ". $data['join_url'] ."
Thanks from " . $org_name;
                    }else{
$Massage = "Dear " . $company->user->name . ",
Training Session has been Updated: ". $data['title'] .",
Join Link : ". $data['join_url'] ."
Thanks from " . $org_name;
                    }
                SendWhatsAppMessageJob::dispatch($phoneNumber, $Massage);
            }



    }
}


    public function failed(\Throwable $exception)
    {
        Log::error('Job failed: ' . $exception->getMessage());
    }
}
