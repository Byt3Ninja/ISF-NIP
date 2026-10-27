<?php

namespace App\Traits;

use App\Organization;

/**
 * Picks the organization that brands a mentorship email (logo, name, SMTP):
 * the one running the session, not the recipient's own organization_id,
 * which is often null for founders and mentors in several organizations.
 */
trait ResolvesMentorshipOrganization
{
    /**
     * @param  mixed  $notifiable
     * @param  \App\MentorshipRequest|null  $session
     * @param  int|null  $organizationId
     */
    protected function sessionOrganization($notifiable, $session = null, $organizationId = null): ?Organization
    {
        return $session?->organization
            ?? $session?->cycle?->organization
            ?? ($organizationId ? Organization::find($organizationId) : null)
            ?? $notifiable->organization;
    }
}
