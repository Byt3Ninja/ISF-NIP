<?php

namespace App\Support;

/**
 * For queued notifications and jobs sent under a NotificationClaim: the send happens on the
 * worker, after the claim was taken, so failed() must give the claim back or the
 * notification is never retried by the next run.
 */
trait ReleasesNotificationClaim
{
    public ?NotificationClaim $claim = null;

    public function withClaim(NotificationClaim $claim): static
    {
        $this->claim = $claim;

        return $this;
    }

    protected function releaseClaim(): void
    {
        $this->claim?->release();
    }
}
