<?php

namespace Sholokhov\Sitemap\Settings\Models\IBlock;

/**
 * Настройки определенного инфоблока
 */
class IBlockItem
{
    /**
     * @param int $id ID инфоблока
     * @param SectionNode[] $sections Список разделов принимающих участие в генерации (добавленные руками)
     * @param bool $active Активность генерации
     */
    public function __construct(
        public readonly int $id,
        public array $ignoredSections = [],
        public bool $active = false,
    )
    {
    }
}