<?php

namespace App\Translation;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use RuntimeException;

/**
 * Loads translations from resources/lang/{locale}/{overlay}/ on top of the normal files.
 *
 * Set LOCALIZATION_PATH=isf and `__('admin.Startups')` is read from
 * resources/lang/{locale}/isf/admin.php first. A key or file missing from the overlay
 * falls back to resources/lang/{locale}/admin.php, so overlay files hold only changed keys.
 * JSON strings use resources/lang/{locale}/{overlay}/{locale}.json the same way.
 * Vendor-namespaced translations are not overlaid.
 */
class OverlayFileLoader extends FileLoader
{
    /**
     * The overlay folder name, or null when no overlay is configured.
     *
     * @var string|null
     */
    protected $overlay;

    public function __construct(Filesystem $files, array|string $path, ?string $overlay = null)
    {
        parent::__construct($files, $path);

        // A folder name only: anything else (empty, "../x", "a/b") disables the overlay.
        $this->overlay = is_string($overlay) && preg_match('/^[A-Za-z0-9_-]+$/', $overlay) ? $overlay : null;
    }

    public function load($locale, $group, $namespace = null)
    {
        $lines = parent::load($locale, $group, $namespace);

        if (is_null($this->overlay) || ! (is_null($namespace) || $namespace === '*')) {
            return $lines;
        }

        if ($group === '*' && $namespace === '*') {
            return array_merge($lines, $this->loadOverlayJson($locale));
        }

        return array_replace_recursive($lines, $this->loadOverlayGroup($locale, $group));
    }

    public function overlay(): ?string
    {
        return $this->overlay;
    }

    protected function loadOverlayGroup($locale, $group): array
    {
        return collect($this->paths)->reduce(function ($output, $path) use ($locale, $group) {
            if ($this->files->exists($full = "{$path}/{$locale}/{$this->overlay}/{$group}.php")) {
                $output = array_replace_recursive($output, $this->files->getRequire($full));
            }

            return $output;
        }, []);
    }

    protected function loadOverlayJson($locale): array
    {
        return collect($this->paths)->reduce(function ($output, $path) use ($locale) {
            if ($this->files->exists($full = "{$path}/{$locale}/{$this->overlay}/{$locale}.json")) {
                $decoded = json_decode($this->files->get($full), true);

                if (is_null($decoded) || json_last_error() !== JSON_ERROR_NONE) {
                    throw new RuntimeException("Translation file [{$full}] contains an invalid JSON structure.");
                }

                $output = array_merge($output, $decoded);
            }

            return $output;
        }, []);
    }
}
