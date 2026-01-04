<?php

namespace Sholokhov\Sitemap\Providers;

use Sholokhov\Sitemap\Container\ServiceContainer;

interface ServiceProviderInterface
{
    /**
     * Регистрация действия провайдера
     *
     * @param ServiceContainer $container
     *
     * @return void
     */
    public function register(ServiceContainer $container): void;
}