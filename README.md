# Генератор карты сайта, для 1с-bitrix

Еще находится в разработке


example
```php
use Sholokhov\Sitemap\Configuration;
use Sholokhov\Sitemap\SitemapGenerator;
use Sholokhov\Sitemap\Strategy\StrategyFactory;
use Sholokhov\Sitemap\Settings\SitemapSettings;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockItem;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockSettings;

$iBlockSettings = new IBlockSettings;
$iBlockSettings->fileName = 'sitemap-iblock-#IBLOCK_ID#.xml';
$iBlockSettings->active = true;
$iBlockSettings->items = [
    new IBlockItem(2, [], true)
];

$sitemapSettings = new SitemapSettings(
    true,
    'sitemap.xml',
    's1',
    30000,
    [],
    [],
    $iBlockSettings
);

$generatorConfiguration = new Configuration($sitemapSettings->siteId, 'bitrix-exchange-market.localhost');
$generator = new SitemapGenerator($generatorConfiguration);
$generator->setStrategies(
    StrategyFactory::create($sitemapSettings)
);

$generator->run();
```