<?php

namespace Sholokhov\Sitemap\Strategy\IBlock;

use Sholokhov\Sitemap\Entry;
use Sholokhov\Sitemap\Strategy\AbstractStrategy;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockSettings;

class IBlockStrategy extends AbstractStrategy
{
    /**
     * Конфигурация стратегии генерации карты сайта
     *
     * @var object
     */
    protected readonly object $settings;

    /**
     * Идентификатор процесса генерации
     *
     * @var string
     */
    protected readonly string $pid;

    /**
     * ID сайта, для которого производится генерация
     *
     * @var string
     */
    protected readonly string $siteId;

    public function __construct(string $siteId, IBlockSettings $settings)
    {
        $this->settings = $settings;
        $this->siteId = $siteId;

        // TODO: Добавить генерацию pid
        $this->pid = crc32($this->siteId . 'iblock');
    }

    protected function configuration(): void
    {
        // TODO: Implement configuration() method.
    }

    protected function logic(): Entry|null
    {
        // TODO: Implement logic() method.
    }

    public function fetch(): Entry
    {
        foreach ($this->settings->items as $iBlockSettings) {

        }
    }
}