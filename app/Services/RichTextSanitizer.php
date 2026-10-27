<?php

namespace App\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

/**
 * Cleans HTML coming from the Quill rich-text editor so it can be stored and
 * rendered as-is (e.g. in emails): only the formatting the editor toolbar can
 * produce survives, everything else (scripts, event handlers, javascript:
 * links, unknown tags/styles) is stripped.
 */
class RichTextSanitizer
{
    // Quill puts color / background styles on whichever inline element holds the text
    // (e.g. <strong style="..."> for bold + colored), not only on <span>.
    private const ALLOWED_HTML = 'p,br,strong[style],b[style],em[style],i[style],u[style],s[style],h1,h2,h3,ol,ul,li,span[style],a[href|target|style]';

    private const ALLOWED_CSS = ['color', 'background-color'];

    private const ALLOWED_SCHEMES = ['http' => true, 'https' => true, 'mailto' => true];

    /** Icons the invitation editor prefixes uploaded-file links with. */
    private const ATTACHMENT_ICONS = ['📎', '🖼️', '🖼'];

    private ?HTMLPurifier $purifier = null;

    /**
     * Sanitized HTML, or null when nothing readable is left.
     */
    public function clean(?string $html): ?string
    {
        if (is_null($html) || trim($html) === '') {
            return null;
        }

        $clean = trim($this->purifier()->purify($html));

        return $this->isBlank($clean) ? null : $clean;
    }

    /**
     * Same as clean(), but also drops the uploaded-file links (📎 / 🖼️ lines)
     * the invitation editor inserts: those files are sent as real email
     * attachments, so they must not be repeated in the message body.
     */
    public function cleanWithoutAttachmentLinks(?string $html): ?string
    {
        $clean = $this->clean($html);

        if (is_null($clean)) {
            return null;
        }

        $icons = implode('|', array_map(fn ($icon) => preg_quote($icon, '/'), self::ATTACHMENT_ICONS));
        $clean = preg_replace('/<a\b[^>]*>\s*(?:'.$icons.')[^<]*<\/a>/u', '', $clean);
        $clean = preg_replace('/<p>(?:\s|&nbsp;|<br\s*\/?>)*<\/p>/u', '', $clean);
        $clean = trim($clean);

        return $this->isBlank($clean) ? null : $clean;
    }

    private function isBlank(string $html): bool
    {
        return trim(html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8'), " \t\n\r\0\x0B\u{00A0}") === '';
    }

    private function purifier(): HTMLPurifier
    {
        if ($this->purifier) {
            return $this->purifier;
        }

        $config = HTMLPurifier_Config::createDefault();
        $config->set('HTML.Allowed', self::ALLOWED_HTML);
        $config->set('CSS.AllowedProperties', self::ALLOWED_CSS);
        $config->set('URI.AllowedSchemes', self::ALLOWED_SCHEMES);
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('HTML.TargetNoopener', true);
        $config->set('Cache.DefinitionImpl', null);

        return $this->purifier = new HTMLPurifier($config);
    }
}
