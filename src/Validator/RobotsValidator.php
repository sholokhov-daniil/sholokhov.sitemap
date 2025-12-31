<?php

namespace Sholokhov\Sitemap\Validator;

use Sholokhov\Sitemap\Entry;
use Sholokhov\Sitemap\Disassemblers\RobotsDisassembler;

/**
 * Проверка на открытость адреса в рамках файла robots.txt
 */
class RobotsValidator implements ValidatorInterface
{
    /**
     * Список закрытых адресов
     *
     * @var array
     */
    protected static array $disallow = [];

    /**
     * ID сайта, для которого производится проверка robots.txt
     *
     * @var string
     */
    protected readonly string $siteId;

    public function __construct(string $siteId)
    {
        $this->siteId = $siteId;
    }

    /**
     * Выполнить проверку
     *
     * @param Entry $entry
     * @return bool
     */
    public function validate(Entry $entry): bool
    {
        $iterator = $this->getDisallow();

        foreach ($iterator as $disallow) {
            $patternUrl = str_replace(['.', '+', '*', '?'], ['\.', '\+', '.*', '\?'], $disallow);
            $pattern = sprintf('#(^.*:\/\/.*%s)|(^%s)#', $patternUrl, $patternUrl);

            if (preg_match($pattern, $entry->url)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Получение исключений.
     *
     * @return array
     */
    protected function getDisallow(): array
    {
        if (!array_key_exists($this->siteId, static::$disallow)) {
            $this->load();
        }

        return static::$disallow[$this->siteId];
    }

    /**
     * Загрузка исключений.
     *
     * @return void
     */
    protected function load(): void
    {
        $disassembler = new RobotsDisassembler($this->siteId);
        static::$disallow[$this->siteId] = $disassembler->getDisallow();
    }
}