<?php

namespace App\Services\Importer;

use App\Cycle;
use App\Organization;
use App\User;
use App\Mail\ImporterFounderCredentialsMail;
use App\Mail\ImporterFounderEnrolledMail;
use Illuminate\Support\Facades\Mail;

/**
 * Sends the two importer mailables AFTER the per-row DB transaction commits.
 * Captures any exception in a string so the orchestrator can record email_status = failed.
 */
class ImporterEmailDispatcher
{
    /**
     * @return array{status: string, reason: ?string}
     */
    public function dispatch(
        bool $wasCreated,
        User $user,
        Cycle $cycle,
        Organization $org,
        ?string $plainPassword
    ): array {
        try {
            $locale = $this->resolveLocale($user, $org);
            $payload = [
                'founder_name'   => trim(($user->first_name_en ?? '') . ' ' . ($user->last_name_en ?? '')),
                'program_name'   => $cycle->title,
                'program_type'   => $cycle->type,
                'start_date'     => $cycle->start,
                'end_date'       => $cycle->end,
                'organization'   => $locale === 'ar'
                    ? ($org->name_ar ?? $org->name_en)
                    : ($org->name_en ?? $org->name_ar),
                'email'          => $user->email,
                'password'       => $plainPassword,
                'locale'         => $locale,
            ];

            $mailable = $wasCreated
                ? new ImporterFounderCredentialsMail($payload)
                : new ImporterFounderEnrolledMail($payload);

            Mail::to($user->email)->queue($mailable);
            return ['status' => 'sent', 'reason' => null];
        } catch (\Throwable $e) {
            return ['status' => 'failed', 'reason' => $e->getMessage()];
        }
    }

    private function resolveLocale(User $user, Organization $org): string
    {
        if (!empty($user->locale)) {
            return $user->locale;
        }
        if (!empty($org->default_locale)) {
            return $org->default_locale;
        }
        return config('app.locale', 'en');
    }
}
