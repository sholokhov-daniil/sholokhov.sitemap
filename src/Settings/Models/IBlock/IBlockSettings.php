<?php

namespace Sholokhov\Sitemap\Settings\Models\IBlock;

/**
 * Настройки импорта карты сайта на основе инфоблока
 */
class IBlockSettings
{
    /**
     * @param bool $active Активность генерации карты сайта из инфоблока
     * @param string $fileName Наименование файла сохранения карты сайта
     * @param IBlockItem[] $items Настройки инфоблоков
     */
    public function __construct(
        public bool $active,
        public string $fileName,
        public array $items = []
    )
    {
    }
}