<?php

namespace Sholokhov\Sitemap;

use Bitrix\Main\EventResult;
use Sholokhov\Sitemap\Source\SourceInterface;
use Sholokhov\Sitemap\Modifier\ModifierInterface;
use Sholokhov\Sitemap\Validator\ValidatorInterface;

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
     * @var SourceInterface[]
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

    /**
     * ID сайта, для которого генерируется карта
     *
     * @var string
     */
    private string $siteId;

    /**
     * Запустить генерацию карты сайта
     *
     * @return void
     */
    public function run(): void
    {
    }

    /**
     * Добавить источник данных
     *
     * @param SourceInterface $source
     * @return $this
     */
    public function addSource(SourceInterface $source): self
    {
        $this->sources[] = $source;
        return $this;
    }

    /**
     * Указать список источников данных.
     *
     * Все ранее добавленные источники удалятся
     *
     * @param array $sources
     * @return $this
     */
    public function setSources(array $sources): self
    {
        $this->sources = [];
        array_walk($sources, $this->addSource(...));

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
     * @param array $modifiers
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
     * @param array $validators
     * @return $this
     */
    public function setValidators(array $validators): self
    {
        $this->validators = [];
        array_walk($validators, $this->addValidator(...));

        return $this;
    }
}