<?php

namespace Sholokhov\Sitemap\Settings;

use Sholokhov\Sitemap\Settings\File\FsSettings;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockSettings;

/**
 * Структура настроек генерации карты сайта
 */
class SitemapSettings
{
    /**
     * @param bool $active Активность генератора карты сайта
     * @param string $fileName Наименование индексного файла
     * @param string $siteId Идентификатор сайта, которому принадлежат настройки
     * @param Extension[] $modifiers Модификаторы добавляемой ссылки в карту сайта
     * @param Extension[] $validators Валидаторы проверяющие возможность добавления ссылки в карту сайта
     * @param int $maxFileSize Максимальное количество записей в рамках одного файла
     * @param IBlockSettings|null $iBlock Настройки генерации карты сайта из инфоблоков
     * @param FsSettings|null $file Настройка генерации карты сайта из физической структуры каталога
     */
    public function __construct(
        public bool $active,
        public string $fileName,
        public string $siteId,
        public int $maxFileSize,
        public array $modifiers = [],
        public array $validators = [],
        public ?Strategies $strategy = null,
    )
    {
    }
}