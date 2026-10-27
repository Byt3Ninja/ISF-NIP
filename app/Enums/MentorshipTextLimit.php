<?php

namespace App\Enums;

/**
 * Max characters of free-text fields on mentorship_requests
 * (comment / admin_comment are varchar(255)).
 */
enum MentorshipTextLimit: int
{
    case NOTES = 250;
}
