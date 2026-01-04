# Генератор карты сайта, для 1с-bitrix

Еще находится в разработке


Ручная конфигурация
```php
use Sholokhov\Sitemap\SitemapGenerator;
use Sholokhov\Sitemap\Configuration;
use Sholokhov\Sitemap\Settings\SitemapSettings;
use Sholokhov\Sitemap\Settings\File\FsEntity;
use Sholokhov\Sitemap\Settings\File\FsSettings;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockItem;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockSettings;
use Sholokhov\Sitemap\Strategy\StrategyFactory;

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
    // Настройки генерации карты сайта, для инфоблоков
    iBlock: new IBlockSettings(
        // Активность генерации
        active: true,
        // Наименование файла в который будет производиться запись
        fileName: 'sitemap-iblock-#IBLOCK_ID#.xml',
        // Настройки конкретных инфоблоков
        items: [
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
        ]
    ),
    // Настройки генерации карты сайта из файловой системы
    file: new FsSettings(
        // Активность генерации
        active: true,
        // Наименование файла в который будет производиться запись
        fileName: 'sitemap-files.xml',
        // Директории и файлы принимающие участие в генерации
        items: [
            new FsEntity(
                // Принимает участие в генерации
                active: true, 
                // Путь до файла или директории
                path: '/about/', 
                // Тип элемента файловой системы: D - директория; F - файл
                type: 'D'
            ),
        ]
    )
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
use Sholokhov\Sitemap\SitemapGenerator;
use Sholokhov\Sitemap\Settings\File\FsEntity;
use Sholokhov\Sitemap\Settings\File\FsSettings;
use Sholokhov\Sitemap\Settings\SitemapSettings;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockItem;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockSettings;

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
    // Настройки генерации карты сайта, для инфоблоков
    iBlock: new IBlockSettings(
        // Активность генерации
        active: true,
        // Наименование файла в который будет производиться запись
        fileName: 'sitemap-iblock-#IBLOCK_ID#.xml',
        // Настройки конкретных инфоблоков
        items: [
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
        ]
    ),
    // Настройки генерации карты сайта из файловой системы
    file: new FsSettings(
        // Активность генерации
        active: true,
        // Наименование файла в который будет производиться запись
        fileName: 'sitemap-files.xml',
        // Директории и файлы принимающие участие в генерации
        items: [
            new FsEntity(
                // Принимает участие в генерации
                active: true, 
                // Путь до файла или директории
                path: '/about/', 
                // Тип элемента файловой системы: D - директория; F - файл
                type: 'D'
            ),
        ]
    )
);

$generator = SitemapGenerator::createFromSettings($sitemapSettings);
$generator->run();
```