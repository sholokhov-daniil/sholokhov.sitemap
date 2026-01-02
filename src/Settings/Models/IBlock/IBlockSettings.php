<?php

namespace Sholokhov\Sitemap\Settings\Models\IBlock;

use Sholokhov\Sitemap\Settings\AbstractGroup;

/**
 * Настройки импорта карты сайта на основе инфоблока
 */
class IBlockSettings extends AbstractGroup
{
    /**
     * Настройки инфоблоков
     *
     * @var IBlockItem[]
     */
    public array $items = [];
}