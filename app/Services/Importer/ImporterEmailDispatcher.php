<?php

namespace App\Services\Importer;

use App\Cycle;
use Carbon\Carbon;
use App\Organization;
use App\User;
use App\Mail\ImporterFounderCredentialsMail;
use App\Mail\ImporterFounderEnrolledMail;
use App\Settings;
use App\Traits\ConfiguresSmtp;
use Illuminate\Support\Facades\Mail;

/**
 * Sends the two importer mailables AFTER the per-row DB transaction commits.
 * Captures any exception in a string so the orchestrator can record email_status = failed.
 */
class ImporterEmailDispatcher
{
    use ConfiguresSmtp;

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
            $locale = $this->resolveLocale();
            $orgData = $this->buildEmailOrganizationData($org);

            $payload = array_merge([
                'founder_name'   => trim(($user->first_name_en ?? '') . ' ' . ($user->last_name_en ?? '')),
                'founder_gender' => strtolower(trim((string) $user->gender)),
                'program_name'   => $cycle->title,
                'program_type'   => $cycle->type,
                'start_date'     => $cycle->start ? Carbon::parse($cycle->start)->format('Y-m-d') : null,
                'end_date'       => $cycle->end ? Carbon::parse($cycle->end)->format('Y-m-d') : null,
                'organization'   => $locale === 'ar'
                    ? ($org->name_ar ?? $org->name_en)
                    : ($org->name_en ?? $org->name_ar),
                'email'          => $user->email,
                'locale'         => $locale,
                // A brand-new founder has never seen their (randomly
                // generated) password, so they get a one-time sign-in link
                // instead of the credentials themselves.
                'login_url'      => $wasCreated ? $user->generateMagicLoginUrl() : null,
            ], $orgData);

            $mailable = $wasCreated
                ? new ImporterFounderCredentialsMail($payload)
                : new ImporterFounderEnrolledMail($payload);

            Mail::to($user->email)->queue($mailable);
            return ['status' => 'sent', 'reason' => null];
        } catch (\Throwable $e) {
            return ['status' => 'failed', 'reason' => $e->getMessage()];
        }
    }

    /**
     * Follow the platform's default language (General Settings), matching
     * every other transactional email -- not the recipient's own locale.
     */
    private function resolveLocale(): string
    {
        $setting = Settings::first();

        return in_array($setting?->Default_Language, ['en', 'ar'], true) ? $setting->Default_Language : 'en';
    }
}
