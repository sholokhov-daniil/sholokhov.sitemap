<?php

namespace Sholokhov\Sitemap;

use DateTimeInterface;

/**
 * Подробная информация добавляемой ссылки в карту сайта
 *
 * Описание параметров:
 * - **additional** - Дополнительные данные генерируемой записи. Дополнительные данные могут использоваться в момент обработки системных событий.
 * - **url** - Польный адрес страницы
 * - **lastModificationDate** - Дата последней модификации страницы
 */
class Entry
{
    /**
     * @param string $url Польный адрес страницы
     * @param DateTimeInterface $lastModificationDate Дата последней модификации страницы
     * @param array $additional Дополнительные данные генерируемой записи
     */
    public function __construct(
        public string $url,
        public DateTimeInterface $lastModificationDate,
        public array $additional = []
    )
    {
    }
}