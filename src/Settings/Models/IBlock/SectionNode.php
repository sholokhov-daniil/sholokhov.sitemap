<?php

namespace Sholokhov\Sitemap\Settings\Models\IBlock;

/**
 * Настройки раздела инфоблока
 */
class SectionNode extends Node
{
    /**
     * В генерации участвуют вложенные разделы
     *
     * @var bool
     */
    public bool $loadSections = true;

    /**
     * В генерации участвуют вложенные элементы
     *
     * @var bool
     */
    public bool $loadElements = true;

    /**
     * Игнорируемые элементы
     *
     * @var Node[]
     */
    public array $ignoredElements = [];
}