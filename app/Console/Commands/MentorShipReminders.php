<?php

namespace App\Console\Commands;

use log;
use App\User;
use App\ReminderLog;
use App\MentorshipRequest;
use App\MentorshipSchedule;
use Illuminate\Console\Command;
use App\Notifications\MentorshipRequestNotification;

class MentorShipReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mentorshipReminder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mentorship Reminders to send notfy evrey minute to startup Command';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
        $l = app()->getLocale();
        $mentorshipRequests = MentorshipRequest::with(['user.company', 'mentor', 'cycle'])->where('approved', 'Yes')->get();
        
        foreach ($mentorshipRequests as $request) {
            if (!$request->cycle) {
                continue;
            }
            $schedules = MentorshipSchedule::where('cycle_id', $request->cycle->id)->get();
            foreach ($schedules as $schedule) {
                $hours = $schedule->value; 
                
                $diffInHours = now()->diffInHours($request->zoom_date, false);
               
                if ($diffInHours > 0 && $diffInHours == $hours) {
                    $data['date']         = $request->zoom_date->format('Y-m-d H:i:s');
                    $data['company_name'] = $request->user->company->{'name_' . $l} ?? '';
                    $data['Mentorname']   = $request->mentor?->first_name_en . " " . $request->mentor?->last_name_en;
                    $data['period']       = $request->period;
                    $data['title']        = $request->title;
                    $data['join_url']     = $request->join_url;
                    $data['start_url']    = $request->start_url;
                    // The organization running the session brands the email and routes SMTP.
                    $data['organizationId'] = $request->organization_id ?: $request->cycle->organization_id;
                    if ($request->approved == 'No') {
                        $data['status'] = 'Rejected';
                    } elseif ($request->approved == 'Yes') {
                        $data['status'] = 'Approved';
                    } else {
                        $data['status'] = 'Pending';
                    }
                    if ($request->mentor) {
                        $request->mentor->notify(
                            new MentorshipRequestNotification($data, 'Reminder')
                        );
                    }
                    if ($request->user) {
                        $request->user->notify(
                            new MentorshipRequestNotification($data)
                        );
                    }
                    $status = ($request->cycle && $request->cycle->is_active_Rminder == 1) ? 'sent' : 'failed';
                    $mentor = User::find($request->mentor_id);
                    $channels = [];
                    if ($mentor && $mentor->accept_whatsapp == 1) $channels[] = 'whatsapp';
                    if ($mentor && $mentor->accept_email == 1) $channels[] = 'email';
                    if ($mentor && $mentor->accept_pusher == 1) $channels[] = 'in-app';
                    $channel = implode(',', $channels);
                    if ($request->mentor) {
                        ReminderLog::create([
                            'user_id'  => $mentor->id,
                            'type'     => 'Mentor',
                            'cycle_id' => $request->cycle->id,
                            'channel'  => $channel,
                            'status'   => $status
                        ]);
                    }
                    if ($request->user) {
                        ReminderLog::create([
                            'user_id'  => $request->user->id,
                            'type'     => 'User',
                            'cycle_id' => $request->cycle->id,
                            'channel'  => $channel,
                            'status'   => $status
                        ]);
                    }
                }
            }
        }

        echo  'success';
        } catch (\Exception $e) {
            \Log::error($e);
        }
    }
}
