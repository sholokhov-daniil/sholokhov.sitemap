<?php

namespace Sholokhov\Sitemap\Helpers;

class ContentHelper
{
    /**
     * Получение каноничной ссылки в рамках текстового контента.
     *
     * @param string $content
     * @return string
     */
    public static function getCanonical(string $content): string
    {
        $pattern = '#<link[^>]+rel=["\']canonical["\'][^>]*href=["\']([^"\']+)["\']#i';
        preg_match($pattern, $content, $matches);

        return is_array($matches) ? end($matches) : '';
    }

}