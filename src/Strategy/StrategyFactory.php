<?php

namespace Sholokhov\Sitemap\Strategy;

use Sholokhov\Sitemap\Exception\SitemapException;
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
        $iterator = self::buildIBlock($settings);
        // TODO: Добавить событие, для модификации

        return $iterator;
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
    public static function buildIBlock(SitemapSettings $settings): array
    {
        if (!$settings->iBlock->active) {
            return [];
        }

        return array_map(
            fn(IBlockItem $iBlockItem) => new IBlockStrategy($settings->siteId, $settings->iBlock->fileName, $iBlockItem),
            $settings->iBlock->items
        );
    }
}