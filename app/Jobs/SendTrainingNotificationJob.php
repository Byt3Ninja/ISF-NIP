<?php

namespace App\Jobs;

use App\Step;
use App\User;
use App\Company;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use App\Notifications\TrainerEmail;
use App\TrainingSession;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Notification;
use App\Notifications\NewTrainingNotification;
use App\Services\TimezoneService;
use Carbon\Carbon;

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
    protected $endDatetime;

    /** Resolved in handle() (GV-9566): real instants and the step's platform zone. */
    protected ?Carbon $startsAt = null;
    protected ?Carbon $endsAt = null;
    protected ?string $stepTimezone = null;
    protected $hostUrl;

    public function __construct($step, $trainingSession, $title, $datetime, $join_url, $address, $type, $lang = 'en', $trainer_name = null, $trainer_email = null, $addOrEdit, $endDatetime = null, $hostUrl = null)
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
        $this->endDatetime = $endDatetime;
        // For the 'sessions' (Zoom) type only: the host/start link, kept
        // separate from $join_url so the trainer gets host controls while
        // founders get the plain join link.
        $this->hostUrl = $hostUrl;
    }

    /**
     * Trainer "you have been invited" email: only when a trainer is set, once
     * per (training instance, trainer email) so re-saving the same trainer
     * never re-sends, while assigning a different trainer does.
     */
    protected function notifyTrainerOfAssignment($step, array $extra = []): void
    {
        if (!$this->trainer_email) {
            return;
        }

        $trainingSessionId = is_object($this->trainingSession)
            ? ($this->trainingSession->id ?? null)
            : $this->trainingSession;

        if ($trainingSessionId) {
            $claimed = DB::table('training_sessions')->where('id', $trainingSessionId)
                ->where(function ($q) {
                    $q->whereNull('trainer_notified_email')
                      ->orWhere('trainer_notified_email', '!=', $this->trainer_email);
                })
                ->update([
                    'trainer_notified_at' => now(),
                    'trainer_notified_email' => $this->trainer_email,
                ]);

            if (!$claimed) {
                return; // already notified this trainer for this training
            }
        }

        $organization = optional($step->cycle)->organization;
        $mail = array_merge([
            'trainer_name' => $this->trainer_name ?? '',
            'title' => $this->title ?? '',
            'type' => $this->type ?? '',
            'program_name' => optional($step->cycle)->title ?? '',
        ], $extra);

        try {
            Notification::route('mail', $this->trainer_email)->notify(new TrainerEmail($mail, $organization));
            Log::info('Trainer notified: ' . $this->trainer_email);
        } catch (\Throwable $th) {
            if ($trainingSessionId) {
                DB::table('training_sessions')->where('id', $trainingSessionId)
                    ->update(['trainer_notified_at' => null, 'trainer_notified_email' => null]);
            }
            Log::error('Failed to notify trainer: ' . $th->getMessage());
        }
    }

    /**
     * Founder "new training created" email: only for creation, once per
     * founder per training instance, and only when the platform email
     * channel is enabled for them.
     */
    protected function notifyFoundersOfNewTraining($step, $companies, array $extra = [], bool $withTimes = false): void
    {
        if ($this->addOrEdit !== 'add') {
            return;
        }

        $organization = optional($step->cycle)->organization;

        // trainingSession is sometimes a scalar id, sometimes the created
        // Eloquent model (e.g. the 'online' type) -- always reduce to a
        // stable scalar so it fits the session_ref column and stays constant
        // across retries (casting the model itself gives a JSON blob with
        // volatile timestamps that both overflows the column and never
        // matches itself twice).
        $trainingSessionId = is_object($this->trainingSession)
            ? ($this->trainingSession->id ?? $step->id)
            : ($this->trainingSession ?? $step->id);
        $sessionRef = (string) $trainingSessionId;

        foreach ($companies as $company) {
            $user = $company->user;
            if (!$user) {
                continue;
            }

            // Atomically claim this (step, session, type, company) triple so a
            // retried job never emails the same founder twice for one training.
            try {
                DB::table('training_notifications_sent')->insert([
                    'step_id' => $step->id,
                    'session_ref' => $sessionRef,
                    'session_type' => $this->type,
                    'company_id' => $company->id,
                    'created_at' => now(),
                ]);
            } catch (\Illuminate\Database\QueryException $e) {
                // 23000 is the SQL-standard integrity-constraint-violation class
                // (MySQL 1062 duplicate entry included in it) -- that, and only
                // that, means "already notified for this training instance".
                // Any other DB error (e.g. a value too long for the column)
                // must not be mistaken for a duplicate and silently swallowed.
                if ($e->getCode() !== '23000') {
                    Log::error('training_notifications_sent claim failed: ' . $e->getMessage());
                }
                continue;
            }

            // GV-9566: each founder reads the session in their own timezone.
            $start = $this->forRecipient($this->startsAt, $user);
            $end = $this->forRecipient($this->endsAt, $user);

            $data = array_merge([
                'name' => trim($user->first_name_en . ' ' . $user->last_name_en),
                'title' => $this->title,
                'date' => $start ? $start->format('Y-m-d h:i A') : $this->datetime,
                'type' => $this->type,
                'organization' => $organization,
            ], $extra);

            if ($withTimes) {
                $data['start_time'] = $start ? $start->format('h:i A') : '';
                $data['end_time'] = $end ? $end->format('h:i A') : '';
            }

            try {
                $user->notify(new NewTrainingNotification($data));
                Log::info('Sent New Training notification to ' . $user->email);
            } catch (\Throwable $th) {
                DB::table('training_notifications_sent')
                    ->where('step_id', $step->id)
                    ->where('session_ref', $sessionRef)
                    ->where('session_type', $this->type)
                    ->where('company_id', $company->id)
                    ->delete();
                Log::error('Failed to send notification to ' . $user->email . ': ' . $th->getMessage());
            }
        }
    }

     public function handle()
    {
        $step = Step::find($this->step);
        if (!$step || !$step->cycle_id) {
            Log::error("SendTrainingNotificationJob failed: Step with ID {$this->step} not found or has no cycle_id.");
            $this->fail(new \Exception("Step with ID {$this->step} not found or has no cycle_id."));
            return;
        }

        $this->stepTimezone = TimezoneService::forStep($step->id);
        [$this->startsAt, $this->endsAt] = $this->sessionInstants();
        // The trainer has no account, so no timezone of their own.
        $trainerStart = $this->forRecipient($this->startsAt, null);
        $trainerEnd = $this->forRecipient($this->endsAt, null);

        $organization = optional($step->cycle)->organization;
        $org_name = $organization->{'name_' . $this->lang} ?? 'Unknown Organization';
        Log::info("SendTrainingNotificationJob: org_name: " . $org_name);
        if ($this->type == 'offline') {
            // Notify Trainer
            $this->notifyTrainerOfAssignment($step, [
                'session_date' => $trainerStart ? $trainerStart->format('Y-m-d') : ($this->datetime ?? ''),
                'start_time' => $trainerStart ? $trainerStart->format('h:i A') : '',
                'end_time' => $trainerEnd ? $trainerEnd->format('h:i A') : '',
                'join_url' => $this->join_url ?? '',
                'Address' => $this->address ?? '',
            ]);

            $companies = Company::where('cycle', $step->cycle_id)->where('step', $step->id)
                ->with(['user', 'cycledata', 'stepdata'])
                ->get();

            $this->notifyFoundersOfNewTraining($step, $companies, [
                'Address' => $this->address ?? '',
                'join_url' => $this->join_url ?? '',
                'trainer_name' => $this->trainer_name ?? '',
                'trainer_email' => $this->trainer_email ?? '',
            ]);

            foreach ($companies as $company) {
                $user = $company->user;
                if (!$user) {
                    Log::warning("No user found for company ID {$company->id}. Skipping notification.");
                    continue;
                }

                $data = [
                    'title' => $this->title ?? 'No Title',
                    'join_url' => $this->join_url ?? 'No Location',
                    'Address' => $this->address ?? 'No Address',
                ];

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

            // The 'online' type has no live meeting link -- founders access a
            // pre-recorded/self-paced course instead, at the same URL as the
            // "View Course" action in the admin sessions list.
            $courseId = is_object($this->trainingSession) ? ($this->trainingSession->training_Address ?? null) : null;
            $courseUrl = $courseId ? url('/dashboard/training/all/' . $courseId) : '';

            $this->notifyFoundersOfNewTraining($step, $companies, [
                'join_url' => $courseUrl,
            ]);

            foreach ($companies as $company) {
                $user = $company->user;
                if (!$user) {
                    Log::warning("No user found for company ID {$company->id}. Skipping notification.");
                    continue;
                }

                $phoneNumber = $user->country_code_w_a . $user->whatsapp;
                $message = $this->addOrEdit == 'add' ?
                    "Dear " . $user->name . ",\nNew Training Session has been created: " . $this->title . "\nThanks from " . $org_name :
                    "Dear " . $user->name . ",\nTraining Session has been Updated: " . $this->title . "\nThanks from " . $org_name;

                SendWhatsAppMessageJob::dispatch($phoneNumber, $message);
            }
        } elseif ($this->type == 'sessions') {
            // Notify Trainer
            $this->notifyTrainerOfAssignment($step, [
                'session_date' => $trainerStart ? $trainerStart->format('Y-m-d') : ($this->datetime ?? ''),
                'start_time' => $trainerStart ? $trainerStart->format('h:i A') : '',
                'end_time' => $trainerEnd ? $trainerEnd->format('h:i A') : '',
                // The trainer hosts the meeting, so they get the start/host
                // link; founders (below) get the plain join link instead.
                'join_url' => $this->hostUrl ?? $this->join_url ?? '',
            ]);

            $companies = Company::where('cycle', $step->cycle_id)->where('step', $step->id)
                ->with(['user', 'cycledata', 'stepdata'])
                ->get();

            $this->notifyFoundersOfNewTraining($step, $companies, [
                'join_url' => $this->join_url ?? '',
                'trainer_name' => $this->trainer_name ?? '',
                'trainer_email' => $this->trainer_email ?? '',
            ], true);

            foreach ($companies as $company) {
                $user = $company->user;
                if (!$user) {
                    Log::warning("No user found for company ID {$company->id}. Skipping notification.");
                    continue;
                }

                $joinUrl = $this->join_url ?? 'Unknown Join URL';
                $phoneNumber = $user->country_code_w_a . $user->whatsapp;
                $message = $this->addOrEdit == 'add' ?
                    "Dear " . $user->name . ",\nNew Training Session has been created: " . $this->title . ",\nJoin Link: " . $joinUrl . "\nThanks from " . $org_name :
                    "Dear " . $user->name . ",\nTraining Session has been Updated: " . $this->title . ",\nJoin Link: " . $joinUrl . "\nThanks from " . $org_name;

                SendWhatsAppMessageJob::dispatch($phoneNumber, $message);
            }
        }
    }

    public function failed(\Throwable $exception)
    {
        Log::error('Job failed: ' . $exception->getMessage());
    }

    /**
     * The session's start/end as real instants (GV-9566).
     *
     * Read from the stored unix timestamps; $this->datetime (a display string
     * of platform wall-clock) is only the fallback.
     *
     * @return array{0: ?Carbon, 1: ?Carbon}
     */
    protected function sessionInstants(): array
    {
        $id = is_object($this->trainingSession) ? ($this->trainingSession->id ?? null) : $this->trainingSession;
        $row = is_numeric($id) ? DB::table('training_sessions')->where('id', $id)->first(['datetime', 'enddatetime']) : null;

        return [
            TimezoneService::wallClock($row->datetime ?? $this->datetime, $this->stepTimezone),
            TimezoneService::wallClock($row->enddatetime ?? $this->endDatetime, $this->stepTimezone),
        ];
    }

    /** A session instant as $recipient reads it; no zone of their own => the step's platform zone. */
    protected function forRecipient(?Carbon $moment, $recipient): ?Carbon
    {
        if (is_null($moment)) {
            return null;
        }

        return TimezoneService::forViewer($moment->copy()->setTimezone($this->stepTimezone ?? TimezoneService::platform()), $recipient);
    }
}
