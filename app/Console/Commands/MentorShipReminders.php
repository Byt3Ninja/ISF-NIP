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
        $data = [];
        $schedules = MentorshipSchedule::get()->groupBy('organization_id');
        $mentorshipRequests = MentorshipRequest::with(['user.company', 'mentor', 'cycle'])->get();
        foreach ($mentorshipRequests as $request) {
            $orgId = $request->organization_id;
            if (!$orgId || !isset($schedules[$orgId])) {
                continue;
            }
            foreach ($schedules[$orgId] as $schedule) {
                    $hours = $schedule->value;
                    $diffInHours = now()->diffInHours($request->zoom_date, false);

                    if ($diffInHours > 0 && $diffInHours == $hours) {
                        $data['date']        = $request->zoom_date->format('Y-m-d H:i:s');
                        $data['company_name']= $request->user->company->{'name_' . $l};
                        $data['Mentorname']  = $request->mentor->first_name_en . " " . $request->mentor->last_name_en;
                        $data['period']      = $request->period;
                        $data['title']       = $request->title;
                        $data['join_url']    = $request->join_url;
                        $data['start_url']   = $request->start_url;

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
                    }
                
                if ($request->cycle && $request->cycle->is_active_Rminder == 1) {
                    $status = 'sent';
                } elseif ($request->cycle && $request->cycle->is_active_Rminder == 0) {
                    $status = 'skipped';
                } else {
                    $status = 'failed';
                }
                $user = User::find($request->mentor_id);
                $channel = [];
                if ($user && $user->accept_whatsapp == 1) {
                    $channel[] = 'whatsapp';
                }
                if ($user && $user->accept_email == 1) {
                    $channel[] = 'email';
                }
                if ($user && $user->accept_pusher == 1) {
                    $channel[] = 'in-app';
                }
                $channel = implode(',', $channel);
                if ($request->mentor) {
                    ReminderLog::create([
                        'user_id'  => $user->id,
                        'type'     => 'Mentor',
                        'cycle_id' => $request->cycle->id ?? null,
                        'channel'  => $channel,
                        'status'   => $status
                    ]);
                }
                if ($request->user) {
                    ReminderLog::create([
                        'user_id'  => $request->user->id,
                        'type'     => 'User',
                        'cycle_id' => $request->cycle->id ?? null,
                        'channel'  => $channel,
                        'status'   => $status
                    ]);
                }
            }
        }

        echo  'success';
        } catch (\Exception $e) {
            \Log::error($e);
        }
    }
}
