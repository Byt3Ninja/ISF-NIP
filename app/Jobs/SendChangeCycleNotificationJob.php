<?php

namespace App\Jobs;

use App\Step;
use App\User;
use App\Role;
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
use Carbon\Carbon;

class SendChangeCycleNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $company;
    protected $step;
    protected $last_step;
    protected $new_step;
    protected $program;

    public function __construct($company, $step, $last_step, $new_step, $program = null)
    {
        $this->company = $company;
        $this->step = $step;
        $this->last_step = $last_step;
        $this->new_step = $new_step;
        $this->program = $program;
    }

    public function handle()
    {
        $cstep = null;
        $program = $this->program ?? null;
        // log::info("notifying data: " . $this->company );
        if($this->step != "0"){
            $cstep = Step::with('cycle.organization')->findOrFail($this->step);
        }
        $company = Company::with('user')->findOrFail($this->company);
        $data = [];

		$start_date = '-';
		$end_date = '-';
		if( $cstep && $cstep->from ) { $start_date = Carbon::parse($cstep->from)->format('Y-m-d'); }
		if( $cstep && $cstep->to ) { $end_date = Carbon::parse($cstep->to)->format('Y-m-d'); }
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
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        $data['phase_description'] = $cstep?->description ?? null;
        $data['program_name'] = $cstep?->cycle?->title ?? null;
        $data['program_id'] = $cstep?->cycle?->id ?? null;
        $data['organization'] = $cstep?->cycle?->organization ?? null;
        $data['preferred_language'] = $company_owner->language;
        $data['program'] = $program ?? $cstep?->cycle;

        // Log::info("notifying data: ", $data); 
        // // إرسال الحدث بالبث
        // broadcast(new ChangeStep($data));
        //     log::info(" notifying user: " . $company_owner->email);

        // $recipients = User::role("Registered")->where('email', $company_owner->email)->get();
        $recipients = Role::where('default_for', 'registered')->with(['users', 'users.roles', 'users.organizations', 'users.company', 'users.company.cycledata'])->get();
		$recipients = $recipients->flatMap(function ($role) {
			return $role->users;
		})->where('email', $company_owner->email)->values();

        // Claim this exact phase move so a retry never double-emails the
        // founder; moving to a DIFFERENT phase later is allowed to notify
        // again. Disapproval ($data['status'] === 'out') has no phase to
        // claim and keeps using its own (unrelated) email entirely.
        $claimed = true;
        if ($data['status'] === 'in') {
            $claimed = Company::where('id', $this->company)
                ->where(function ($q) {
                    $q->whereNull('phase_notified_step_id')
                      ->orWhere('phase_notified_step_id', '!=', $this->step);
                })
                ->update(['phase_notified_step_id' => $this->step]) > 0;
        }

        if (!$claimed) {
            return;
        }

        foreach ($recipients as $recipient) {
            try {
                $data['preferred_language'] = $recipient->language;
                $recipient->notify(new ChangeCycle( $data ) );
            } catch (\Throwable $th) {
                if ($data['status'] === 'in') {
                    Company::where('id', $this->company)->update(['phase_notified_step_id' => null]);
                }
                // يمكنك التسجيل هنا بدلاً من الرمي
                Log::error("Error notifying user: " . $th->getMessage());
            }
        }
    }
}
