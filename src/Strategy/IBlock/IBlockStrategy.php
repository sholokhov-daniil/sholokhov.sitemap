<?php

namespace Sholokhov\Sitemap\Strategy\IBlock;

use Bitrix\Main\Loader;
use Sholokhov\Sitemap\Entry;
use Sholokhov\Sitemap\Exception\SitemapException;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockItem;
use Sholokhov\Sitemap\Source\IBlock\SectionSource;
use Sholokhov\Sitemap\Strategy\StrategyInterface;

use Bitrix\Iblock\IblockTable;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class IBlockStrategy implements StrategyInterface
{
    /**
     * Конфигурация стратегии генерации карты сайта
     *
     * @var IBlockItem
     */
    protected readonly IBlockItem $settings;

    /**
     * ID сайта, для которого производится генерация
     *
     * @var string
     */
    protected readonly string $siteId;

    /**
     * Шаблон наименования файла sitemap, для инфоблока
     *
     * @var string
     */
    protected readonly string $filenameTemplate;

    /**
     * Информация по текущему инфоблоку
     *
     * @var array
     */
    protected readonly array $info;

    /**
     * Источник данных доступных разделов
     *
     * @var SectionSource|null
     */
    protected ?SectionSource $section = null;

    /**
     * @param string $siteId
     * @param IBlockItem $settings
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SitemapException
     * @throws SystemException
     */
    public function __construct(string $siteId, string $fileNameTemplate, IBlockItem $settings)
    {
        $this->settings = $settings;
        $this->siteId = $siteId;
        $this->filenameTemplate = $fileNameTemplate;
        $this->info = $this->getInfo();

        if (empty($this->info)) {
            throw new SitemapException("Iblock {$this->settings->id} not found");
        }

        if ($settings->active) {
            $this->section = new SectionSource(0, $this->settings, $siteId);
        }
    }

    /**
     * Возвращает доступную ссылку
     *
     * @return Entry|null
     */
    public function fetch(): ?Entry
    {
        return $this->section?->fetch();
    }

    /**
     * Возвращает наименование файла в который будет производиться запись ссылок
     *
     * @return string
     */
    public function getFileName(): string
    {
        return str_replace(
            ['#IBLOCK_ID#', '#IBLOCK_CODE#', '#IBLOCK_XML_ID#'],
            [],
            $this->filenameTemplate
        );
    }

    /**
     * Возвращает информацию по текущему инфоблоку
     *
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function getInfo(): array
    {
        if (!Loader::includeModule('iblock')) {
            throw new SitemapException("IBLOCK module is not installed");
        }

        $info =  IblockTable::getRow([
            'filter' => [
                '=ID' => $this->settings->id,
                'ACTIVE' => 'Y'
            ]
        ]);

        return $info ?: [];
    }
}