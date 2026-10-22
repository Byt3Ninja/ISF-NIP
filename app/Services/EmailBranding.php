<?php

namespace App\Services;

use App\Company;
use App\Cycle;
use App\Invitation;
use App\MentorshipRequest;
use App\MentorshipTask;
use App\Organization;
use App\ProgramTask;
use App\ServicesRequests;
use App\Settings;
use App\Step;
use App\TeamFormation;
use App\TodoList;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;

/**
 * The single rule deciding which brand (logo, name, footer links) an email
 * carries:
 *
 *  - an email tied to a program carries the organization running that program;
 *  - an email about one specific organization carries that organization;
 *  - everything else carries the platform brand.
 *
 * The recipient's own organization never decides the brand: a judge, mentor or
 * founder often belongs to no organization, or to a different one than the
 * program the email is about.
 *
 * The organization is resolved once per send from the notification or
 * mailable being sent (see begin()), and the email layout reads it from here,
 * so a new email is branded correctly without having to pass anything. An
 * email can state its branding explicitly by defining emailBranding(),
 * returning anything organizationFor() accepts, or null for the platform brand.
 */
class EmailBranding
{
    /**
     * Whether a send is in flight, and the organization it is branded with
     * (null = platform brand).
     */
    protected static bool $active = false;

    protected static ?Organization $organization = null;

    /**
     * Branding data already built during the in-flight send, keyed by
     * organization and locale (the organization name is locale dependent).
     */
    protected static array $built = [];

    /**
     * Array keys that carry a program or organization reference, in priority
     * order, and the model an id under that key refers to.
     */
    protected const CONTEXT_KEYS = [
        'organization'    => Organization::class,
        'organizationId'  => Organization::class,
        'organization_id' => Organization::class,
        'org'             => Organization::class,
        'program'         => Cycle::class,
        'program_id'      => Cycle::class,
        'cycle'           => Cycle::class,
        'cycle_id'        => Cycle::class,
        'step'            => Step::class,
        'step_id'         => Step::class,
        'stepId'          => Step::class,
        'service_request_id' => ServicesRequests::class,
    ];

    /**
     * Start branding the email that is about to be built and rendered.
     */
    public static function begin(object $mail): void
    {
        static::$organization = static::forMail($mail);
        static::$active = true;
        static::$built = [];
    }

    /**
     * The in-flight send is over: emails rendered afterwards (previews) fall
     * back to what they were given.
     */
    public static function end(): void
    {
        static::$organization = null;
        static::$active = false;
        static::$built = [];
    }

    public static function active(): bool
    {
        return static::$active;
    }

    /**
     * The organization branding the in-flight send, null for the platform.
     */
    public static function current(): ?Organization
    {
        return static::$organization;
    }

    /**
     * The organization an email (notification or mailable) is branded with.
     */
    public static function forMail(object $mail): ?Organization
    {
        if (method_exists($mail, 'emailBranding')) {
            return static::organizationFor($mail->emailBranding());
        }

        // Every property, whatever its visibility: what the email was built
        // from is what it is about.
        $properties = (fn () => get_object_vars($this))->call($mail);

        return static::organizationFor($properties);
    }

    /**
     * Resolve the organization behind a program or organization reference.
     *
     * Accepts an Organization, a program (Cycle), a Step, a startup (Company),
     * a mentorship request or task, a program task, a todo, a hackathon team,
     * an invitation, a service request, or an array of any of these (keys such as organizationId
     * or cycle_id may hold plain ids). Users are never resolved.
     */
    public static function organizationFor($context): ?Organization
    {
        return match (true) {
            $context instanceof Organization      => $context,
            $context instanceof Cycle             => $context->organization,
            $context instanceof Step              => $context->cycle?->organization,
            $context instanceof Company           => $context->cycledata?->organization,
            $context instanceof MentorshipRequest => $context->organization ?? $context->cycle?->organization,
            $context instanceof MentorshipTask    => static::organizationFor($context->mentorshipRequest)
                                                     ?? static::organizationFor($context->company),
            $context instanceof ProgramTask       => $context->program?->organization,
            $context instanceof TodoList          => $context->cycle?->organization,
            $context instanceof TeamFormation     => $context->cycle?->organization,
            $context instanceof Invitation        => $context->organization,
            // A service request is about the requesting startup's program.
            $context instanceof ServicesRequests  => static::organizationFor($context->user?->company),
            is_array($context)                    => static::organizationFromArray($context),
            default                               => null,
        };
    }

