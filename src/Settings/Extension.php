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
     * @param string $id Уникальный символьный код расширения
     * @param bool $active Активность расширения
     */
    public function __construct(
        public string $id,
        public bool $active,
    )
    {
    }
}