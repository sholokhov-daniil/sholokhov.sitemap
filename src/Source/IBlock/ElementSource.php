<?php

namespace Sholokhov\Sitemap\Source\IBlock;

use Sholokhov\Sitemap\Entry;
use Sholokhov\Sitemap\Exception\SitemapException;
use Sholokhov\Sitemap\Rules\IBlock\IBlockPolicy;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockItem;
use Sholokhov\Sitemap\Source\SourceInterface;
use Sholokhov\Sitemap\Strategy\IBlock\Normalizer\ElementEntryNormalizer;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

/**
 * Источник элементов инфоблока, для добавления в sitemap
 */
class ElementSource implements SourceInterface
{
    protected ElementEntryNormalizer $normalizer;

    /**
     * Количество обрабатываемых элементов за один шаг
     */
    protected const int LIMIT = 300;

    /**
     * Индекс элемента на котором произвели остановку
     *
     * @var int
     */
    protected int $offset = 0;

    /**
     * Итератор отбираемых значений
     *
     * @var Result|null
     */
    protected Result|null $iterator = null;

    public function __construct(
        protected readonly int        $sectionId,
        protected readonly IBlockItem $settings,
        protected readonly string     $siteId,
        protected readonly IBlockPolicy $policy,
    )
    {
        if (Loader::includeModule('iblock') === false) {
            throw new SitemapException('IBLOCK module is not installed.');
        }

        $this->normalizer = new ElementEntryNormalizer($this->siteId);
    }

    /**
     * Возвращает элементы карты сайта
     *
     * @inheritDoc
     * @return Entry|null
     */
    public function fetch(): ?Entry
    {
        while (true) {
            if ($this->iterator === null) {
                $this->iterator = $this->query();
            }

            if ($this->iterator->getSelectedRowsCount() === 0) {
                return null;
            }

            $row = $this->iterator->fetch();

            if (!$row) {
                $this->offset += self::LIMIT;
                $this->iterator = null;
                continue;
            }

            if ($this->isDeny($row)) {
                continue;
            }

            return $this->normalizer->normalize($row);
        }
    }

    /**
     * Проверка запрета на добавление в карту сайта
     *
     * @param array $element
     * @return bool
     */
    protected function isDeny(array $element): bool
    {
        $sectionId = (int)($element['IBLOCK_SECTION_ID'] ?? 0);
        $leftMargin = (int)($element['LEFT_MARGIN'] ?? 0);
        $rightMargin = (int)($element['RIGHT_MARGIN'] ?? 0);

        return $this->policy->isDenyElement($sectionId, $leftMargin, $rightMargin);
    }

    /**
     * Выполнить запрос на получение доступных элементов
     *
     * @return Result
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function query(): Result
    {
        return ElementTable::getList([
            'select' => [
                'ID',
                'IBLOCK_ID',
                'TIMESTAMP_X',
                'IBLOCK_SECTION_ID',
                'XML_ID',
                'CODE',
                'RIGHT_MARGIN' => 'IBLOCK_SECTION.RIGHT_MARGIN',
                'LEFT_MARGIN' => 'IBLOCK_SECTION.LEFT_MARGIN',
                'DETAIL_PAGE_URL' => 'IBLOCK.DETAIL_PAGE_URL',
            ],
            'filter' => [
                '=IBLOCK_ID' => $this->settings->id,
                '=IBLOCK_SECTION_ID' => $this->sectionId,
                '=ACTIVE' => 'Y',
            ],
            'order' => [
                'ID' => 'ASC',
            ],
            'limit' => self::LIMIT,
            'offset' => $this->offset,
        ]);
    }
}