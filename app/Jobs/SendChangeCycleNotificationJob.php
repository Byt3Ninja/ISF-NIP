<?php

namespace App\Jobs;

use App\Step;
use App\User;
use App\Events\ChangeStep;
use Illuminate\Bus\Queueable;
use App\Notifications\ChangeCycle;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Company;

class SendChangeCycleNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $company;
    protected $step;
    protected $last_step;
    protected $new_step;

    public function __construct($company, $step, $last_step, $new_step)
    {
        $this->company = $company;
        $this->step = $step;
        $this->last_step = $last_step;
        $this->new_step = $new_step;
    }

    public function handle()
    {
        // log::info("notifying data: " . $this->company );
       if($this->step != "0"){
        $cstep = Step::findOrFail($this->step);
       }
        $company = Company::with('user')->findOrFail($this->company);
        $data = [];

        $company_owner = $company->user;
        $data['new_step'] = $this->new_step ?? null;
        $data['last_step'] = $this->last_step ?? null;
        $data['owner_name_en'] = $company_owner->first_name_en . " " . $company_owner->last_name_en;
        $data['owner_name_ar'] = $company_owner->first_name_ar . " " . $company_owner->last_name_ar;
        $data['company_name_ar'] = $company_owner->company->name_ar;
        $data['company_name_en'] = $company_owner->company->name_en;
        $data['organization_name_ar'] = $cstep->cycle->organization->name_ar ?? null;
        $data['organization_name_en'] = $cstep->cycle->organization->name_en ?? null;
        $data['emailCompany'] = $company_owner->email;
        $data['status'] = $this->step != "0" ? 'in' : 'out';

        // Log::info("notifying data: ", $data); 
        // // إرسال الحدث بالبث
        // broadcast(new ChangeStep($data));
        //     log::info(" notifying user: " . $company_owner->email);
        $recipients = User::role("Registered")
            ->where('email', $company_owner->email)
            ->get();

             
            foreach ($recipients as $recipient) {
                try {
                    $recipient->notify(new ChangeCycle( $data));
                } catch (\Throwable $th) {
                    // يمكنك التسجيل هنا بدلاً من الرمي
                    Log::error("Error notifying user: " . $th->getMessage());
                }
            }
        
         
    }
}
