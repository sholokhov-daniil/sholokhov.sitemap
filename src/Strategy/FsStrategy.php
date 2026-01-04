<?php

namespace Sholokhov\Sitemap\Strategy;

use Sholokhov\Sitemap\Entry;
use Sholokhov\Sitemap\Settings\File\FsSettings;
use Sholokhov\Sitemap\Source\FsSource;

use Bitrix\Main\IO\FileNotFoundException;
use Bitrix\Main\IO\InvalidPathException;

/**
 * Стратегия формирования доступных файлов и директорий, для добавления в карту сайта
 */
class FsStrategy implements StrategyInterface
{
    /**
     * Конфигурация стратегии генерации карты сайта
     *
     * @var FsSettings
     */
    protected readonly FsSettings $settings;

    /**
     * Источник данных добавляемых ссылок в карту сайта
     *
     * @var FsSource
     */
    protected readonly FsSource $source;

    /**
     * @param string $siteId ID сайта, для которого производится генерация
     * @param FsSettings $settings Конфигурация стратегии генерации карты сайта
     */
    public function __construct(string $siteId, FsSettings $settings)
    {
        $this->settings = $settings;
        $this->source = new FsSource($siteId, $this->settings->items);
    }

    /**
     * Доступная ссылка, для добавления в карту сайта
     *
     * @inheritDoc
     * @return Entry|null
     * @throws FileNotFoundException
     * @throws InvalidPathException
     */
    public function fetch(): ?Entry
    {
        if (!$this->settings->active) {
            return null;
        }

        return $this->source->fetch();
    }

    /**
     * Наименование файла в который производится генерация
     *
     * @inheritDoc
     * @return string
     */
    public function getFileName(): string
    {
        return $this->settings->fileName;
    }
}