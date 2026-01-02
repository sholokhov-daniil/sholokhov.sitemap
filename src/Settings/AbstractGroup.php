<?php

namespace Sholokhov\Sitemap\Settings;

/**
 * Базовая настройка источника генерации карты сайта
 */
abstract class AbstractGroup
{
    /**
     * Активность генерации карты сайта сущности
     *
     * @var bool
     */
    public bool $active = false;

    /**
     * Наименование файла сохранения карты сайта
     *
     * @var string
     */
    public string $fileName = '';
}