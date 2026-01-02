<?php

use Sholokhov\Sitemap\Strategy;
use Bitrix\Main\Result;

/**
 * Стратегия генерации карты сайта
 */
interface StrategyInterface
{
    /**
     * Запустить работу
     *
     * @return Result
     */
    public function run(): Result;
}

