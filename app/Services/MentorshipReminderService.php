<?php

namespace App\Services;

use App\Enums\MentorshipSessionType;
use App\Enums\ReminderLogStatus;
use App\Enums\ReminderScheduleUnit;
use App\MentorshipRequest;
use App\MentorshipSchedule;
use App\Notifications\FounderUpcomingSessionReminderNotification;
use App\Notifications\MentorUpcomingSessionReminderNotification;
use App\Repositories\MentorshipRequestRepository;
use App\Repositories\ReminderLogRepository;
use App\Repositories\SettingRepository;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class MentorshipReminderService
{
    /**
     * @var MentorshipRequestRepository
     */
    protected $mentorshipRequestRepository;

    /**
     * @var ReminderLogRepository
     */
    protected $reminderLogRepository;

    /**
     * @var SettingRepository
     */
    protected $settingRepository;

    /**
     * @var FileManagerService
     */
    protected $fileManagerService;

    public function __construct(
        MentorshipRequestRepository $mentorshipRequestRepository,
        ReminderLogRepository $reminderLogRepository,
        SettingRepository $settingRepository,
        FileManagerService $fileManagerService
    ) {
        $this->mentorshipRequestRepository = $mentorshipRequestRepository;
        $this->reminderLogRepository = $reminderLogRepository;
        $this->settingRepository = $settingRepository;
        $this->fileManagerService = $fileManagerService;
    }

    /**
     * Process and send upcoming session reminders to mentors and founders.
     * Fully optimized against N+1 database queries and following SLAP principles.
     *
     * @return int Total number of reminders sent
     */
    public function sendUpcomingSessionReminders(): int
    {
        $sentCount = 0;
        $logRows = [];

        try {
            $locale = $this->resolveLocale();
            $platformName = $this->settingRepository->get('Platform_Name', 'GrowthLabs');

            $mentorshipRequests = $this->mentorshipRequestRepository->getPendingApprovedRequests();
            $cycleIds = $mentorshipRequests->pluck('cycle.id')->filter()->unique()->all();

            $sentReminderKeysMap = $this->reminderLogRepository->getSentReminderKeysForCycles($cycleIds);

            foreach ($mentorshipRequests as $request) {
                $sentCount += $this->processRequestReminders(
                    $request,
                    $locale,
                    $platformName,
                    $sentReminderKeysMap,
                    $logRows
                );
            }
        } catch (\Exception $e) {
            Log::error('Error processing mentorship reminders: '.$e->getMessage(), [
                'exception' => $e,
            ]);
            throw $e;
        } finally {
            // Written even when a later request fails, so reminders already
            // handed to the queue are not sent again on the next run.
            $this->reminderLogRepository->insertLogs($logRows);
        }

        return $sentCount;
    }

    /**
     * Process reminders for a single mentorship request across its active cycle schedules.
     *
     * When several schedules are due at once (e.g. the session was booked 3h
     * ahead with 24h and 6h reminders configured) only the closest one is sent;
     * the overtaken ones are logged as skipped so they never fire later.
     */
    protected function processRequestReminders(
        MentorshipRequest $request,
        string $locale,
        string $platformName,
        array &$sentReminderKeysMap,
        array &$logRows
    ): int {
        if (! $request->cycle || ! $request->mentor) {
            return 0;
        }

        $dueSchedules = $this->dueSchedules($request);
        if ($dueSchedules->isEmpty()) {
            return 0;
        }

        $schedule = $dueSchedules->shift();

        // Localized model names (organization, users) follow the app locale.
        $previousLocale = app()->getLocale();
        app()->setLocale($locale);
        $payload = $this->buildSessionPayload($request, $locale, $platformName);
        app()->setLocale($previousLocale);
        $sentCount = 0;

        if ($this->processMentorReminder($request, $schedule, $locale, $payload, $sentReminderKeysMap, $logRows)) {
            $sentCount++;
        }

        if ($request->user && $this->processFounderReminder($request, $schedule, $locale, $payload, $sentReminderKeysMap, $logRows)) {
            $sentCount++;
        }

        foreach ($dueSchedules as $overtaken) {
            $this->skipReminder($request->mentor_id, 'Mentor', $request, $overtaken, $sentReminderKeysMap, $logRows);

            if ($request->user) {
                $this->skipReminder($request->user_id, 'User', $request, $overtaken, $sentReminderKeysMap, $logRows);
            }
        }

        return $sentCount;
    }

    /**
     * Active schedules whose reminder window the session is in, closest first.
     */
    protected function dueSchedules(MentorshipRequest $request): Collection
    {
        return collect($request->cycle->mentorshipSchedules ?? [])
            ->filter(fn (MentorshipSchedule $schedule) => $this->isWithinReminderWindow(
                $request->zoom_date,
                $this->calculateScheduleMinutes($schedule)
            ))
            ->sortBy(fn (MentorshipSchedule $schedule) => $this->calculateScheduleMinutes($schedule))
            ->values();
    }

    /**
     * Check if a session starts in the future and falls within the schedule reminder threshold.
     */
    protected function isWithinReminderWindow(?Carbon $zoomDate, int $scheduleMinutes): bool
    {
        if (! $zoomDate) {
            return false;
        }

        $diffInMinutes = now()->diffInMinutes($zoomDate, false);

        return $diffInMinutes > 0 && $diffInMinutes <= $scheduleMinutes;
    }

    /**
     * Convert schedule value to minutes based on unit.
     */
    public function calculateScheduleMinutes(MentorshipSchedule $schedule): int
    {
        return ReminderScheduleUnit::fromStored($schedule->unit)->toMinutes((float) $schedule->value);
    }

    /**
     * Resolve default application locale with fallback.
     */
    protected function resolveLocale(): string
    {
        $locale = $this->settingRepository->get('Default_Language', 'en');

        return in_array($locale, ['en', 'ar'], true) ? $locale : 'en';
    }

    /**
     * Build standard session payload for notifications.
     */
    protected function buildSessionPayload(MentorshipRequest $request, string $locale, string $platformName): array
    {
        $founderName = $request->user ? $request->user->name : '-';
        $mentorName = $request->mentor ? $request->mentor->name : '-';

        ['date' => $date, 'startTime' => $startTime, 'endTime' => $endTime] = $this->sessionTimes($request, $locale, null);

        $duration = $request->period
            ? ($locale === 'ar' ? $request->period.' دقيقة' : $request->period.' mins')
            : '-';

        $attachments = [];
        if (! empty($request->fileurl)) {
            $attachments[] = [
                'url' => $this->fileManagerService->getUrl($request->fileurl, 'UsersDiskInviteMentor'),
                'name' => basename($request->fileurl),
                'path' => $request->fileurl,
            ];
        }

        $orgName = $request->organization ? $request->organization->name : $platformName;
        $company = $request->user ? $request->user->company : null;
        $isOnline = $request->session_type !== MentorshipSessionType::OFFLINE->value;

        return [
            'locale' => $locale,
            'mentorName' => $mentorName,
            'founderName' => $founderName,
            'founderProfileUrl' => $request->user ? route('clients.profile_view', $request->user->id) : null,
            'startupName' => $company ? $company->name : null,
            'startupProfileUrl' => $company ? route('company.profile_view', $company->id) : null,
            'date' => $date,
            'startTime' => $startTime,
            'endTime' => $endTime,
            'duration' => $duration,
            'sessionType' => $request->session_type ?? MentorshipSessionType::ONLINE->value,
            'isOnline' => $isOnline,
            'meetingLink' => $request->join_url ?: '-',
            'passcode' => $request->zoom_passcode ?: null,
            'locationName' => $request->organization->name ?? '-',
            'address' => $request->cycle->offline_location_address ?: ($request->organization->address ?? '-'),
            'locationLink' => $this->mapLink($request),
            // Staff notes (admin / org manager / subadmin) first, else the founder's booking comment.
            'notes' => $request->admin_comment ?: ($request->comment ?: null),
            'attachments' => $attachments,
            'orgName' => $orgName,
            // Only a real logo: the placeholder path renders broken in mail clients (GV-8625).
            'org_logo_url' => filled($request->organization?->img_url) ? $request->organization->image_url : null,
        ];
    }

    /**
     * Map link of the in-person venue: the program's own, else the organization's.
     * Only http(s) values are returned, since this ends up in an href.
     */
    protected function mapLink(MentorshipRequest $request): ?string
    {
        $link = $request->cycle->offline_location_map ?: ($request->organization->google_map_url ?? null);

        return ($link && preg_match('/^https?:\/\/\S+$/i', $link)) ? $link : null;
    }

    /**
     * Session date / start / end as the recipient should read them (GV-9566):
     * zoom_date is a real instant (PlatformDateTime cast), shown in the
     * recipient's own timezone, or the platform zone when they have none.
     */
    protected function sessionTimes(MentorshipRequest $request, string $locale, $recipient): array
    {
        $start = TimezoneService::forViewer($request->zoom_date, $recipient);

        if (! $start) {
            return ['date' => '-', 'startTime' => '-', 'endTime' => '-'];
        }

        $end = $start->copy()->addMinutes((int) $request->period);
        $clock = fn (Carbon $at) => $locale === 'ar'
            ? str_replace(['AM', 'PM'], ['صباحاً', 'مساءاً'], $at->format('h:i A'))
            : $at->format('h:i A');

        return ['date' => $start->format('Y-m-d'), 'startTime' => $clock($start), 'endTime' => $clock($end)];
    }

    /**
     * Send mentor reminder if not already sent.
     *
     * @param  array  $sentReminderKeysMap  Passed by reference to update in-memory map
     */
    protected function processMentorReminder(
        MentorshipRequest $request,
        MentorshipSchedule $schedule,
        string $locale,
        array $payload,
        array &$sentReminderKeysMap,
        array &$logRows
    ): bool {
        $lookupKey = $this->reminderKey($request->mentor_id, 'Mentor', $request, $schedule);

        if (isset($sentReminderKeysMap[$lookupKey])) {
            return false;
        }

        $payload['subject'] = __('email.mentor_session_reminder.subject', ['founder' => $payload['founderName']], $locale);
        $payload = array_merge($payload, $this->sessionTimes($request, $locale, $request->mentor));

        $request->mentor->notify(
            (new MentorUpcomingSessionReminderNotification($payload, $request->organization))->locale($locale)
        );

        $logRows[] = $this->logRow($request->mentor_id, 'Mentor', $request, $schedule, ReminderLogStatus::SENT);
        $sentReminderKeysMap[$lookupKey] = true;

        return true;
    }

    /**
     * Send founder reminder if not already sent.
     *
     * @param  array  $sentReminderKeysMap  Passed by reference to update in-memory map
     */
    protected function processFounderReminder(
        MentorshipRequest $request,
        MentorshipSchedule $schedule,
        string $locale,
        array $payload,
        array &$sentReminderKeysMap,
        array &$logRows
    ): bool {
        $lookupKey = $this->reminderKey($request->user_id, 'User', $request, $schedule);

        if (isset($sentReminderKeysMap[$lookupKey])) {
            return false;
        }

        $payload['subject'] = __('email.founder_session_reminder.subject', ['mentor' => $payload['mentorName']], $locale);
        $payload = array_merge($payload, $this->sessionTimes($request, $locale, $request->user));

        $request->user->notify(
            (new FounderUpcomingSessionReminderNotification($payload, $request->organization))->locale($locale)
        );

        $logRows[] = $this->logRow($request->user_id, 'User', $request, $schedule, ReminderLogStatus::SENT);

        $sentReminderKeysMap[$lookupKey] = true;

        return true;
    }

    /**
     * Mark a reminder that was overtaken by a closer schedule as handled.
     */
    protected function skipReminder(
        int $userId,
        string $type,
        MentorshipRequest $request,
        MentorshipSchedule $schedule,
        array &$sentReminderKeysMap,
        array &$logRows
    ): void {
        $lookupKey = $this->reminderKey($userId, $type, $request, $schedule);

        if (isset($sentReminderKeysMap[$lookupKey])) {
            return;
        }

        $logRows[] = $this->logRow($userId, $type, $request, $schedule, ReminderLogStatus::SKIPPED);
        $sentReminderKeysMap[$lookupKey] = true;
    }

    /**
     * Same key format as ReminderLogRepository::getSentReminderKeysForCycles().
     */
    protected function reminderKey(int $userId, string $type, MentorshipRequest $request, MentorshipSchedule $schedule): string
    {
        return "{$userId}_{$type}_{$request->cycle->id}_{$request->id}_{$schedule->id}";
    }

    protected function logRow(
        int $userId,
        string $type,
        MentorshipRequest $request,
        MentorshipSchedule $schedule,
        ReminderLogStatus $status
    ): array {
        return [
            'user_id' => $userId,
            'type' => $type,
            'cycle_id' => $request->cycle->id,
            'channel' => 'email',
            'status' => $status->value,
            'meta' => [
                'mentorship_request_id' => $request->id,
                'schedule_id' => $schedule->id,
            ],
        ];
    }
}
