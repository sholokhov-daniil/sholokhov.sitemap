<?php

namespace Sholokhov\Sitemap;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SiteTable;
use Bitrix\Main\SystemException;
use Sholokhov\Sitemap\Exception\SitemapException;

/**
 * Конфигурация генератора карты сайта
 *
 * @property string siteId
 * @property string $protocol
 * @property string $domain
 */
class Configuration
{
    private array $data = [
        'SITE_ID' => '',
        'PROTOCOL' => 'https',
        'DOMAIN' => ''
    ];

    private array $aliases = [
        'siteId' => 'SITE_ID',
        'protocol' => 'PROTOCOL',
        'domain' => 'DOMAIN',
    ];

    /**
     * @param string $siteId
     * @param string $domain
     */
    public function __construct(string $siteId, string $domain)
    {
        $this->siteId = $siteId;
        $this->domain = $domain;
    }

    /**
     * Создание конфигурации на основе настроек сайта
     *
     * @param string $siteId
     * @return self
     * @throws SitemapException
     * @throws ArgumentException
     * @throws ObjectPropertyException
     * @throws SystemException
     */
    public static function createFromSiteId(string $siteId): self
    {
        $site = SiteTable::getByPrimary($siteId)->fetchObject();

        if (!$site) {
            throw new SitemapException("Site $siteId not found");
        }

        return new self($siteId, $site->get('SERVER_NAME'));
    }

    /**
     * @param string $name
     * @return mixed
     * @throws SitemapException
     */
    public function __get(string $name): mixed
    {
        $key = $this->getAliasName($name);
        return $this->data[$key];
    }

    /**
     * @param string $name
     * @param $value
     * @return void
     * @throws SitemapException
     */
    public function __set(string $name, $value): void
    {
        $key = $this->getAliasName($name);
        $this->data[$key] = $value;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        if (isset($this->aliases[$name]) === false) {
            return false;
        }

        return isset($this->data[$name]);
    }

    /**
     * Преобразовать объект в массив
     *
     * @return array|string[]
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Возвращает корректный ключ массива с данными
     *
     * @param string $name
     * @return string
     * @throws SitemapException
     */
    private function getAliasName(string $name): string
    {
        $this->checkAlias($name);
        return $this->aliases[$name];
    }

    /**
     * Проверяет доступность свойства
     *
     * @param string $name
     * @return void
     * @throws SitemapException
     */
    private function checkAlias(string $name): void
    {
        if (isset($this->aliases[$name]) === false) {
            throw new SitemapException(sprintf('Unknown property "%s".', $name));
        }
    }
}