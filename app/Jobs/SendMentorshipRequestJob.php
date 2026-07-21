<?php

namespace App\Jobs;

use App\Events\MentorshipSessionUpdated;
use App\Events\RequestMentorship;
use App\Events\WebMessage;
use App\MentorshipRequest;
use App\Services\FileManagerService;
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
use App\Notifications\MentorNotifyMentorshipNotification;
use App\Notifications\StartupNotifyMentorshipNotification;
use App\Notifications\StartupNotifyMentorshipApprovalNotification;
use App\Notifications\StartupNotifyMentorshipRejectionNotification;

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
            // $mentor = User::find($this->mentorId);
            try {
                // ======> notify Mentor <=================
                $locale = app()->getLocale() ?? 'en';
                $session = MentorshipRequest::with(['user', 'mentor', 'user.company', 'cycle', 'cycle.organization', 'organization'])->findOrFail($this->id);
                $session->attachments = $session->fileurl ? [
                    [
                        'url' => app(FileManagerService::class)->getUrl($session->fileurl, 'UsersDiskInviteMentor'),
                        'name' => basename($session->fileurl),
                        'path' => $session->fileurl
                    ]
                ] : [];

                $mentor = $session->mentor;
                $startup = $session->user;
                $mentorFullName = $mentor->name;

                $data['Mentorname'] = $mentor->first_name_en . " " . $mentor->last_name_en;
                $data['email'] = $mentor->email;
                $data['date'] = $this->date;
                $data['period'] = $this->period;
                $data['user_name'] = $startup->first_name_en . " " . $startup->last_name_en;
                $data['company_name'] = $startup->company->name_en;
                // $mentor->notify(new MentorshipRequestNotification($data));
                $startupFullName_user = $startup->name;
                $mentorMessage = [
                    __("messages.mentorship_session_subject", [], $locale),
                    __("messages.dear", ['name' => $mentorFullName], $locale),
                    $this->stat == 'Yes'
                        ? __("messages.approved_mentor", ['other_party' => $startupFullName_user], $locale)
                        : __("messages.denied_message", ['other_party' => $startupFullName_user], $locale),
                    "/dashboard",
                    $this->stat == 'Yes'
                        ? __("messages.mentorship_session_action", ['action' => __('messages.Approved'), 'name' => $startupFullName_user])
                        : __("messages.mentorship_session_action", ['action' => __('messages.Rejected'), 'name' => $startupFullName_user]),
                ];
                $mentor->notify(
                    new MentorNotifyMentorshipNotification(
                        $mentorMessage,
                        $session
                    )
                );
                log::info('Sent New Mentorship Request notification to ' . $mentor->email);

                // notify the startup to the directly assigned session
                $userMessage = [
                    __("messages.mentorship_session_subject", [], $locale),
                    __("messages.dear", ['name' => $startupFullName_user], $locale),
                    __("messages.approved_message", ['other_party' => $mentorFullName], $locale),
                    "/dashboard",
                    __("messages.mentorship_session_action", ['action' => __('messages.Approved'), 'name' => $mentorFullName]),
                ];
                $startup->notify(
                    new StartupNotifyMentorshipNotification(
                        $userMessage,
                        $session
                    )
                );
                log::info('Sent New Mentorship Request notification to ' . $startup->email);
            } catch (\Throwable $th) {
                Log::error('add wait Notification Error: ' . $th->getMessage());
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
                    Log::error('add Notification Error 1: ' . $th->getMessage());
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
                    Log::error('add Notification Error 2: ' . $th->getMessage());
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
                'notes' => $session->comment ?? '',
                'attachments' => $session->fileurl ? [['url' => env('APP_URL').'/dashboard/uploads/InviteMentor/'.$session->fileurl, 'name' => basename($session->fileurl), 'path' => $session->fileurl]] : [],
                'status' => $session->approved ?? '',
                'action' => 'modifySession',
                'user' => 'mentor',
                'lang' => $locale,
            ];
            $MSGM = new MentorshipSessionMentor($mentorMessage);
            if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                try {
                    $rName->notify($MSGM);
                } catch (\Throwable $th) {
                    // throw $th;
                    Log::error('modify session Notification Error 1: ' . $th->getMessage());
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
                'notes' => $session->comment ?? '',
                'attachments' => $session->fileurl ? [['url' => env('APP_URL').'/dashboard/uploads/InviteMentor/'.$session->fileurl, 'name' => basename($session->fileurl), 'path' => $session->fileurl]] : [],
                'status' => $session->approved ?? '',
                'action' => 'modifySession',
                'user' => 'user',
                'lang' => $locale,
            ];
            $MSG = new MentorshipSession($message);
            if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                try {
                    $rName_usr->notify($MSG);
                } catch (\Throwable $th) {
                    // throw $th;
                    Log::error('modify session Notification Error 2: ' . $th->getMessage());
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
            $session = MentorshipRequest::with(['user', 'mentor', 'user.company', 'cycle', 'cycle.organization', 'organization'])->findOrFail($this->id);
            $session->attachments = $session->fileurl ? [
                [
                    'url' => app(FileManagerService::class)->getUrl($session->fileurl, 'UsersDiskInviteMentor'),
                    'name' => basename($session->fileurl),
                    'path' => $session->fileurl
                ]
            ] : [];
            $mentor_user = $session->mentor;
            $startup_user = $session->user;

            $date_Request = $session->zoom_date;
            $mentorFullName = $mentor_user->name;
            $startupFullName_user = $startup_user->name;
            $mentorMessage = [
                __("messages.mentorship_session_subject", [], $locale),
                __("messages.dear", ['name' => $mentorFullName], $locale),
                $this->stat == 'Yes'
                    ? __("messages.approved_mentor", ['other_party' => $startupFullName_user], $locale)
                    : __("messages.denied_message", ['other_party' => $startupFullName_user], $locale),
                "/dashboard",
                $this->stat == 'Yes'
                    ? __("messages.mentorship_session_action", ['action' => __('messages.Approved'), 'name' => $startupFullName_user])
                    : __("messages.mentorship_session_action", ['action' => __('messages.Rejected'), 'name' => $startupFullName_user]),
            ];
            $MSG = new MentorshipSession($mentorMessage);
            $phoneNumber = $startup_user->country_code_w_a . $startup_user->whatsapp;
            $Massage = "Dear " . $mentorFullName . ", you have a new mentorship session with '" . $startupFullName_user . "',is Rejected Thanks from " . env('APP_NAME');
            if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                try {
                    // $mentor_user->notify($MSG);
                    $mentor_user->notify(
                        new MentorNotifyMentorshipNotification(
                            $mentorMessage,
                            $session
                        )
                    );
                } catch (\Throwable $th) {
                    // throw $th;
                    Log::error('modify mentorship Notification Error: ' . $th->getMessage());
                }
                $result = $whatsAppService->sendMessage($phoneNumber, $Massage);
                Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
            }
            /// Notify User
            $userMessage = [
                __("messages.mentorship_session_subject", [], $locale),
                __("messages.dear", ['name' => $startupFullName_user], $locale),
                $this->stat == 'Yes'
                    ? __("messages.approved_message", ['other_party' => $mentorFullName], $locale)
                    : __("messages.denied_message", ['other_party' => $mentorFullName], $locale),
                "/dashboard",
                $this->stat == 'Yes'
                    ? __("messages.mentorship_session_action", ['action' => __('messages.Approved'), 'name' => $mentorFullName])
                    : __("messages.mentorship_session_action", ['action' => __('messages.Rejected'), 'name' => $mentorFullName]),
            ];
            $MSG = new MentorshipSession($userMessage);
            $phoneNumber = $startup_user->country_code_w_a . $startup_user->whatsapp;
            $Massage = "Dear " . $startupFullName_user . ",you have a new mentorship session with '" . $mentorFullName . "',is Rejected Thanks from " . env('APP_NAME');
            if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                try {
                    // $startup_user->notify($MSG);
                    $status = strtolower( (string) $this->stat ) == 'yes' ? true : false;
                    if( $status )
                    {
                        $startup_user->notify(
                            new StartupNotifyMentorshipApprovalNotification(
                                $userMessage,
                                $session
                            )
                        );
                    }
                    else
                    {
                        $startup_user->notify(
                            new StartupNotifyMentorshipRejectionNotification(
                                $userMessage,
                                $session
                            )
                        );
                    }
                } catch (\Throwable $th) {
                    Log::error('modify user Notification Error: ' . $th->getMessage());
                    // throw $th;
                }
                $result = $whatsAppService->sendMessage($phoneNumber, $Massage);
                Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
            }
            broadcast(new MentorshipSessionUpdated(
                $mentor_user->id,
                $startup_user->id,
                [
                    'email' => $mentor_user->email,
                    'name' => $mentor_user->name,
                ],
                $this->stat
            ));
        }
    }
}
