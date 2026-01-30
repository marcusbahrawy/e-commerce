<?php

declare(strict_types=1);

namespace App\Support;

final class Html
{
    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Sanitize HTML for CMS/product description (whitelist).
     * Allowed: p, br, ul, ol, li, strong, em, a(href), h2, h3, table, th, td, tr, thead, tbody
     */
    public static function sanitize(string $html): string
    {
        $allowed = [
            'p' => [], 'br' => [], 'ul' => [], 'ol' => [], 'li' => [],
            'strong' => [], 'em' => [], 'b' => [], 'i' => [],
            'a' => ['href' => true],
            'h2' => [], 'h3' => [], 'h4' => [],
            'table' => [], 'thead' => [], 'tbody' => [], 'tr' => [], 'th' => [], 'td' => [],
        ];
        return strip_tags($html, array_keys($allowed));
    }
}
