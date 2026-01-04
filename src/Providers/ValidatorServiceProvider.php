<?php

namespace Sholokhov\Sitemap\Providers;

use Sholokhov\Sitemap\Validator;
use Sholokhov\Sitemap\Container\ServiceContainer;

/**
 * Провайдер инициализирующий валидаторы генератора карты сайта
 */
class ValidatorServiceProvider implements ServiceProviderInterface
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
            ->bind(Validator\HtaccessValidator::class, Validator\HtaccessValidator::class)
            ->bind(Validator\HttpValidator::class, Validator\HttpValidator::class)
            ->bind(Validator\RobotsValidator::class, Validator\RobotsValidator::class)
            ->alias('validator.htaccess', Validator\HtaccessValidator::class)
            ->alias('validator.http', Validator\HttpValidator::class)
            ->alias('validator.robots', Validator\RobotsValidator::class);
    }
}