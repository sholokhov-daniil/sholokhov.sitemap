<?php

namespace Sholokhov\Sitemap\Source;

use CSeoUtils;
use DateTime;
use Generator;

use Sholokhov\Sitemap\Helpers\Http;
use Sholokhov\Sitemap\Entry;
use Sholokhov\Sitemap\Settings\File\FsEntity;

use Bitrix\Main\IO\File;
use Bitrix\Main\SiteTable;
use Bitrix\Main\IO\FileNotFoundException;
use Bitrix\Main\IO\InvalidPathException;

/**
 * Источник данных элементов файловой структуры, для включения в карту сайта
 */
class FsSource implements SourceInterface
{
    /**
     * Элементы файловой системы принимающие участие в формировании доступных ссылок
     *
     * @var FsEntity[]
     */
    protected readonly array $items;

    /**
     * Корневая директория сайта анализа файловой структуры
     *
     * @var string
     */
    protected readonly string $documentRoot;

    /**
     * ID файла, которого анализируется файловая структура
     *
     * @var string
     */
    protected readonly string $siteId;

    /**
     * Поток элементов файловой структуры
     *
     * @var Generator|null
     */
    protected ?Generator $entryStream = null;

    /**
     * @param string $siteId ID сайта принимающий участие в анализе
     * @param FsEntity[] $items Элементы файловой системы принимающие участие в анализе доступных ссылок
     */
    public function __construct(string $siteId, array $items)
    {
        $this->siteId = $siteId;
        $this->documentRoot = (string)SiteTable::getDocumentRoot($this->siteId);
        $this->items = $items;
    }

    /**
     * Возвращает доступную ссылку
     *
     * @return Entry|null
     * @throws FileNotFoundException
     * @throws InvalidPathException
     */
    public function fetch(): ?Entry
    {
        if (empty($this->items)) {
            return null;
        }

        if ($this->entryStream === null) {
            $this->entryStream = $this->iterateSources();
        }

        if (!$this->entryStream->valid()) {
            return null;
        }

        $current = $this->entryStream->current();
        $this->entryStream->next();

        return $current;
    }

    /**
     * Итерация по пользовательским источникам файловой системы
     *
     * @return Generator
     * @throws FileNotFoundException
     * @throws InvalidPathException
     */
    protected function iterateSources(): Generator
    {
        foreach ($this->items as $entity) {
            if (!$entity->active) {
                continue;
            }

            if ($entity->type === 'F') {
                yield $this->mapFileToEntry(
                    new File($entity->path, $this->siteId)
                );
            }

            if ($entity->type === 'D') {
                yield from $this->walkDirectory($entity->path);
            }
        }
    }

    /**
     * Ленивый рекурсивный обход директории
     *
     * @param string $directoryPath
     * @return Generator
     * @throws FileNotFoundException
     * @throws InvalidPathException
     */
    protected function walkDirectory(string $directoryPath): Generator
    {
        $list = CSeoUtils::getDirStructure(true, $this->siteId, $directoryPath);

        foreach ($list as $item) {
            if ($item['TYPE'] === 'F') {
                yield $this->mapFileToEntry(
                    new File($item['DATA']['PATH'], $this->siteId)
                );
                continue;
            }

            $nextDir = DIRECTORY_SEPARATOR . ltrim(
                    $item['DATA']['ABS_PATH'],
                    DIRECTORY_SEPARATOR
                );

            yield from $this->walkDirectory($nextDir);
        }
    }

    /**
     * Преобразование File → Entry
     */

    /**
     * Преобразование файла в элемент sitemap
     *
     * @param File $file
     * @return Entry
     * @throws FileNotFoundException
     * @throws InvalidPathException
     */
    protected function mapFileToEntry(File $file): Entry
    {
        $date = DateTime::createFromTimestamp(
            $file->getModificationTime()
        );

        $url = Http::getFileUrl($file, $this->documentRoot);

        return new Entry($url, $date);
    }
}
