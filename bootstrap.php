<?php

use Sholokhov\Sitemap\Providers;
use Sholokhov\Sitemap\Container\ServiceContainer;

$container = ServiceContainer::getInstance();

$providers = [
    Providers\ValidatorServiceProvider::class,
    Providers\StrategyServiceProvider::class,
];

foreach ($providers as $entityName) {
    /** @var Providers\ServiceProviderInterface $provider */
    $provider = $container->create($entityName);
    $provider->register($container);
}