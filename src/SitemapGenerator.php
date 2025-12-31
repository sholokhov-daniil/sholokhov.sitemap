<?php

namespace Sholokhov\Sitemap;

use Sholokhov\Sitemap\Source\SourceInterface;
use Sholokhov\Sitemap\Modifier\ModifierInterface;
use Sholokhov\Sitemap\Validator\ValidatorInterface;

use Bitrix\Main\EventResult;
use Bitrix\Seo\Sitemap\File\Index;
use Bitrix\Seo\Sitemap\File\Runtime;

/**
 *  Генератор карты сайта.
 *  Генерация карты сайта происходит на основе передаваемыми данными от источников {@see SourceInterface}
 *
 *  Для переопределения ссылки можно воспользоваться модификатором {@see ModifierInterface}
 *  При появлении необходимости валидации URL (исключение из импорта) следует использовать валидаторы {@see ValidatorInterface}
 *
 *  Генератор предусматривает ряд событий:
 *  <li>
 *      beforeSitemapAddEntry - позволяет кастомизировать ссылку перед ее записью в карту сайта.
 *      Передаются параметры:
 *      <ul>
 *          <li>entry - Добавляемая запись в карту сайта {@see Entry}</li>
 *      </ul>
 *      Если нам необходимо исключить ссылку из генерации, то событие должно вернуть не положительный результат {@see EventResult}
 *  </li>
 *  <li>
 *      beforeSitemapFinishGenerateFile - позволяет добавить пользовательский ресурс в рамках генерации текущего файла.
 *      Передаются параметры:
 *      <ul>
 *          <li>
 *              context:
 *                  <ul>
 *                      <li>runtime - менеджер отвечающий за работу с файлом карты сайта {@see Runtime}</li>
 *                      <li>source - источник данных, для генерации файла карты сайта {@see SourceInterface}</li>
 *                  </ul>
 *          </li>
 *      </ul>
 *      Для передачи собственного источника данных в параметры нужно указать ключ 'source',
 *      а его значение должно соответствовать интерфейсу {@see SourceInterface}.
 *      Пример формата параметров: ['source' => {Свой источник}, 'context' => ['runtime' => '...', source => '...']].
 *  </li>
 */
class SitemapGenerator
{
    /**
     * Источники данных URL адресов, для включения в карту сайта
     *
     * @var SourceInterface[][]
     */
    private array $sources = [];

    /**
     * Модификаторы адресов
     *
     * @var ModifierInterface[]
     */
    private array $modifiers = [];

    /**
     * Валидаторы добавляемых адресов
     *
     * @var ValidatorInterface[]
     */
    private array $validators = [];

    /**
     * Записи необходимые добавить в индексный файл карты сайта
     *
     * @var array
     */
    private array $indexEntries = [];

    /**
     * Количество записей в файле карты сайта
     *
     * @var int
     */
    private int $countEntry = 0;


    private int $maxFileSize = 0;

    /**
     * Наименование индексной страницы карты сайта
     *
     * @var string
     */
    private string $indexFileName = 'sitemap.xml';

    private object $configuration;

