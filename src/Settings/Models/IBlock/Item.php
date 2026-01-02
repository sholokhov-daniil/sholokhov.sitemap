<?php

namespace Sholokhov\Sitemap\Settings\Models\IBlock;

/**
 * Базовое описание генерируемого элемента.
 *
 * В качестве пункта может выступать элемент или раздел инфоблока
 */
class Item
{
    /**
     * @param int $id ID генерируемого пункта. В качестве ID выступает ID элемента, раздела или иного вида
     * @param bool $active Необходимость генерации
     */
    public function __construct(
        public int $id,
        public bool $active,
    )
    {
    }
}
