# Генератор карты сайта, для 1с-bitrix

Еще находится в разработке


Ручная конфигурация
```php
use Sholokhov\Sitemap\Configuration;
use Sholokhov\Sitemap\Settings\SitemapSettings;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockItem;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockSettings;
use Sholokhov\Sitemap\SitemapGenerator;
use Sholokhov\Sitemap\Strategy\StrategyFactory;

// Настройки генерации карты сайта, для инфоблоков
$iBlockSettings = new IBlockSettings;
$iBlockSettings->fileName = 'sitemap-iblock-#IBLOCK_ID#.xml';
$iBlockSettings->active = true;
$iBlockSettings->items = [
    new IBlockItem(
        // ID инфоблока
        id: 2,
        // Инфоблок принимает участие в формировании sitemap
        active: true,
        // Массив ID разделов исключенных из формирования sitemap (запрещен и всем вложенным)
        executedSections: [],
        // Массив ID разделов в которых элементы не принимают участие в генерации sitemap (запрещен и всем вложенным)
        executedSectionElements: [1]
    )
];

// Общие настройки генерации карты сайта
$sitemapSettings = new SitemapSettings(
    // Активность генерации sitemap
    active: true,
    // Наименование индексного файла
    fileName: 'sitemap.xml',
    // ID сайта, для которого генерируется sitemap
    siteId: 's1',
    // Максимальное количество записей в рамках одного файла
    maxFileSize: 30000,
    // Модификаторы ссылок
    modifiers: [],
    // Валидаторы ссылок
    validators: [],
    // Настройки генерации sitemap для инфоблоков
    iBlock:  $iBlockSettings
);

$generatorConfiguration = new Configuration($sitemapSettings->siteId, 'bitrix-exchange-market.localhost');
$generator = new SitemapGenerator($generatorConfiguration);
$generator->setStrategies(
    StrategyFactory::create($sitemapSettings)
);

$generator->run();
```

Автоматическое создание на основе настроек
```php
use Sholokhov\Sitemap\Settings\SitemapSettings;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockItem;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockSettings;
use Sholokhov\Sitemap\SitemapGenerator;

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

$generator = SitemapGenerator::createFromSettings($sitemapSettings);
$generator->run();
```