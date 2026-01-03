<?php

namespace Sholokhov\Sitemap\Settings\Models\IBlock;

use Sholokhov\Sitemap\Rules\NodeState;

/**
 * Описание узла дерева
 *
 * В качестве пункта может выступать элемент или раздел инфоблока
 */
class Node
{
    /**
     * @param int $id ID генерируемого пункта. В качестве ID выступает ID элемента, раздела или иного вида
     * @param bool $active Необходимость генерации
     */
    public function __construct(
        public readonly int $id,
        public int $state = NodeState::Inherit->value,
    )
    {
    }
}