<?php

namespace App\Enums;

/**
 * mentorship_requests.session_type
 */
enum MentorshipSessionType: string
{
    case ONLINE = 'Online';
    case OFFLINE = 'Offline';
}
