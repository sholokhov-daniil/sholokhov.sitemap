<?php

namespace Sholokhov\Sitemap\Rules\IBlock;

use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockItem;
use Sholokhov\Sitemap\Settings\Models\IBlock\SectionNode;

class SectionPolicy
{
    public function __construct(protected readonly IBlockItem $settings)
    {

    }

    public function getNode(int $id): SectionNode
    {
        return $this->settings->sections[$id] ?? new SectionNode($id);
    }
}