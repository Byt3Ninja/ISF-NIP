<?php

namespace App\Jobs;

use Carbon\Carbon;
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
use Illuminate\Support\Facades\DB;
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
                $session = MentorshipRequest::with(['user', 'mentor', 'admin', 'user.company', 'cycle', 'cycle.organization', 'organization'])->findOrFail($this->id);
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

                $mentorLocale = $mentor ? $mentor->preferredLocale() : 'en';
                $startupLocale = $startup ? $startup->preferredLocale() : 'en';

                $data['Mentorname'] = $mentor->first_name_en . " " . $mentor->last_name_en;
                $data['email'] = $mentor->email;
                $data['date'] = $this->date;
                $data['period'] = $this->period;
                $data['user_name'] = $startup->first_name_en . " " . $startup->last_name_en;
                $data['company_name'] = $startup->company->name_en;
                // $mentor->notify(new MentorshipRequestNotification($data));
                $startupFullName_user = $startup->name;

                $session_date = strtotime($session->zoom_date);
                $start = Carbon::parse($session->zoom_date);
                $end = $start->copy()->addMinutes($session->period);
                $sessionDate = date('Y-m-d', $session_date);

                $sessionTimeMentor = date('h:i A', $session_date) . ' – ' . date('h:i A', $end->timestamp);
                if( $mentorLocale == 'ar' )
                {
                    $sessionTimeMentor = str_replace('AM', 'صباحاً', $sessionTimeMentor);
                    $sessionTimeMentor = str_replace('PM', 'مساءاً', $sessionTimeMentor);
                }

                $sessionTimeStartup = date('h:i A', $session_date) . ' – ' . date('h:i A', $end->timestamp);
                if( $startupLocale == 'ar' )
                {
                    $sessionTimeStartup = str_replace('AM', 'صباحاً', $sessionTimeStartup);
                    $sessionTimeStartup = str_replace('PM', 'مساءاً', $sessionTimeStartup);
                }

                // ======> notify Mentor <=================
                $mentorMessage = [
                    __("messages.mentorship_session_subject", [], $mentorLocale),
                    __("messages.dear", ['name' => $mentorFullName], $mentorLocale),
                    $this->stat == 'Yes'
                        ? __("messages.approved_mentor", ['other_party' => $startupFullName_user], $mentorLocale)
                        : __("messages.denied_message", ['other_party' => $startupFullName_user], $mentorLocale),
                    "/dashboard",
                    $this->stat == 'Yes'
                        ? __("messages.mentorship_session_notification_message", ['name' => $startupFullName_user, 'session_date' => $sessionDate, 'session_time' => $sessionTimeMentor], $mentorLocale)
                        : __("messages.mentorship_session_action", ['action' => __('messages.Rejected', [], $mentorLocale), 'name' => $startupFullName_user], $mentorLocale),
                ];
                // Atomically claim the "notify mentor of booked session" step so a
                // retry of this job never double-emails the mentor.
                $claimedMentorNotify = MentorshipRequest::where('id', $session->id)
                    ->whereNull('mentor_notified_at')
                    ->update(['mentor_notified_at' => now()]);

                if ($claimedMentorNotify) {
                    $mentor->notify(
                        new MentorNotifyMentorshipNotification(
                            $mentorMessage,
                            $session
                        )
                    );
                    log::info('Sent New Mentorship Request notification to ' . $mentor->email);
                }

                // notify the startup to the directly assigned session
                $userMessage = [
                    __("messages.mentorship_session_subject", [], $startupLocale),
                    __("messages.dear", ['name' => $startupFullName_user], $startupLocale),
                    __("messages.approved_message", ['other_party' => $mentorFullName], $startupLocale),
                    "/dashboard",
                    __("messages.mentorship_session_notification_message", ['name' => $mentorFullName, 'session_date' => $sessionDate, 'session_time' => $sessionTimeStartup], $startupLocale),
                ];
                // Atomically claim the "notify founder of scheduled session" step
                // so a retry of this job never double-emails the founder.
                $claimedFounderNotify = MentorshipRequest::where('id', $session->id)
                    ->whereNull('founder_notified_at')
                    ->update(['founder_notified_at' => now()]);

                if ($claimedFounderNotify) {
                    $startup->notify(
                        new StartupNotifyMentorshipNotification(
                            $userMessage,
                            $session
                        )
                    );
                    log::info('Sent New Mentorship Request notification to ' . $startup->email);
                }
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

            $mentorLocale = $mentor->preferredLocale();
            $startupLocale = $startup_owner->preferredLocale();

            $session = MentorshipRequest::with('cycle', 'orgnization')->find($this->id);
            $is_hackathon = $session && $session->cycle ? (strtolower($session->cycle->type) === 'hackathon') : false;

            $session_date = strtotime($this->date);
            $start = Carbon::parse($this->date);
            $end = $start->copy()->addMinutes($this->period ?? 60);
            $sessionDate = date('Y-m-d', $session_date);
            
            $sessionTime = date('h:i A', $session_date) . ' – ' . date('h:i A', $end->timestamp) . ' (AM/PM)';
            if ($mentorLocale == 'ar') {
                $sessionTime = str_replace('AM', 'صباحاً', $sessionTime);
                $sessionTime = str_replace('PM', 'مساءاً', $sessionTime);
            }

            $user = new User();
            $user->email = $mentor->email;
            $user->id = $mentor->id;

            $mentorSubject = $mentorLocale === 'ar' ? "جلسة إرشاد مع {$startup_owner_name}." : "Mentorship Session with {$startup_owner_name}.";
            $mentorMessage = [
                'subject' => $mentorSubject,
                'title' => $mentorSubject,
                'mentorName' => $mentor_name,
                'mentorGender' => $mentor->gender ?? '',
                'startupOwnerName' => $startup_owner_name,
                'startupName' => $startup_owner->company->name_en ?? '',
                'sessionDate' => $sessionDate,
                'sessionTime' => $sessionTime,
                'is_hackathon' => $is_hackathon,
                'duration' => $this->period ?? '60 min',
                'sessionType' => $this->onlineMeeting ? 'Online' : 'Offline',
                'meetingLink' => $this->onlineMeeting['data']['join_url'] ?? '',
                'meetingPass' => $this->onlineMeeting['data']['password'] ?? '',
                'location' => $session->orgnization->address ?? '',
                'mapLink' => $session->orgnization->google_map_url ?? '',
                'notes' => '',
                'attachments' => [],
                'status' => 'Yes',
                'action' => 'add',
                'user' => 'mentor',
                'lang' => $mentorLocale,
            ];
            $MSG = new MentorshipSessionMentor($mentorMessage);

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

            $userSubject = $startupLocale === 'ar' ? "جلسة إرشاد مع {$mentor_name}." : "Mentorship Session with {$mentor_name}.";
            $userMessage = [
                'subject' => $userSubject,
                'title' => $userSubject,
                'mentorName' => $mentor_name,
                'startupOwnerName' => $startup_owner_name,
                'startupName' => $startup_owner->company->name_en ?? '',
                'sessionDate' => $this->date,
                'duration' => $this->period ?? '60 min',
                'sessionType' => $this->onlineMeeting ? 'Online' : 'Offline',
                'meetingLink' => $this->onlineMeeting['data']['join_url'] ?? '',
                'meetingPass' => $this->onlineMeeting['data']['password'] ?? '',
                'location' => '',
                'notes' => '',
                'attachments' => [],
                'status' => 'Yes',
                'action' => 'add',
                'user' => 'user',
                'lang' => $startupLocale,
            ];
            $MSGUser = new MentorshipSession($userMessage);

            if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                try {
                    $user->notify($MSGUser);

                } catch (\Throwable $th) {
                    // throw $th;
                    Log::error('add Notification Error 2: ' . $th->getMessage());
                }
                $phoneNumber = $startup_owner->country_code_w_a . $startup_owner->whatsapp;
                $Massage = "Dear " . $startup_owner->name . ",
                This Is notification new mentorship session with '" . $startup_owner_name . "',
                Session will start: " . $this->date . ",
                Session Link: " . ($this->onlineMeeting['data']['join_url'] ?? '') . ",
                Thanks from " . env('APP_NAME');
                $result = $whatsAppService->sendMessage($phoneNumber, $Massage);
                Log::info('Message sent to user: ' . $phoneNumber . ' Result: ' . json_encode($result));
            }
        }

        if ($this->action == 'modifySession') {
            /// Notify Mentor
            $session = MentorshipRequest::with('orgnization', 'cycle', 'cycle.organization', 'user', 'user.company', 'mentor')->findOrFail($this->id);
            // Tie the dedupe claim below to this exact update -- a later
            // reschedule bumps updated_at again and is allowed to re-notify.
            $mentorUpdateVersion = $session->updated_at;
            $rName = User::findOrFail($session->mentor_id);
            $rName_usr = User::findOrFail($session->user_id);
            $user = new User();
            $user->email = $rName->email;
            $user->id = $rName->id;
            $rFullName = $rName->name;
            $rFullName_usr = $rName_usr->name;

            $mentorLocale = $rName->preferredLocale();
            $userLocale = $rName_usr->preferredLocale();

            $mentorSubject = $mentorLocale === 'ar'
                ? "تم تحديث جلسة الإرشاد – {$rFullName_usr}"
                : "Mentorship Session Updated – {$rFullName_usr}";

            $is_hackathon = $session->cycle ? (strtolower($session->cycle->type) === 'hackathon') : false;

            $session_date = strtotime($session->zoom_date);
            $start = Carbon::parse($session->zoom_date);
            $end = $start->copy()->addMinutes($session->period ?? 60);
            $sessionDate = date('Y-m-d', $session_date);
            
            $sessionTime = date('h:i A', $session_date) . ' – ' . date('h:i A', $end->timestamp) . ' (AM/PM)';
            if ($mentorLocale == 'ar') {
                $sessionTime = str_replace('AM', 'صباحاً', $sessionTime);
                $sessionTime = str_replace('PM', 'مساءاً', $sessionTime);
            }

            $mentorMessage =[
                'subject' => $mentorSubject,
                'title' => $mentorSubject,
                'mentorName' => $rFullName,
                'mentorGender' => $rName->gender ?? '',
                'startupOwnerName' => $rFullName_usr,
                'startupOwnerId' => $rName_usr->id,
                'startupName' => $session->user->company->name_en ?? '',
                'startupId' => $session->user->company->id ?? null,
                'sessionDate' => $sessionDate,
                'sessionTime' => $sessionTime,
                'is_hackathon' => $is_hackathon,
                'duration' => $session->period ?? '60 min',
                'sessionType' => $session->session_type ?? 'Online',
                'meetingLink' => $session->start_url ?? '',
                'meetingPass' => $session->zoom_passcode ?? '',
                'location' => $session->cycle->offline_location_address ?? '',
                'address' => $session->orgnization->address ?? '',
                'mapLink' => $session->cycle->offline_location_map ?? $session->orgnization->google_map_url ?? '',
                'notes' => $session->comment ?? '',
                'attachments' => $session->fileurl ? [['url' => env('APP_URL').'/dashboard/uploads/InviteMentor/'.$session->fileurl, 'name' => basename($session->fileurl), 'path' => $session->fileurl]] : [],
                'status' => $session->approved ?? '',
                'action' => 'modifySession',
                'user' => 'mentor',
                'lang' => $mentorLocale,
            ];
            $MSGM = new MentorshipSessionMentor($mentorMessage);
            if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                try {
                    // Claim this specific update -- a later reschedule bumps
                    // updated_at again, so this comparison lets it notify again.
                    $claimedMentorUpdateNotify = DB::table('mentorship_requests')->where('id', $session->id)
                        ->where(function ($q) use ($mentorUpdateVersion) {
                            $q->whereNull('mentor_update_notified_at')
                              ->orWhere('mentor_update_notified_at', '!=', $mentorUpdateVersion);
                        })
                        ->update(['mentor_update_notified_at' => $mentorUpdateVersion]);

                    if ($claimedMentorUpdateNotify) {
                        $rName->notify($MSGM);
                    }
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
            $userSubject = $userLocale === 'ar' ? "جلسة إرشاد مع {$rFullName}." : "Mentorship Session with {$rFullName}.";
            $userSessionTime = date('h:i A', $session_date) . ' – ' . date('h:i A', $end->timestamp) . ' (AM/PM)';
            if ($userLocale == 'ar') {
                $userSessionTime = str_replace('AM', 'صباحاً', $userSessionTime);
                $userSessionTime = str_replace('PM', 'مساءاً', $userSessionTime);
            }

            $message = [
                'subject' => $userSubject,
                'title' => __('messages.mentorship_session_subject:'.$session->user->company->name_en.'', [], $userLocale),
                'mentorName' => $rFullName,
                'startupOwnerName' => $rFullName_usr,
                'startupName' => $session->user->company->name_en ?? '',
                'sessionDate' => $sessionDate,
                'sessionTime' => $userSessionTime,
                'duration' => $session->period ?? '60 min',
                'sessionType' => $session->session_type ?? 'Online',
                'meetingLink' => $session->start_url ?? '',
                'meetingPass' => $session->zoom_passcode ?? '',
                'location' => $session->orgnization->address ?? '',
                'mapLink' => $session->orgnization->google_map_url ?? '',
                'notes' => $session->comment ?? '',
                'attachments' => $session->fileurl ? [['url' => env('APP_URL').'/dashboard/uploads/InviteMentor/'.$session->fileurl, 'name' => basename($session->fileurl), 'path' => $session->fileurl]] : [],
                'status' => $session->approved ?? '',
                'action' => 'modifySession',
                'user' => 'user',
                'lang' => $userLocale,
                // Raw values for the founder "session updated" email, which is
                // localized to the platform default language when it is built.
                'organizationId' => $session->cycle->organization_id ?? $session->organization_id,
                'startAt' => Carbon::parse($session->zoom_date)->format('Y-m-d H:i:s'),
                'periodMinutes' => $session->period,
                'joinUrl' => $session->join_url ?? '',
                'passcode' => $session->zoom_passcode ?? '',
                'offlineLocation' => $session->cycle->offline_location_address ?? '',
                'offlineMapLink' => $session->cycle->offline_location_map
                    ?? optional($session->cycle->organization ?? $session->orgnization)->google_map_url ?? '',
                'offlineAddress' => optional($session->cycle->organization ?? $session->orgnization)->address ?? '',
                'founderNotes' => $session->admin_comment ?: ($session->comment ?? ''),
                'startupNameEn' => $session->user->company->name_en ?? '',
                'startupNameAr' => $session->user->company->name_ar ?? '',
                'attachmentLinks' => $session->fileurl ? [[
                    'name' => basename($session->fileurl),
                    'url' => app(FileManagerService::class)->getUrl($session->fileurl, 'UsersDiskInviteMentor'),
                ]] : [],
            ];
            $MSG = new MentorshipSession($message);
            if (!in_array(env('APP_ENV', 'local'), ['local'])) {
                try {
                    // Claim this specific update -- a later reschedule bumps
                    // updated_at again, so this comparison lets it notify again.
                    // Uses the query builder: an Eloquent update() would itself
                    // bump updated_at and make a retry look like a new update.
                    $claimedFounderUpdateNotify = DB::table('mentorship_requests')->where('id', $session->id)
                        ->where(function ($q) use ($mentorUpdateVersion) {
                            $q->whereNull('founder_update_notified_at')
                              ->orWhere('founder_update_notified_at', '!=', $mentorUpdateVersion);
                        })
                        ->update(['founder_update_notified_at' => $mentorUpdateVersion]);

                    if ($claimedFounderUpdateNotify) {
                        try {
                            $rName_usr->notify($MSG);
                        } catch (\Throwable $th) {
                            DB::table('mentorship_requests')->where('id', $session->id)->update(['founder_update_notified_at' => null]);
                            throw $th;
                        }
                    }
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

            $mentorLocale = $mentor_user ? $mentor_user->preferredLocale() : 'en';
            $userLocale = $startup_user ? $startup_user->preferredLocale() : 'en';

            $date_Request = $session->zoom_date;
            $mentorFullName = $mentor_user->name;
            $startupFullName_user = $startup_user->name;
            $mentorMessage = [
                __("messages.mentorship_session_subject", [], $mentorLocale),
                __("messages.dear", ['name' => $mentorFullName], $mentorLocale),
                $this->stat == 'Yes'
                    ? __("messages.approved_mentor", ['other_party' => $startupFullName_user], $mentorLocale)
                    : __("messages.denied_message", ['other_party' => $startupFullName_user], $mentorLocale),
                "/dashboard",
                $this->stat == 'Yes'
                    ? __("messages.mentorship_session_action", ['action' => __('messages.Approved', [], $mentorLocale), 'name' => $startupFullName_user], $mentorLocale)
                    : __("messages.mentorship_session_action", ['action' => __('messages.Rejected', [], $mentorLocale), 'name' => $startupFullName_user], $mentorLocale),
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
                __("messages.mentorship_session_subject", [], $userLocale),
                __("messages.dear", ['name' => $startupFullName_user], $userLocale),
                $this->stat == 'Yes'
                    ? __("messages.approved_message", ['other_party' => $mentorFullName], $userLocale)
                    : __("messages.denied_message", ['other_party' => $mentorFullName], $userLocale),
                "/dashboard",
                $this->stat == 'Yes'
                    ? __("messages.mentorship_session_action", ['action' => __('messages.Approved', [], $userLocale), 'name' => $mentorFullName], $userLocale)
                    : __("messages.mentorship_session_action", ['action' => __('messages.Rejected', [], $userLocale), 'name' => $mentorFullName], $userLocale),
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
                        // Atomically claim the "notify founder of approval" step
                        // so a retry of this job never double-emails the founder.
                        $claimedApprovalNotify = MentorshipRequest::where('id', $session->id)
                            ->whereNull('approval_notified_at')
                            ->update(['approval_notified_at' => now()]);

                        if ($claimedApprovalNotify) {
                            $startup_user->notify(
                                new StartupNotifyMentorshipApprovalNotification(
                                    $userMessage,
                                    $session
                                )
                            );
                        }
                    }
                    else
                    {
                        // Atomically claim the "notify founder of decline" step
                        // so a retry of this job never double-emails the founder.
                        $claimedRejectionNotify = MentorshipRequest::where('id', $session->id)
                            ->whereNull('rejection_notified_at')
                            ->update(['rejection_notified_at' => now()]);

                        if ($claimedRejectionNotify) {
                            $startup_user->notify(
                                new StartupNotifyMentorshipRejectionNotification(
                                    $userMessage,
                                    $session
                                )
                            );
                        }
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
