<?php

namespace Sholokhov\Sitemap\Strategy;

use Sholokhov\Sitemap\Entry;

/**
 * Стратегия формирования ссылок, для генерации карты сайта
 */
interface StrategyInterface
{
    /**
     * Запустить работу
     *
     * @return Entry|null
     */
    public function fetch(): Entry|null;
}

