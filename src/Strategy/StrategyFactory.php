<?php

namespace Sholokhov\Sitemap\Strategy;

use Sholokhov\Sitemap\Exception\SitemapException;
use Sholokhov\Sitemap\Rules\IBlock\IBlockPolicy;
use Sholokhov\Sitemap\Settings\File\FsSettings;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockItem;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockSettings;
use Sholokhov\Sitemap\Settings\Strategies;
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
     * @param Strategies $settings
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SitemapException
     * @throws SystemException
     */
    public static function create(string $siteId, Strategies $settings): array
    {
        $iterator = [];

        if ($settings->iBlock) {
            $iterator = self::createIBlock($siteId, $settings->iBlock);
        }

        if ($settings->fs) {
            $iterator = array_merge($iterator, self::createFs($siteId, $settings->fs));
        }

        // TODO: Добавить событие, для модификации

        return $iterator;
    }

    /**
     * Создает стратегии генерации данных на основе файловой системы
     *
     * @param FsSettings $settings
     * @return array|FsStrategy[]
     */
    public static function createFs(string $siteId, FsSettings $settings): array
    {
        if ($settings->active === false) {
            return [];
        }

        return [
            new FsStrategy($siteId, $settings),
        ];
    }

    /**
     * Создает стратегий генерации данных по инфоблокам
     *
     * @param IBlockSettings $settings
     * @return array
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws SitemapException
     */
    public static function createIBlock(string $siteId, IBlockSettings $settings): array
    {
        if ($settings->active === false) {
            return [];
        }

        $policy = new IBlockPolicy($settings);

        return array_map(
            fn(IBlockItem $iBlockItem) => new IBlockStrategy($siteId, $settings->fileName, $iBlockItem, $policy),
            $settings->items
        );
    }
}