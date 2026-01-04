<?php

namespace Sholokhov\Sitemap\Settings\File;

/**
 * Настройки генерации sitemap на основе физических файлов
 */
class FsSettings
{
    /**
     * @param bool $active Активность генерации
     * @param string $fileName Наименование файла в которую идет генерация
     * @param FsEntity[] $items Список включенных файлов\директорий
     */
    public function __construct(
        public bool $active = false,
        public string $fileName,
        public array $items = []
    )
    {
    }
}