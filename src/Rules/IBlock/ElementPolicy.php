<?php

namespace Sholokhov\Sitemap\Rules\IBlock;

use Sholokhov\Sitemap\Exception\SitemapException;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockItem;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockSettings;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

/**
 * Политика доступности элементов
 */
class ElementPolicy
{
    /**
     * Политика доступности по разделам
     *
     * @var SectionPolicy
     */
    protected SectionPolicy $sectionPolicy;

    /**
     * Список запрещенных элементов по разделам
     *
     * @var SectionMarginPolicy
     */
    protected SectionMarginPolicy $elementPolicy;

    /**
     * @param IBlockSettings $settings Настройки генерации карты сайта для инфоблоков
     * @param SectionPolicy $sectionPolicy Политика доступности разделов
     */
    public function __construct(IBlockSettings $settings, SectionPolicy $sectionPolicy)
    {
        $this->sectionPolicy = $sectionPolicy;
        $this->elementPolicy = $this->createPolicy($settings);
    }

    /**
     * Проверка запрета на использование элемента
     *
     * @param int $sectionId
     * @param int $leftMargin
     * @param int $rightMargin
     * @return bool
     */
    public function isDeny(int $sectionId, int $leftMargin, int $rightMargin): bool
    {
        return $this->sectionPolicy->isDeny($leftMargin, $sectionId)
            || $this->elementPolicy->isDeny($rightMargin, $sectionId);
    }

    /**
     * Создание политики запрещенных элементов по разделам
     *
     * @param IBlockSettings $settings
     * @return SectionMarginPolicy
     * @throws ArgumentException
     * @throws LoaderException
     * @throws ObjectPropertyException
     * @throws SystemException
     * @throws SitemapException
     */
    private function createPolicy(IBlockSettings $settings): SectionMarginPolicy
    {
        $ids = $this->getExecuted($settings);
        return new SectionMarginPolicy($ids);
    }

    /**
     * Возвращает список запрещенных разделов с элементами
     *
     * @param IBlockSettings $settings
     * @return array
     */
    private function getExecuted(IBlockSettings $settings): array
    {
        $ids = array_reduce(
            $settings->items,
            fn (array $carry, IBlockItem $item) => array_merge($carry, $item->executedElements),
            []
        );

        return array_unique($ids);
    }
}