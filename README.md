# Генератор карты сайта, для 1с-bitrix

Еще находится в разработке


Ручная конфигурация
```php
use Sholokhov\Sitemap\SitemapGenerator;
use Sholokhov\Sitemap\Settings\File\FsEntity;
use Sholokhov\Sitemap\Settings\File\FsSettings;

$siteId = 's1';
$host = 'example.com';
$config = new \Sholokhov\Sitemap\Configuration($siteId, $host);

$fsSettings = new FsSettings(
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
);

$generator = new SitemapGenerator($config);
$generator->addStrategy(
    new MyStrategy,
    new \Sholokhov\Sitemap\Strategy\FsStrategy($siteId, $fsSettings)
);

$generator->run();
```

Автоматическое создание на основе настроек
```php
use Sholokhov\Sitemap\SitemapGenerator;
use Sholokhov\Sitemap\Settings\Strategies;
use Sholokhov\Sitemap\Settings\SitemapSettings;
use Sholokhov\Sitemap\Settings\File\FsEntity;
use Sholokhov\Sitemap\Settings\File\FsSettings;
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
    validators: [
        new \Sholokhov\Sitemap\Settings\Extension(
            // Уникальный идентификатор валидатора .htaccess
            id: 'validator.htaccess'
            // Активность валидатора
            active: true,
        )
    ],
    // Стратегии генерации карты сайта
    strategy: new Strategies(
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
        fs: new FsSettings(
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
    ),
);

$generator = SitemapGenerator::createFromSettings($sitemapSettings);
$generator->run();
```

## Валидаторы ссылок

> Доступна регистрация пользовательских валидаторов.  
> Для избежания дублирования избегайте названия по шаблону ``validator.{id}``  
> Данный шаблон используется пакетом.  
> Альтернативное название своих валидаторов ``validator.custom_{id}`` или ``validator.custom.{id}``
>

Более подробная информация по работе с контейнером описана ниже.

### Проверяет редирект на уровне htaccess
ID: ``validator.htaccess``  
Класс: ``Sholokhov\Sitemap\Validator\HtaccessValidator``

Описание:  
Проверяет наличие редиректа на уровне .htaccess. Если ссылка имеет редирект, то она не проходит проверку и не попадает в карту сайта.

**Способы создания**

```php
use Sholokhov\Sitemap\Validator\HtaccessValidator;
use \Sholokhov\Sitemap\Container\ServiceContainer;

$container = ServiceContainer::getInstance();

// Рекомендуется
$container->create('validator.htaccess', ['siteId' => 's1']);

// или
$container->create(HtaccessValidator::class, ['siteId' => 's1']);

// или
new HtaccessValidator('s1');
```

### Проверяет доступность страницы и наличие через http

ID: ``validator.http``  
Класс: ``Sholokhov\Sitemap\Validator\HttpValidator``

Описание:  
Делает http запрос к каждой странице, для проверки наличия ``2xx`` ответа и отсутствия редиректов.  
Присутствует возможность проверять canonical на сходство.  
Если ссылка не проходит проверку, то не попадает в карту сайта

**Способы создания**

```php
use Sholokhov\Sitemap\Validator\HttpValidator;
use \Sholokhov\Sitemap\Container\ServiceContainer;

$container = ServiceContainer::getInstance();

// Рекомендуется
$container->create('validator.http');

// или
$container->create(HttpValidator::class);

// или
new HttpValidator();
```

## Проверка отсутствия запрета в robots.txt

ID: ``validator.robots``    
Класс: ``Sholokhov\Sitemap\Validator\RobotsValidator``

Описание:  
Парсит robots.txt и проверяет запрета на индексацию. Если присутствует запрет, то ссылка не попадает в карту сайта.

**Способы создания**

```php
use Sholokhov\Sitemap\Validator\RobotsValidator;
use \Sholokhov\Sitemap\Container\ServiceContainer;

$container = ServiceContainer::getInstance();

// Рекомендуется
$container->create('validator.robots');

// или
$container->create(RobotsValidator::class);

// или
new RobotsValidator();
```

## Стратегии генерации

> Доступна регистрация пользовательских стратегии генерации.  
> Для избежания дублирования избегайте названия по шаблону ``strategy.{id}``  
> Данный шаблон используется пакетом.  
> Альтернативное название своих стратегий ``strategy.custom_{id}`` или ``strategy.custom.{id}``

### Генерация из файлового каталога

ID: ``strategy.fs``  
Класс: ``Sholokhov\Sitemap\Strategy\FsStrategy``

Описание:  
Генерирует карту сайта на основе физических файлов и директорий сайта.

**Способы создания**

```php
use Sholokhov\Sitemap\Strategy\FsStrategy;
use Sholokhov\Sitemap\Strategy\StrategyFactory;
use \Sholokhov\Sitemap\Container\ServiceContainer;

$container = ServiceContainer::getInstance();

$settings = new FsSettings(
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
);

$siteId = 's1';
$params = [
    'siteId' => $siteId,
    'settings' => $settings
];

// Рекомендуется
$container->create('strategy.fs', $params);

// Рекомендуется
StrategyFactory::createFs($siteId, $settings);

// или
$container->create(FsStrategy::class, $params);

// или
new FsStrategy($siteId, $settings);
```

### Генерация из элементов и разделов инфоблока

ID: ``strategy.iblock``  
Класс: ``Sholokhov\Sitemap\Strategy\IBlock\IBlockStrategy``

Описание:  
Генерирует карту сайта из элементов и разделов инфоблока

**Способы создания**

```php
use Sholokhov\Sitemap\Strategy\StrategyFactory;
use Sholokhov\Sitemap\Rules\IBlock\IBlockPolicy;
use Sholokhov\Sitemap\Settings\Models\IBlock\IBlockSettings;
use Sholokhov\Sitemap\Strategy\IBlock\IBlockStrategy;
use Sholokhov\Sitemap\Container\ServiceContainer;

$container = ServiceContainer::getInstance();

$settings = new IBlockSettings(
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
);

$siteId = 's1';
$policy = new IBlockPolicy($settings);

$parameters = [
    'siteId' => $siteId,
    'fileNameTemplate' => $settings->fileName,
    'settings' => $settings->items[0],
    'policy' => $policy
];

// Рекомендуется
$container->create('strategy.iblock', $parameters);

// Рекомендуется
StrategyFactory::createIBlock($siteId, $settings);

// или
$container->create(IBlockStrategy::class, $parameters);

// или
new IBlockStrategy($siteId, $settings->fileName, $settings->items[0], $policy);
```