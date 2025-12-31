<?php

namespace Sholokhov\Sitemap\Modifier;

use Sholokhov\Sitemap\Entry;

/**
 * Производит модификацию адреса
 */
interface ModifierInterface
{
    /**
     * Выполнить модификацию адреса
     *
     * @param Entry $entry
     * @return void
     */
    public function modify(Entry $entry): void;
}