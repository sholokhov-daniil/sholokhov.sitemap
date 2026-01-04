<?php

namespace Sholokhov\Sitemap\Strategy;

use Sholokhov\Sitemap\Exception\SitemapException;
use Sholokhov\Sitemap\Rules\IBlock\IBlockPolicy;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockItem;
use Sholokhov\Sitemap\Settings\SitemapSettings;
use Sholokhov\Sitemap\Strategy\IBlock\IBlockStrategy;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

/**
 * Фабрика создания стратегий формирования данных
 */
class StrategyFactory
{
    /**
     * Создание всех доступных стратегий генерации данных
     *
     * @param SitemapSettings $settings
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SitemapException
     * @throws SystemException
     */
    public static function create(SitemapSettings $settings): array
    {
        $iterator = array_merge(
            self::createIBlock($settings),
            self::createFs($settings)
        );
        // TODO: Добавить событие, для модификации

        return $iterator;
    }

    /**
     * Создает стратегии генерации данных на основе файловой системы
     *
     * @param SitemapSettings $settings
     * @return array|FsStrategy[]
     */
    public static function createFs(SitemapSettings $settings): array
    {
        if (!$settings->file || !$settings->file->active) {
            return [];
        }

        return [
            new FsStrategy($settings->siteId, $settings->file),
        ];
    }

    /**
     * Создает стратегий генерации данных по инфоблокам
     *
     * @param SitemapSettings $settings
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws SitemapException
     */
    public static function createIBlock(SitemapSettings $settings): array
    {
        if (!$settings->iBlock || !$settings->iBlock->active) {
            return [];
        }

        $policy = new IBlockPolicy($settings->iBlock);

        return array_map(
            fn(IBlockItem $iBlockItem) => new IBlockStrategy($settings->siteId, $settings->iBlock->fileName, $iBlockItem, $policy),
            $settings->iBlock->items
        );
    }
}