    /**
     * Known keys first (ids allowed there), then any other value that is a
     * program or organization reference itself (nested arrays included).
     */
    protected static function organizationFromArray(array $context): ?Organization
    {
        foreach (static::CONTEXT_KEYS as $key => $model) {
            $value = $context[$key] ?? null;

            if (is_numeric($value)) {
                $value = $model::find($value);
            }

            if ($organization = static::organizationFor($value)) {
                return $organization;
            }
        }

        foreach ($context as $key => $value) {
            if (array_key_exists($key, static::CONTEXT_KEYS) || !(is_object($value) || is_array($value))) {
                continue;
            }

            if ($organization = static::organizationFor($value)) {
                return $organization;
            }
        }

        return null;
    }

    /**
     * The email layout variables for an organization (null = platform brand).
     */
    public static function data(?Organization $organization): array
    {
        $key = ($organization?->id ?? 0) . ':' . app()->getLocale();

        if (static::$active && isset(static::$built[$key])) {
            return static::$built[$key];
        }

        $setting = static::settings();
        $defaultName = $setting ? ($setting->Platform_Name ?? config('app.name')) : config('app.name');

        // image_url falls back to a RELATIVE placeholder path when the
        // organization has no logo, which renders as a broken image in a mail
        // client. Only a real logo is passed, so the platform logo is used
        // instead (GV-8625).
        $logoUrl = filled($organization?->img_url) ? $organization->image_url : null;
        $logoPath = static::embeddableOrganizationLogo($organization);

        $data = [
            'org_name'      => $organization?->name ?? $defaultName,
            'org_logo_url'  => $logoUrl,
            'org_logo_path' => $logoPath,
            'org_address'   => $organization?->address ?? null,
            'org_website'   => $organization?->website_url ?? null,
            'org_linkedin'  => $organization?->linkedin_company_url ?? null,
            'org_twitter'   => $organization?->twitter_handle ?? null,
            'org_facebook'  => $organization?->facebook_page_url ?? null,
            'org_instagram' => $organization?->instagram_profile_url ?? null,
            'email_logo'    => static::logo($logoUrl, $logoPath, $setting),
        ];

        if (static::$active) {
            static::$built[$key] = $data;
        }

        return $data;
    }

    /**
     * The header logo: the organization logo when there is one, else the
     * platform logo. `path` is set when the logo can be embedded (CID).
     *
     * @return array{url: string|null, path: string|null}
     */
    public static function logo(?string $organizationLogoUrl, ?string $organizationLogoPath = null, ?Settings $setting = null): array
    {
        if (filled($organizationLogoUrl)) {
            return ['url' => $organizationLogoUrl, 'path' => $organizationLogoPath];
        }

        $setting ??= static::settings();

        if (blank($setting?->Logo)) {
            return ['url' => null, 'path' => null];
        }

        $path = public_path('images/logo/' . $setting->Logo);

        return [
            'url'  => asset('/images/logo/' . $setting->Logo),
            'path' => config('mail.embed_logos') && is_file($path) ? $path : null,
        ];
    }

    /**
     * Brand an email view. During a send the resolved branding overrides
     * whatever the email passed itself; outside a send (previews) the email's
     * own values are kept and only the header logo is completed.
     */
    public static function compose(View $view): void
    {
        if (static::$active) {
            $view->with(static::data(static::$organization));

            return;
        }

        $data = $view->getData();

        if (!isset($data['email_logo'])) {
            $view->with('email_logo', static::logo($data['org_logo_url'] ?? null, $data['org_logo_path'] ?? null));
        }
    }

    /**
     * Local file path of the organization logo, so the layout can embed it
     * (CID) when mail.embed_logos is on; null otherwise.
     */
    protected static function embeddableOrganizationLogo(?Organization $organization): ?string
    {
        if (!config('mail.embed_logos') || blank($organization?->img_url)) {
            return null;
        }

        try {
            $disk = Storage::disk('organizations');

            return $disk->exists($organization->img_url) ? $disk->path($organization->img_url) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected static function settings(): ?Settings
    {
        try {
            return Settings::first();
        } catch (\Throwable $e) {
            return null;
        }
    }
}
