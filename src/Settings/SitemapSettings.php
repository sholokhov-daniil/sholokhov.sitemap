<?php

namespace Sholokhov\Sitemap\Settings;

use Sholokhov\Sitemap\Modifier\ModifierInterface;
use Sholokhov\Sitemap\Settings\File\FsSettings;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockSettings;
use Sholokhov\Sitemap\Validator\ValidatorInterface;

/**
 * Структура настроек генерации карты сайта
 */
class SitemapSettings
{
    /**
     * @param bool $active Активность генератора карты сайта
     * @param string $fileName Наименование индексного файла
     * @param string $siteId Идентификатор сайта, которому принадлежат настройки
     * @param ModifierInterface[] $modifiers Модификаторы добавляемой ссылки в карту сайта
     * @param ValidatorInterface[] $validators Валидаторы проверяющие возможность добавления ссылки в карту сайта
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
        public ?IBlockSettings $iBlock = null,
        public ?FsSettings $file = null
    )
    {
    }
}