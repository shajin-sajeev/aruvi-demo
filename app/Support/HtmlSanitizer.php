<?php

namespace App\Support;

/**
 * Simple HTML sanitizer that strips dangerous tags/attributes
 * while preserving safe formatting tags from CMS content.
 *
 * Allowed tags: p, br, strong, b, em, i, u, ul, ol, li, h2, h3, h4, a, blockquote
 * Strips: script, style, iframe, object, embed, form, input, svg, on* attributes
 */
class HtmlSanitizer
{
    /** Safe tags allowed in CMS content */
    private const ALLOWED_TAGS = '<p><br><strong><b><em><i><u><ul><ol><li><h2><h3><h4><a><blockquote><span>';

    public static function clean(?string $html): string
    {
        if (empty($html)) {
            return '';
        }

        // Strip dangerous tags entirely (including their content for script/style)
        $html = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $html);
        $html = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $html);
        $html = preg_replace('/<iframe\b[^>]*>(.*?)<\/iframe>/is', '', $html);
        $html = preg_replace('/<(object|embed|form|input|svg|math)[^>]*>.*?<\/\1>/is', '', $html);
        $html = preg_replace('/<(object|embed|form|input|svg|math)[^>]*\/?>/is', '', $html);

        // Strip all tags not in the allowed list
        $html = strip_tags($html, self::ALLOWED_TAGS);

        // Strip dangerous attributes (on*, javascript:, data: URLs)
        $html = preg_replace('/\s+on\w+\s*=\s*["\'][^"\']*["\']/i', '', $html);
        $html = preg_replace('/\s+on\w+\s*=\s*[^\s>]*/i', '', $html);
        $html = preg_replace('/href\s*=\s*["\']javascript:[^"\']*["\']/i', 'href="#"', $html);
        $html = preg_replace('/href\s*=\s*["\']data:[^"\']*["\']/i', 'href="#"', $html);

        return trim($html);
    }
}
