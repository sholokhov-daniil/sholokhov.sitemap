<?php

namespace Sholokhov\Sitemap\Rules\IBlock;

use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockItem;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockSettings;

/**
 * Политика доступности разделов
 */
class SectionPolicy
{
    /**
     * Политика запрета на основе margin
     * @var SectionMarginPolicy
     */
    private SectionMarginPolicy $policy;

    /**
     * @param IBlockSettings $settings Настройки генерации карты сайта для инфоблоков
     */
    public function __construct(IBlockSettings $settings)
    {
        $this->policy = $this->createMarginPolicy($settings);
    }

    /**
     * Проверяем запрет на использование раздела
     *
     * @param int $leftMargin
     * @param int $rightMargin
     * @return bool
     */
    public function isDeny(int $leftMargin, int $rightMargin): bool
    {
        return $this->policy->isDeny($leftMargin, $rightMargin);
    }

    private function createMarginPolicy(IBlockSettings $settings): SectionMarginPolicy
    {
        $ids = $this->getDenyIds($settings);
        return new SectionMarginPolicy($ids);
    }

    /**
     * Формируем список запрещенных вложенностей разделов
     *
     * @param IBlockSettings $settings
     * @return array
     */
    private function getDenyIds(IBlockSettings $settings): array
    {
        $ids = array_reduce(
            $settings->items,
            fn(array $carry, IBlockItem $item) => array_merge($carry, $item->executedSections),
            []
        );

        return array_unique($ids);
    }
}