<?php

namespace Sholokhov\Sitemap\Factory;

use Sholokhov\Sitemap\Exception\SitemapException;
use Sholokhov\Sitemap\Validator\ValidatorInterface;

/**
 * Сборщик валидаторов
 */
class ValidatorFactory
{
    /**
     * ID сайта, для которого создается валидатор
     *
     * @var string
     */
    private readonly string $siteId;

    /**
     * Список зарегистрированных валидаторов
     *
     * @var array<string, callable>
     */
    protected array $registry = [];

    public function __construct(string $siteId)
    {
        $this->siteId = $siteId;
    }

    /**
     * Создать валидатор
     *
     * @param string $id
     * @return ValidatorInterface
     * @throws SitemapException
     */
    public function create(string $id)
    {
        if (!isset($this->registry[$id])) {
            throw new SitemapException('Validator not found: ' . $id);
        }

        $callback = $this->registry[$id];
        $validator = call_user_func($callback, $this->siteId);

        if (!($validator instanceof ValidatorInterface)) {
            throw new SitemapException("Validator created by '{$id}' must implement " . ValidatorInterface::class);
        }

        return $validator;
    }

    /**
     * Регистрация валидатора
     *
     * @param string $id
     * @param callable $callback
     * @return $this
     */
    public function bind(string $id, callable $callback): self
    {
        $this->registry[$id] = $callback;
        return $this;
    }
}