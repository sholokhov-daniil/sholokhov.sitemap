<?php

namespace Sholokhov\Sitemap\Source;

use Sholokhov\Sitemap\Entry;
use Sholokhov\Sitemap\Source\SourceInterface;

/**
 * Базовая реализация источника данных используемых при генерации карты сайта
 */
abstract class AbstractSource implements SourceInterface
{
    /**
     * Флаг отвечающий за сигнализирование, что источник уже запущена
     *
     * @var bool
     */
    private bool $running = false;

    /**
     * Загрузка данных, для выдачи.
     *
     * @return void
     *
     */
    abstract protected function load(): void;

    /**
     * Логика получения данных, для передачи в карту сайта.
     *
     * @return Entry|null
     *
     */
    abstract protected function logic(): Entry|null;

    /**
     * Получение ссылки, для записи
     *
     * @return Entry|null
     */
    final public function fetch(): Entry|null
    {
        if (!$this->running) {
            $this->load();
            $this->running = true;
        }

        return $this->logic();
    }
}