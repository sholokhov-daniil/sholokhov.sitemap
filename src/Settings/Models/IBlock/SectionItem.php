<?php

namespace Sholokhov\Sitemap\Settings\Models\IBlock;

/**
 * Настройка раздела инфоблока
 */
class SectionItem extends Item
{
    /**
     * В генерации участвуют вложенные разделы
     *
     * @var bool
     */
    public bool $loadSections = false;

    /**
     * В генерации участвуют вложенные элементы
     *
     * @var bool
     */
    public bool $loadElements = false;
}