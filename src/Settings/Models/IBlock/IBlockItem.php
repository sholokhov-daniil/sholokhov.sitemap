<?php

namespace Sholokhov\Sitemap\Settings\Models\IBlock;

/**
 * Настройки определенного инфоблока
 */
class IBlockItem
{
    /**
     * @param int $id ID инфоблока
     * @param int[] $executedSections Список разделов запрещенных участвовать в генерации карты сайта
     * @param int[][] $executedSectionElements Список элементов запрещенных участвовать в генерации карты сайта
     * @param bool $active Активность генерации
     */
    public function __construct(
        public readonly int $id,
        public array        $executedSections = [],
        public array        $executedSectionElements = [],
        public bool         $active = false,
    )
    {
    }
}