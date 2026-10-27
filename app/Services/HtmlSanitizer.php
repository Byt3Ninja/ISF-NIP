<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;
use HTMLPurifier_URIScheme_data;
use HTMLPurifier_URISchemeRegistry;

/**
 * Sanitizes user-supplied rich-text/HTML so that legitimate formatting markup
 * is preserved while any executable content (script tags, inline event
 * handlers, javascript: URIs, etc.) is stripped out.
 *
 * This guards against stored XSS when the content is later rendered raw
 * (e.g. {!! $content->content !!} in Blade templates).
 */
class HtmlSanitizer
{
    /**
     * Clean an HTML string, removing any JavaScript/XSS vectors while keeping
     * the safe HTML tags and text content.
     */
    public static function clean(?string $html): string
    {
        if ($html === null || trim($html) === '') {
            return '';
        }

        $config = HTMLPurifier_Config::createDefault();

        // Allow links (including mailto) but never javascript: or scriptable data: URIs.
        // `data` is limited by HTMLPurifier to real GIF/JPEG/PNG image payloads (it decodes
        // and inspects them), which is what the editors insert for pasted/inline pictures;
        // data:text/html and the like are still dropped.
        $config->set('HTML.Nofollow', true);
        $config->set('URI.AllowedSchemes', [
            'http'   => true,
            'https'  => true,
            'mailto' => true,
            'ftp'    => true,
            'tel'    => true,
            'data'   => true,
        ]);

        // Editors paste pictures as WebP too, which HTMLPurifier's data: scheme does not list.
        // The scheme still decodes the payload and checks its real type (exif_imagetype), so
        // only genuine WebP images pass; landing-page sections already store such images.
        $dataScheme = new HTMLPurifier_URIScheme_data();
        $dataScheme->allowed_types['image/webp'] = true;
        HTMLPurifier_URISchemeRegistry::instance()->register('data', $dataScheme);

        // Keep target="_blank" usable on links from the editor.
        $config->set('HTML.TargetBlank', true);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);

        // Training content embeds videos. Allow iframes, but only players of the two
        // video hosts in use; every other iframe is still removed.
        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^(https?:)?//(www\.youtube(-nocookie)?\.com/embed/|player\.vimeo\.com/video/)%');
        $config->set('HTML.DefinitionID', 'incubator-rich-text');
        $config->set('HTML.DefinitionRev', 2);

        // Use Laravel's storage path for the serializer cache; fall back to
        // disabling definition caching if the directory is not writable.
        $cacheDir = storage_path('framework/cache/htmlpurifier');
        if (! is_dir($cacheDir)) {
            @mkdir($cacheDir, 0775, true);
        }
        if (is_dir($cacheDir) && is_writable($cacheDir)) {
            $config->set('Cache.SerializerPath', $cacheDir);
        } else {
            $config->set('Cache.DefinitionImpl', null);
        }

        // Let the allowed video players keep their full-screen button.
        if ($definition = $config->maybeGetRawHTMLDefinition()) {
            $definition->addAttribute('iframe', 'allowfullscreen', 'Bool');
        }

        return (new HTMLPurifier($config))->purify($html);
    }
}
