<?php

namespace Sholokhov\Sitemap\Settings;

/**
 * Настройки расширения функционала генерации
 *
 * Расширениями могут быть различные модификаторы, нормализаторы и т.п.
 */
class Extension
{
    /**
     * @param bool $active Активность расширения
     * @param string $code Уникальный символьный код расширения
     */
    public function __construct(
        public bool $active,
        public string $code,
    )
    {
    }
}