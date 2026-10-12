<?php

namespace App\Services;

use App\Settings;
use Throwable;

/**
 * Platform Branding (GV-9673): the tenant's colors, read from settings row 1.
 *
 * Every saved value is optional. NULL means "use the theme default", so a
 * platform that never configured branding renders exactly the stock theme:
 *
 *   main_color            -> #7367f0 (Vuexy primary)
 *   button_text_color     -> #ffffff
 *   gradient start + end  -> both or neither. Neither = the theme gradient,
 *                            which the stock theme derives from the main color
 *                            (see admin/includes/branding-styles.blade.php).
 */
class PlatformBrandingService
{
    public const DEFAULT_MAIN = '#7367f0';
    public const DEFAULT_BUTTON_TEXT = '#ffffff';

    private const SETTINGS_BINDING = 'platform-branding.settings';

    /** Form field (without the "branding_" prefix) => settings column. */
    public const FIELDS = [
        'gradient_start' => 'gradient_start_color',
        'gradient_end' => 'gradient_end_color',
        'main_color' => 'main_color',
        'button_text_color' => 'button_text_color',
    ];

    /**
     * "#ABC", "abc", " #AABBCC " -> "#aabbcc". Anything else -> null.
     */
    public static function normalize($value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $hex = ltrim(trim($value), '#');
        if (preg_match('/^[0-9a-fA-F]{3}$/', $hex)) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        return preg_match('/^[0-9a-fA-F]{6}$/', $hex) ? '#' . strtolower($hex) : null;
    }

    /**
     * The saved values keyed by form field; null where nothing is saved.
     */
    public static function saved(?Settings $settings = null): array
    {
        $settings = $settings ?? self::settings();

        $saved = [];
        foreach (self::FIELDS as $field => $column) {
            $saved[$field] = self::normalize($settings?->{$column});
        }

        return $saved;
    }

    /**
     * The colors to render. A half-saved gradient (one end only) cannot be
     * produced by the form, but is treated as "no custom gradient" all the same.
     */
    public static function palette(?Settings $settings = null): array
    {
        $saved = self::saved($settings);

        $main = $saved['main_color'] ?? self::DEFAULT_MAIN;
        $hasGradient = $saved['gradient_start'] !== null && $saved['gradient_end'] !== null;
        $start = $hasGradient ? $saved['gradient_start'] : $main;
        $end = $hasGradient ? $saved['gradient_end'] : $main;

        return [
            'main' => $main,
            'main_rgb' => self::rgb($main),
            'button_text' => $saved['button_text_color'] ?? self::DEFAULT_BUTTON_TEXT,
            'has_gradient' => $hasGradient,
            'gradient_start' => $start,
            'gradient_start_rgb' => self::rgb($start),
            'gradient_end' => $end,
            'gradient_end_rgb' => self::rgb($end),
        ];
    }

    /**
     * Fallback colors for a program (cycle) that has none of its own (GV-9673: programs keep
     * their own colors, platform branding is only the default). The program hero paints its
     * start/end as radial blobs at 8% alpha ("14"); the theme gradient's end is the main color
     * mixed 50% with white, as in .bg-gradient-primary.
     */
    public static function programDefaults(?Settings $settings = null): array
    {
        $palette = self::palette($settings);
        $end = $palette['has_gradient'] ? $palette['gradient_end'] : self::mixWithWhite($palette['main'], 0.5);

        return [
            'start_color' => $palette['gradient_start'] . '14',
            'end_color' => $end . '14',
            'main_color' => $palette['main'],
        ];
    }

    /**
     * "115, 103, 240" for any "#rgb" / "#rrggbb" / "#rrggbbaa" (alpha ignored), else null.
     * Program colors may carry alpha, so this is wider than normalize().
     */
    public static function rgbOf($color): ?string
    {
        $hex = is_string($color) ? ltrim(trim($color), '#') : '';
        if (preg_match('/^[0-9a-fA-F]{8}$/', $hex)) {
            $hex = substr($hex, 0, 6);
        }
        $hex = self::normalize($hex);

        return $hex ? self::rgb($hex) : null;
    }

    private static function mixWithWhite(string $hex, float $weight): string
    {
        $channels = array_map(
            fn ($c) => (int) round($c * $weight + 255 * (1 - $weight)),
            sscanf($hex, '#%02x%02x%02x')
        );

        return vsprintf('#%02x%02x%02x', $channels);
    }

    /** "#7367f0" -> "115, 103, 240" (the format Bootstrap's --*-rgb vars use). */
    private static function rgb(string $hex): string
    {
        return implode(', ', sscanf($hex, '#%02x%02x%02x'));
    }

    /**
     * Read once per request / queued job (a scoped binding): the Cycle color accessors
     * call this for every program without its own colors.
     *
     * Error pages render this too, including the 500 page while the database
     * is down: a failed read must fall back to the defaults, never re-throw.
     */
    private static function settings(): ?Settings
    {
        if (!app()->bound(self::SETTINGS_BINDING)) {
            app()->scoped(self::SETTINGS_BINDING, function () {
                try {
                    return Settings::find(1);
                } catch (Throwable $e) {
                    return null;
                }
            });
        }

        return app(self::SETTINGS_BINDING);
    }
}
