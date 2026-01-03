<?php

namespace Sholokhov\Sitemap\Strategy;

use Bitrix\Seo\Sitemap\File\Runtime;
use Sholokhov\Sitemap\Configuration;
use Sholokhov\Sitemap\Entry;
use Sholokhov\Sitemap\Exception\SitemapException;
use Sholokhov\Sitemap\Source\SourceInterface;
use function PHPUnit\Framework\containsEqual;

/**
 * Базовая реализация стратегии формирования данных, для генерации карты сайта
 */
abstract class AbstractStrategy implements StrategyInterface
{
    private array $runtimes = [];

    protected function generate(Runtime $runtime, SourceInterface $source): void
    {
        while ($entry = $source->fetch()) {
            $this->modifyEntry($entry);

            // TODO: Добавить событие, для модификации
            if (!$this->validateEntry($entry)) {
                continue;
            }

            $this->addEntry($entry, $runtime);
        }
    }

    protected function getRuntime(string $pid): ?Runtime
    {
        return $this->runtimes[$pid] ?? null;
    }

    protected function createRuntime(string $pid, string $fileName, Configuration $config): Runtime
    {
        if (isset($this->runtimes[$pid])) {
            throw new SitemapException('Runtime already exists: ' . $pid);
        }

        return $this->runtimes[$pid] = new Runtime($pid, $fileName, $config->toArray());
    }

    protected function modifyEntry(Entry $entry): void
    {
    }

    protected function validateEntry(Entry $entry): bool
    {
    }

    protected function addEntry(Entry $entry, Runtime $runtime): void
    {
    }

    protected function isSplitNeeded(): bool
    {
    }
}