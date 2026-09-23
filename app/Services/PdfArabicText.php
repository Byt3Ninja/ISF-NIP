<?php

namespace App\Services;

use ArPHP\I18N\Arabic;

/**
 * Prepares Arabic text for dompdf. (GV-9497)
 *
 * dompdf renders each code point standalone and left-to-right: it performs no
 * Arabic letter shaping (joining initial/medial/final forms) and no bidi
 * reordering, so Arabic comes out as disconnected letters in reverse order --
 * the "symbols" the ticket reports. The embedded font is not the problem;
 * DejaVu Sans carries the Arabic block.
 *
 * The fix is to hand dompdf text that is already shaped and already in visual
 * order, which is what Arabic::utf8Glyphs() produces.
 */
class PdfArabicText
{
    /** @var Arabic|null */
    protected static $arabic;

    /**
     * Shape + reorder a single string. Non-Arabic strings are returned as-is,
     * so this is safe to call on every cell of a mixed-language export.
     *
     * Shaping emits words in VISUAL order (last word first), which is only
     * correct while the line stays intact. If dompdf then wraps that line it
     * breaks it left-to-right, pushing what should be the first word onto the
     * bottom line -- "*Sub Criteria Total*" came out as "*Criteria Total* /
     * *Sub*". So the wrapping has to happen here, in logical order, before the
     * glyphs are shaped: utf8Glyphs() does exactly that when given a width.
     *
     * @param int|null $width Max characters per line, or null for no wrapping.
     */
    public static function shape( $text, $width = null ): string
    {
        $text = (string) $text;

        if( $text === '' || !static::hasArabic( $text ) ) { return $text; }

        if( static::$arabic === null ) { static::$arabic = new Arabic(); }

        $width = $width === null ? mb_strlen( $text, 'UTF-8' ) + 1 : max( 1, intval( $width ) );

        return static::$arabic->utf8Glyphs( $text, $width );
    }

    /**
     * Shape a string that must survive wrapping inside a narrow cell, returning
     * HTML with each wrapped line in its own block so dompdf renders the lines
     * as ar-php ordered them instead of re-wrapping the visual-order glyphs.
     *
     * Returns escaped HTML; callers must emit it unescaped.
     */
    public static function shapeBlock( $text, $width = 14 ): string
    {
        $text = (string) $text;

        if( $text === '' ) { return ''; }

        if( !static::hasArabic( $text ) ) { return e( $text ); }

        $lines = preg_split( '/\r\n|\r|\n/', static::shape( $text, $width ) );

        return implode( '', array_map(function( $line ) {
            return '<span style="display: block;">' . e( $line ) . '</span>';
        }, $lines ) );
    }

    /**
     * True when the string contains at least one character from the Arabic
     * block. Latin-only cells (startup names, timestamps) then skip shaping
     * entirely and keep their natural order.
     */
    public static function hasArabic( $text ): bool
    {
        return (bool) preg_match('/[\x{0600}-\x{06FF}\x{0750}-\x{077F}\x{08A0}-\x{08FF}\x{FB50}-\x{FDFF}\x{FE70}-\x{FEFF}]/u', (string) $text );
    }
}
