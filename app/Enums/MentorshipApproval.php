<?php

namespace App\Enums;

/**
 * mentorship_requests.approved (NULL = still pending).
 */
enum MentorshipApproval: string
{
    case APPROVED = 'Yes';
    case REJECTED = 'No';
}