    public function __construct(object $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Запустить генерацию карты сайта
     *
     * @return void
     */
    public function run(): void
    {
        $pid = '';
        $index = new Index($this->indexFileName, $this->configuration->toArray());

        foreach ($this->sources as $fileName => $iterator) {
            foreach ($iterator as $source) {
                $this->generate($pid, $fileName, $source);
            }
        }

        $index->createIndex($this->indexEntries);
    }

    /**
     * Добавить источник данных
     *
     * @param string $sitemapName
     * @param SourceInterface $source
     * @return $this
     */
    public function addSource(string $sitemapName, SourceInterface $source): self
    {
        $this->sources[$sitemapName][] = $source;
        return $this;
    }

    /**
     * Указать список источников данных.
     *
     * Все ранее добавленные источники удалятся
     *
     * @param SourceInterface[][] $sources
     * @return $this
     */
    public function setSources(array $sources): self
    {
        $this->sources = [];

        foreach ($sources as $filename => $iterator) {
            foreach ($iterator as $source) {
                $this->addSource($filename, $source);
            }
        }

        return $this;
    }

    /**
     * Добавить модификатор адреса
     *
     * @param ModifierInterface $modifier
     * @return $this
     */
    public function addModifier(ModifierInterface $modifier): self
    {
        $this->modifiers[] = $modifier;
        return $this;
    }

    /**
     * Указать список модификаторов адреса
     *
     * Все ранее добавленные модификаторы удалятся
     *
     * @param ModifierInterface[] $modifiers
     * @return $this
     */
    public function setModifiers(array $modifiers): self
    {
        $this->modifiers = [];
        array_walk($modifiers, $this->addModifier(...));

        return $this;
    }

    /**
     * Добавить способ валидации адреса
     *
     * @param ValidatorInterface $validator
     * @return $this
     */
    public function addValidator(ValidatorInterface $validator): self
    {
        $this->validators[] = $validator;
        return $this;
    }

    /**
     * Указать список валидаторов адреса
     *
     * Все ранее добавленные валидаторов удалятся
     *
     * @param ValidatorInterface[] $validators
     * @return $this
     */
    public function setValidators(array $validators): self
    {
        $this->validators = [];
        array_walk($validators, $this->addValidator(...));

        return $this;
    }

    /**
     * Наименование индексного файла карты сайта
     *
     * @param string $fileName
     * @return $this
     */
    public function setIndexFileName(string $fileName): self
    {
        $this->indexFileName = $fileName;
        return $this;
    }

    /**
     * Создание файла карты сайта
     *
     * @param string $pid
     * @param string $fileName
     * @param SourceInterface $source
     * @return void
     */
    private function generate(string $pid, string $fileName, SourceInterface $source): void
    {
        $runtime = new Runtime($pid, $fileName, $this->configuration->toArray());

        while ($entry = $source->fetch()) {
            $this->modify($entry);

            // TODO: Добавить событие, для модификации

            if ($this->validate($entry) === false) {
                continue;
            }

            $this->addEntry($entry, $runtime);
        }

        if ($runtime->isCurrentPartNotEmpty()) {
            $runtime->finish();
            $this->indexEntries[] = $runtime;
        } elseif ($runtime->isExists()) {
            $runtime->delete();
        }

        $this->countEntry = 0;
    }

    /**
     * Модифицировать адрес
     *
     * @param Entry $entry
     * @return void
     */
    private function modify(Entry $entry): void
    {
        foreach ($this->modifiers as $modifier) {
            $modifier->modify($entry);
        }
    }

    /**
     * Проверка корректности ссылки
     *
     * @param Entry $entry
     * @return bool
     */
    private function validate(Entry $entry): bool
    {
        foreach ($this->validators as $validator) {
            $isValid = $validator->validate($entry);
            if ($isValid === false) {
                // TODO: Добавить логирование
                return false;
            }
        }

        return true;
    }

    /**
     * Добавление ссылки в карту сайта
     *
     * @param Entry $entry
     * @param Runtime $runtime
     * @return void
     */
    private function addEntry(Entry $entry, Runtime $runtime): void
    {
        // TODO: Добавить проверку на дублирование

        if ($this->isSplitNeeded()) {
            $runtime->split();
            $this->countEntry = 0;
        }

        $data = [
            'XML_LOC' => $entry->url,
            'XML_LASTMOD' => $entry->lastModificationDate->format('c'),
        ];

        $runtime->addEntry($data);
        // TODO: Добавить запись в хранилище, что ссылка уже добавлена
        $this->countEntry++;
    }

    /**
     * Определение необходимости разделения файла карты сайта.
     *
     * @return bool
     */
    private function isSplitNeeded(): bool
    {
        return $this->maxFileSize > 0 && $this->countEntry > $this->maxFileSize;
    }
}