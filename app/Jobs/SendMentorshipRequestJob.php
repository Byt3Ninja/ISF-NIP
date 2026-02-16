<?php

namespace App\Jobs;

use App\Events\MentorshipSessionUpdated;
use App\Events\RequestMentorship;
use App\Events\WebMessage;
use App\MentorshipRequest;
use App\Notifications\MentorshipRequestNotification;
use App\Notifications\MentorshipSession;
use App\Notifications\MentorshipSessionMentor;
use App\Notifications\TemplateEmail;
use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SendMentorshipRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $mentorId;
    protected $date;
    protected $period;
    protected $userId;
    protected $onlineMeeting;
    protected $action;
    protected $id;
    protected $stat;
    /**
     * Create a new job instance.
     */
    public function __construct($mentorId, $date, $period = null, $userId = null, $onlineMeeting = null, $id = null, $stat = null, $action = null)
    {
        $this->mentorId = $mentorId;
        $this->date = $date;
        $this->period = $period;
        $this->userId = $userId;
        $this->onlineMeeting = $onlineMeeting;
        $this->id = $id;
        $this->stat = $stat;
        $this->action = $action;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $whatsAppService = app()->make(\App\Services\WhatsAppService::class);
        if ($this->action == 'addwait') {

            $data = [];
            $mentor = User::find($this->mentorId);
            // ======> notify Mentor <=================
            $data['Mentorname'] = $mentor->first_name_en . " " . $mentor->last_name_en;
            $data['email'] = $mentor->email;
            $data['date'] = $this->date;
            $data['period'] = $this->period;
            $data['user_name'] = Auth::user()->first_name_en . " " . Auth::user()->last_name_en;
            $data['company_name'] = Auth::user()->company->name_en;
            try {
                $mentor->notify(new MentorshipRequestNotification($data));
                log::info('Sent New Mentorship Request notification to ' . $mentor->email);
            } catch (\Throwable $th) {
                throw $th;
            }
            // Broadcast the RequestMentorship event
            // broadcast(new RequestMentorship($data));
            $phoneNumber = $mentor->country_code_w_a . $mentor->whatsapp;
            $Massage = "Dear " . $mentor->name . ",
 				'" . $data['company_name'] . "' has sent you a mentorship request, request will start: " . $data['date'] . ",Thanks from " . env('APP_NAME');
            $result = $whatsAppService->sendMessage($phoneNumber, $Massage);
            Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
        }

        if ($this->action == 'add') {
            $mentor = User::findOrFail($this->mentorId);
            $mentor_name = $mentor->name;

            $startup_owner = User::findOrFail($this->userId);
            $startup_owner_name = $startup_owner->name;
            /// Notify Mentor
            $user = new User();
            $user->email = $mentor->email;
            $user->id = $mentor->id;
            $MSG = new MentorshipSession;
            $MSG->Message = [
                "New Mentorship session - VI portal",
                "Dear " . $mentor_name . ",",
                "Kindly note you have a new mentorship session with '" . $startup_owner_name . "', Session will start:" . $this->date . ", Check your account to join the session ",
                "/dashboard"
            ];
            if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                try {
                    $user->notify($MSG);
                } catch (\Throwable $th) {
                    // throw $th;
                }
                $phoneNumber = $startup_owner->country_code_w_a . $startup_owner->whatsapp;
                $Massage = "Dear " . $mentor_name . ", 
                This Is notification new mentorship session with '" . $startup_owner_name . "',
                Session will start:" . $this->date . ",
                Check your account to join the session ";
                $whatsAppService = app()->make(\App\Services\WhatsAppService::class);
                $result = $whatsAppService->sendMessage($phoneNumber, $Massage);
                // $result = new MentorshipRequestNotification($data);
                Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
            }
            /// Notify Startup Owner
            $user = new User();
            $user->email = $startup_owner->email;
            $user->id = $startup_owner->id;
            $MSG = new MentorshipSession;
            $MSG->Message = [
                "New Mentorship session - VI portal",
                "Dear " . $startup_owner_name . ",",
                "Kindly note you have a new mentorship session with '" . $mentor_name . "', Session will start:" . $this->date . ", Check your account to join the session ",
                "/dashboard"
            ];
            if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                try {
                    $user->notify($MSG);

                } catch (\Throwable $th) {
                    // throw $th;
                }
                $phoneNumber = $startup_owner->country_code_w_a . $startup_owner->whatsapp;
                $Massage = "Dear " . $startup_owner->name . ",
                This Is notification new mentorship session with '" . $startup_owner_name . "',
                Session will start: " . $this->date . ",
                Session Link: " . $this->onlineMeeting['data']['join_url'] . ",
                Thanks from " . env('APP_NAME');
                $result = $whatsAppService->sendMessage($phoneNumber, $Massage);
                Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
            }
        }

        if ($this->action == 'modifySession') {
            /// Notify Mentor
            $locale = app()->getLocale() ?? 'en';
            $session = MentorshipRequest::findOrFail($this->id);
            $rName = User::findOrFail($session->mentor_id);
            $rName_usr = User::findOrFail($session->user_id);
            $user = new User();
            $user->email = $rName->email;
            $user->id = $rName->id;
            $rFullName = $rName->name;
            $rFullName_usr = $rName_usr->name;
            $mentorMessage =[
                'subject' => __('messages.New_mentorship_session', [], $locale) . $session->user->company->name_en,
                'title' => __('messages.mentorship_session_subject: ', [], $locale) . $session->user->company->name_en,
                'mentorName' => $rFullName,
                'startupOwnerName' => $rFullName_usr,
                'startupName' => $session->user->company->name_en ?? '',
                'sessionDate' => $session->zoom_date,
                'duration' => $session->period ?? '60 min',
                'sessionType' => $session->session_type ?? 'Online',
                'meetingLink' => $session->start_url ?? '',
                'meetingPass' => $session->zoom_passcode ?? '',
                'location' => $session->orgnization->google_map_url ?? '',
                'notes' => $session->notes ?? '',
                'attachments' => $session->attachments ?? [],
                'status' => $session->approved ?? '',
                'action' => 'modifySession',
            ];
            $MSGM = new MentorshipSessionMentor($mentorMessage);
            if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                try {
                    $rName->notify($MSGM);
                } catch (\Throwable $th) {
                    // throw $th;
                }
                $phoneNumber = $rName->country_code_w_a . $rName->whatsapp;
                $Massage = "Dear " . $rName->name . ",
				you have a new mentorship session with '" . $rFullName . "',
				Session will start:" . $this->date . ",
				Check your account to join the session,
				Thanks from " . env('APP_NAME');
                $result = $whatsAppService->sendMessage($phoneNumber, $Massage);
                Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
            }
            // /// Notify User
            $user = new User();
            $user->email = $rName_usr->email;
            $user->id = $rName_usr->id;
            $message = [
                "New Mentorship session - VI portal",
                "Dear " . $rFullName_usr . ",",
                "Kindly note you have a new mentorship session with '" . $rFullName . "', Session will start:" . $this->date . ", Check your account to join the session ",
                "/dashboard",
                'Kindly note you have a new mentorship session with ' . $rFullName . ', Session will start: ' . $this->date,
            ];
            $MSG = new TemplateEmail($message);
            if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                try {
                    $user->notify($MSG);
                } catch (\Throwable $th) {
                    // throw $th;
                }
                $phoneNumber = $rName_usr->country_code_w_a . $rName_usr->whatsapp;
                $Massage = "Dear " . $rName_usr->name . ",
				you have a new mentorship session with '" . $rFullName . "',
				Session will start:" . $this->date . ",
				Check your account to join the session,
				Thanks from " . env('APP_NAME');
                $result = $whatsAppService->sendMessage($phoneNumber, $Massage);
                Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
            }
        }
        if ($this->action == 'modify') {
            $locale = app()->getLocale() ?? 'en';
            $session = MentorshipRequest::findOrFail($this->id);
            $rName = User::findOrFail($session->mentor_id);
            $rName_usr = User::findOrFail($session->user_id);
            $user = new User();
            $user->email = $rName->email;
            $user->id = $rName->id;
            $date_Request = $session->zoom_date;
            $rFullName = $rName->name;
            $rFullName_usr = $rName_usr->name;
            $mentorMessage =[
                'subject' => __('messages.New_mentorship_session', [], $locale) . $session->user->company->name_en,
                'title' => __('messages.mentorship_session_subject:'.$session->user->company->name_en.'', [], $locale),
                'mentorName' => $rFullName,
                'startupOwnerName' => $rFullName_usr,
                'startupName' => $session->user->company->name_en ?? '',
                'sessionDate' => $session->zoom_date,
                'duration' => $session->period ?? '60 min',
                'sessionType' => $session->session_type ?? 'Online',
                'meetingLink' => $session->start_url ?? '',
                'meetingPass' => $session->zoom_passcode ?? '',
                'location' => $session->orgnization->google_map_url ?? '',
                'notes' => $session->notes ?? '',
                'attachments' => $session->attachments ?? [],
                'status' => $session->approved ?? '',
                'action' => 'modify',
            ];
            $MSGM = new MentorshipSessionMentor($mentorMessage);
            $phoneNumber = $rName_usr->country_code_w_a . $rName_usr->whatsapp;
            $Massage = "Dear " . $rFullName . ", you have a new mentorship session with '" . $rFullName_usr . "',is Rejected Thanks from " . env('APP_NAME');
            if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                try {
                    $rName->notify($MSGM);
                } catch (\Throwable $th) {
                    // throw $th;
                }
                $result = $whatsAppService->sendMessage($phoneNumber, $Massage);
                Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
            }
            /// Notify User
            $user = new User();
            $user->email = $rName_usr->email;
            $user->id = $rName_usr->id;
            $userMessage = [
                __("messages.mentorship_session_subject", [], $locale),
                __("messages.dear", ['name' => $rFullName_usr], $locale),
                $this->stat == 'Yes'
                    ? __("messages.approved_message", ['other_party' => $rFullName], $locale)
                    : __("messages.denied_message", ['other_party' => $rFullName], $locale),
                "/dashboard",
                $this->stat == 'Yes'
                    ? __("messages.mentorship_session_action", ['action' => __('messages.Approved'), 'name' => $rFullName])
                    : __("messages.mentorship_session_action", ['action' => __('messages.Rejected'), 'name' => $rFullName]),
            ];
            $MSG = new MentorshipSession($userMessage);
            $phoneNumber = $rName_usr->country_code_w_a . $rName_usr->whatsapp;
            $Massage = "Dear " . $rFullName_usr . ",you have a new mentorship session with '" . $rFullName . "',is Rejected Thanks from " . env('APP_NAME');
            if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                try {
                    $user->notify($MSG);
                } catch (\Throwable $th) {
                    // throw $th;
                }
                $result = $whatsAppService->sendMessage($phoneNumber, $Massage);
                Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
            }
            broadcast(new MentorshipSessionUpdated(
                $rName->id,
                $rName_usr->id,
                [
                    'email' => $rName->email,
                    'name' => $rName->name,
                ],
                $this->stat
            ));
        }
    }
}
