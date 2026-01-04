<?php

namespace Sholokhov\Sitemap\Providers;

use Sholokhov\Sitemap\Strategy;
use Sholokhov\Sitemap\Container\ServiceContainer;

/**
 * Провайдер инициализирующий стратегий генерации данных
 */
class StrategyServiceProvider implements ServiceProviderInterface
{
    /**
     * Регистрания провайдера
     *
     * @param ServiceContainer $container
     *
     * @return void
     */
    public function register(ServiceContainer $container): void
    {
        $container
            ->bind(Strategy\FsStrategy::class, Strategy\FsStrategy::class)
            ->bind(Strategy\IBlock\IBlockStrategy::class, Strategy\IBlock\IBlockStrategy::class)
            ->alias('strategy.fs', Strategy\FsStrategy::class)
            ->alias('strategy.iblock', Strategy\IBlock\IBlockStrategy::class);
    }
}