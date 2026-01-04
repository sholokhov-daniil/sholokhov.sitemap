<?php

namespace Sholokhov\Sitemap;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use CBXPunycode;
use Sholokhov\Sitemap\Settings\SitemapSettings;
use Sholokhov\Sitemap\Source\SourceInterface;
use Sholokhov\Sitemap\Modifier\ModifierInterface;
use Sholokhov\Sitemap\Strategy\StrategyFactory;
use Sholokhov\Sitemap\Strategy\StrategyInterface;
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
     * Модификаторы адресов
     *
     * @var ModifierInterface[]
     */
    private array $modifiers = [];

    /**
     * Стратегии формирования данных, для sitemap
     *
     * @var StrategyInterface[]
     */
    private array $strategies = [];

    /**
     * Валидаторы добавляемых адресов
     *
     * @var ValidatorInterface[]
     */
    private array $validators = [];

    /**
     * Записи необходимые добавить в индексный файл карты сайта
     *
     * @var Runtime[]
     */
    private array $runtimes = [];

    /**
     * Количество записей в файле карты сайта
     *
     * @var int
     */
    private int $countEntry = 0;

    /**
     * Максимальное количество записей в одном файле карты сайта
     *
     * @var int
     */
    private int $maxFileSize = 0;

    /**
     * Наименование индексной страницы карты сайта
     *
     * @var string
     */
    private string $indexFileName = 'sitemap.xml';

    private Configuration $config;

    public function __construct(Configuration $config)
    {
        $this->config = $config;
    }

    /**
     * Создание генератора карты сайта на основе объекта настроек
     *
     * @param SitemapSettings $settings
     * @return self
     * @throws Exception\SitemapException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function createFromSettings(SitemapSettings $settings): self
    {
        $config = Configuration::createFromSiteId($settings->siteId);

        $generator = new self($config);

        if ($settings->strategy) {
            $generator->setStrategies(
                StrategyFactory::create($settings->siteId, $settings->strategy)
            );
        }

        return $generator;
    }

    /**
     * Запустить генерацию карты сайта
     *
     * @return void
     */
    public function run(): void
    {
        $this->runtimes = [];
        $pid = $this->config->siteId;
        $index = new Index($this->indexFileName, $this->config->toArray());

        foreach ($this->strategies as $strategy) {
            $this->generate($pid, $strategy);
        }

        foreach ($this->runtimes as $name => $runtime) {
            if ($runtime->isCurrentPartNotEmpty()) {
                $runtime->finish();
            } elseif ($runtime->isExists()) {
                unset($this->runtimes[$name]);
                $runtime->delete();
            }
        }

        $index->createIndex($this->runtimes);
        $this->runtimes = [];
    }

    /**
     * Добавить стратегию формирования данных
     *
     * @param string $sitemapName
     * @param StrategyInterface $source
     * @return $this
     */
    public function addStrategy(StrategyInterface $strategy): self
    {
        $this->strategies[] = $strategy;
        return $this;
    }

    /**
     * Указать список стратегий формирования данных.
     *
     * Все ранее добавленные стратегии удалятся
     *
     * @param StrategyInterface[][] $sources
     * @return $this
     */
    public function setStrategies(array $strategies): self
    {
        $this->strategies = [];

        foreach ($strategies as $strategy) {
            $this->addStrategy($strategy);
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
    private function generate(string $pid, StrategyInterface $strategy): void
    {
        $runtime = $this->getRuntime($pid, $strategy);

        while ($entry = $strategy->fetch()) {
            $this->modify($entry);

            // TODO: Добавить событие, для модификации

            if ($this->validate($entry) === false) {
                continue;
            }

            $this->addEntry($entry, $runtime);
        }

        $this->countEntry = 0;
    }

    /**
     * Создает или возвращает файл вложенного файла с картой сайта
     *
     * @param string $pid
     * @param StrategyInterface $strategy
     * @return Runtime
     */
    private function getRuntime(string $pid, StrategyInterface $strategy): Runtime
    {
        $fileName = $strategy->getFileName();
        return $this->runtimes[$fileName] ??= new Runtime($pid, $fileName, $this->config->toArray());
    }

    /**
     * Модифицировать адрес
     *
     * @param Entry $entry
     * @return void
     */
    private function modify(Entry $entry): void
    {
        $this->modifyUrl($entry);

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

    /**
     * Добавляет хост к адресу, если он отсутствует
     *
     * @param Entry $entry
     * @return void
     */
    private function modifyUrl(Entry $entry): void
    {
        $errors = [];
        $host = $this->config->protocol
            . '://'
            . CBXPunycode::toASCII($this->config->domain, $errors);

        if (!str_starts_with($entry->url, $host)) {
            $entry->url = $host . $entry->url;
        }
    }
}