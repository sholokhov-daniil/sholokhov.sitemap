<?php

namespace Sholokhov\Sitemap\Source\IBlock;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use Sholokhov\Sitemap\Entry;
use Sholokhov\Sitemap\Exception\SitemapException;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockItem;
use Sholokhov\Sitemap\Source\SourceInterface;
use Sholokhov\Sitemap\Strategy\IBlock\Normalizer\ElementEntryNormalizer;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Result;

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
        protected readonly int $sectionId,
        protected readonly IBlockItem $settings,
        protected readonly string $siteId
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
     */
    public function fetch(): Entry|null
    {
        if ($this->iterator === null) {
            $this->iterator = $this->query();
        }

        if ($row = $this->iterator->fetch()) {
            return $this->normalizer->normalize($row);
        }

        // текущая страница закончилась
        $this->offset += self::LIMIT;
        $this->iterator = null;

        // пробуем следующую страницу
        $this->iterator = $this->query();

        if ($this->iterator && ($row = $this->iterator->fetch())) {
            return $this->normalizer->normalize($row);
        }

        return null;
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