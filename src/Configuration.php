<?php

namespace Sholokhov\Sitemap;

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
     * @param string $name
     * @return mixed
     * @throws SitemapException
     */
    public function __get(string $name): mixed
    {
        $this->checkAlias($name);
        return $this->data[$name];
    }

    /**
     * @param string $name
     * @param $value
     * @return void
     * @throws SitemapException
     */
    public function __set(string $name, $value): void
    {
        $this->checkAlias($name);
        $this->data[$name] = $value;
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