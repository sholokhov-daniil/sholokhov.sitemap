<?php

namespace Sholokhov\Sitemap\Source\IBlock;

use Sholokhov\Sitemap\Entry;
use Sholokhov\Sitemap\Exception\SitemapException;
use Sholokhov\Sitemap\Rules\IBlock\IBlockPolicy;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockItem;
use Sholokhov\Sitemap\Source\SourceInterface;
use Sholokhov\Sitemap\Strategy\IBlock\Normalizer\SectionEntryNormalizer;

use Bitrix\Main\Loader;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\ORM\Query\Result;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class SectionSource implements SourceInterface
{
    protected int $offset = 0;
    protected int $limit = 3;
    protected ?Result $sectionIterator = null;

    protected ?ElementSource $elementSource = null;

    protected ?int $leftMargin = null;
    protected ?int $rightMargin = null;
    protected SectionEntryNormalizer $normalizer;

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

        if ($this->sectionId > 0) {
            $this->loadMargins();
        }

        $this->normalizer = new SectionEntryNormalizer($this->siteId);
    }

    public function fetch(): ?Entry
    {
        while (true) {
            // 1. Сначала элементы текущего раздела
            if ($this->elementSource !== null) {
                if ($entry = $this->elementSource->fetch()) {
                    return $entry;
                }

                $this->elementSource = null;
            }

            // 2. Инициализация итератора
            if ($this->sectionIterator === null) {
                $this->sectionIterator = $this->getSectionIterator();
            }

            if ($this->sectionIterator->getSelectedRowsCount() === 0) {
                return null;
            }

            $section = $this->sectionIterator->fetch();

            // 3. Страница закончилась → следующая
            if ($section === false) {
                $this->offset += $this->limit;
                $this->sectionIterator = null;
                continue;
            }

            // 4. Раздел запрещён → пропускаем ВСЁ поддерево
            if ($this->isDeny($section)) {
                continue;
            }

            // 5. Раздел разрешён → сначала сам раздел
            $this->elementSource = new ElementSource(
                (int)$section['ID'],
                $this->settings,
                $this->siteId,
                $this->policy
            );

            return $this->normalizer->normalize($section);
        }
    }

    /**
     * Проверка запрета добавления раздела в карту сайта
     *
     * @param array $section
     * @return bool
     */
    protected function isDeny(array $section): bool
    {
        return $this->policy->isDenySection($section['LEFT_MARGIN'], $section['RIGHT_MARGIN']);
    }

    /**
     * Формирует итератор найденных разделов
     *
     * @return Result|null
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    protected function getSectionIterator(): ?Result
    {
        $filter = [
            '=IBLOCK_ID' => $this->settings->id,
            '=ACTIVE' => 'Y',
        ];

        if ($this->sectionId > 0) {
            $filter['>LEFT_MARGIN'] = $this->leftMargin;
            $filter['<RIGHT_MARGIN'] = $this->rightMargin;
        }

        return SectionTable::getList([
            'select' => [
                'ID',
                'NAME',
                'CODE',
                'XML_ID',
                'TIMESTAMP_X',
                'LEFT_MARGIN',
                'RIGHT_MARGIN',
                'IBLOCK_SECTION_ID',
                'SECTION_PAGE_URL' => 'IBLOCK.SECTION_PAGE_URL',
            ],
            'filter' => $filter,
            'order' => [
                'LEFT_MARGIN' => 'ASC',
            ],
            'limit' => $this->limit,
            'offset' => $this->offset,
        ]);
    }

    protected function loadMargins(): void
    {
        $section = SectionTable::getByPrimary($this->sectionId, [
            'select' => ['LEFT_MARGIN', 'RIGHT_MARGIN']
        ])->fetch();

        if (!$section) {
            throw new SitemapException("Section with ID {$this->sectionId} not found");
        }

        $this->leftMargin = (int)$section['LEFT_MARGIN'];
        $this->rightMargin = (int)$section['RIGHT_MARGIN'];
    }
}