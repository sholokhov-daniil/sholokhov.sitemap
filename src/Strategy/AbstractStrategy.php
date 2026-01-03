<?php

namespace Sholokhov\Sitemap\Strategy;

use Sholokhov\Sitemap\Entry;

/**
 * Базовая реализация стратегии формирования данных, для генерации карты сайта
 */
abstract class AbstractStrategy implements StrategyInterface
{
    /**
     * Флаг отвечающий за сигнализирование, что стратегия уже запущена
     *
     * @var bool
     */
    private bool $running = false;

    /**
     * Стартовая конфигурация стратегии
     *
     * @return void
     */
    abstract protected function configuration(): void;

    /**
     * Логика получения данных, для передачи в карту сайта.
     *
     * @return Entry|null
     *
     */
    abstract protected function logic(): ?Entry;

    /**
     * Получение ссылки, для записи
     *
     * @return Entry|null
     */
    final public function fetch(): ?Entry
    {
        if (!$this->running) {
            $this->configuration();
            $this->running = true;
        }

        return $this->logic();
    }
}