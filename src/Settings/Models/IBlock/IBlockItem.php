<?php

namespace Sholokhov\Sitemap\Settings\Models\IBlock;

/**
 * Настройки определенного инфоблока
 */
class IBlockItem
{
    /**
     * @param int $id ID инфоблока
     * @param SectionItem[] $sections Список разделов принимающих участие в генерации (добавленные руками)
     * @param Item[] $elements Список элементов принимающих участие в генерации (добавленные руками)
     * @param bool $active Активность генерации
     * @param bool $loadSections В генерации участвуют разделы
     * @param bool $loadElements В генерации участвуют элементы
     */
    public function __construct(
        public readonly int $id,
        public array $sections,
        public array $elements,
        public bool $active = false,
        public bool $loadSections = false,
        public bool $loadElements = false,
    )
    {
    }
}