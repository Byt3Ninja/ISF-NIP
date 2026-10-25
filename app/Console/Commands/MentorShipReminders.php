<?php

namespace App\Console\Commands;

use log;
use App\User;
use App\ReminderLog;
use App\MentorshipRequest;
use App\MentorshipSchedule;
use Illuminate\Console\Command;
use App\Notifications\MentorshipRequestNotification;
use App\Services\MentorshipReminderService;

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
    protected $description = 'Mentorship Reminders to notify mentors and founders before sessions';

    /**
     * @var MentorshipReminderService
     */
    protected $reminderService;

    /**
     * Create a new command instance.
     *
     * @param MentorshipReminderService $reminderService
     */
    public function __construct(MentorshipReminderService $reminderService)
    {
        parent::__construct();
        $this->reminderService = $reminderService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $sentCount = $this->reminderService->sendUpcomingSessionReminders();
            $this->info("Mentorship reminders processed successfully. Reminders sent: {$sentCount}");
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to process mentorship reminders: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
