# Генератор карты сайта, для 1с-bitrix

Еще находится в разработке


example
```php
use Sholokhov\Sitemap\Configuration;
use Sholokhov\Sitemap\Settings\SitemapSettings;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockItem;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockSettings;
use Sholokhov\Sitemap\SitemapGenerator;
use Sholokhov\Sitemap\Strategy\StrategyFactory;

$iBlockSettings = new IBlockSettings;
$iBlockSettings->fileName = 'sitemap-iblock-#IBLOCK_ID#.xml';
$iBlockSettings->active = true;
$iBlockSettings->items = [
    new IBlockItem(
        id: 2,
        active: true,
        executedSections: [],
        executedSectionElements: [1]
    )
];

$sitemapSettings = new SitemapSettings(
    active: true,
    fileName: 'sitemap.xml',
    siteId: 's1',
    maxFileSize: 30000,
    modifiers: [],
    validators: [],
    iBlock:  $iBlockSettings
);

$generatorConfiguration = new Configuration($sitemapSettings->siteId, 'bitrix-exchange-market.localhost');
$generator = new SitemapGenerator($generatorConfiguration);
$generator->setStrategies(
    StrategyFactory::create($sitemapSettings)
);

$generator->run();
```