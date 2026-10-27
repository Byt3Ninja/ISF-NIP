<?php

namespace App\Enums;

/**
 * reminder_logs.status
 */
enum ReminderLogStatus: string
{
    case SENT = 'sent';
    case FAILED = 'failed';
    case SKIPPED = 'skipped';
}
