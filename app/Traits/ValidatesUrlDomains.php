<?php

namespace App\Traits;

/**
 * Shared "URL allowed-domains" logic for dynamic form fields.
 *
 * A `url` field may carry an `allowed_domains` option: a comma-separated list
 * of domains the submitted URL must belong to (host equals the domain or is a
 * subdomain of it). Used by both the FormBuilder and the cycles/hackathon
 * application forms so the front-end and back-end enforce the same rule.
 */
trait ValidatesUrlDomains
{
    /**
     * Normalize the raw `allowed_domains` option into a clean list of lowercase
     * domains. Returns [] when no restriction is configured.
     *
     * @param  string|array|null  $raw
     * @return array<int,string>
     */
    protected function parseAllowedDomains($raw): array
    {
        if (is_array($raw)) {
            $parts = $raw;
        } else {
            $parts = explode(',', (string) ($raw ?? ''));
        }

        return collect($parts)
            ->map(fn ($d) => strtolower(trim((string) $d)))
            ->map(fn ($d) => preg_replace('/^https?:\/\//', '', $d)) // tolerate a pasted scheme
            ->map(fn ($d) => preg_replace('/^www\./', '', $d))
            ->map(fn ($d) => rtrim($d, '/'))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * Whether $url's host equals one of $allowedDomains or is a subdomain of it.
     * An empty allow-list means "no restriction" (always true).
     */
    protected function urlHostAllowed(?string $url, array $allowedDomains): bool
    {
        if (empty($allowedDomains)) {
            return true;
        }

        $host = strtolower((string) parse_url((string) $url, PHP_URL_HOST));
        if ($host === '') {
            return false;
        }
        $host = preg_replace('/^www\./', '', $host);

        foreach ($allowedDomains as $domain) {
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                return true;
            }
        }

        return false;
    }
}
