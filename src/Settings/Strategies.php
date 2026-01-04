<?php

namespace Sholokhov\Sitemap\Settings;

use Sholokhov\Sitemap\Settings\File\FsSettings;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockSettings;

/**
 * Настройки стратегий генерации карты сайта
 */
class Strategies
{
    /**
     * @param IBlockSettings|null $iBlock Настройки генерации карты сайта из инфоблоков
     * @param FsSettings|null $fs Настройка генерации карты сайта из физической структуры каталога
     */
    public function __construct(
        public ?IBlockSettings $iBlock = null,
        public ?FsSettings $fs = null
    )
    {
    }
}