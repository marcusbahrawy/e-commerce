<?php

declare(strict_types=1);

namespace App\Support;

final class Slug
{
    public static function from(string $title): string
    {
        $slug = mb_strtolower($title, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9\s\-]/u', '', $slug);
        $slug = preg_replace('/[\s\-]+/', '-', $slug);
        return trim($slug, '-');
    }
}
