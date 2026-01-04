<?php

namespace Sholokhov\Sitemap\Container;

use ReflectionClass;
use ReflectionException;
use ReflectionParameter;
use RuntimeException;

/**
 * Минималистичный DI-контейнер в стиле Laravel.
 * Не является PSR-11 контейнером намеренно (упрощённый API).
 *
 * @final
 */
final class ServiceContainer
{
    /**
     * Обычные бинды (transient)
     * Каждый вызов create() создаёт новый объект.
     *
     * @var array<string, callable|class-string>
     */
    protected array $bindings = [];

    /**
     * Singleton-бинды
     * Экземпляр создаётся один раз и кэшируется.
     *
     * @var array<string, callable|class-string>
     */
    protected array $singletons = [];

    /**
     * Готовые экземпляры (instance / singleton)
     *
     * @var array<string, object>
     */
    protected array $instances = [];

    /**
     * Алиасы сервисов
     *
     * alias => id
     *
     * @var array<string, string>
     */
    protected array $aliases = [];

    /**
     * @var ServiceContainer
     */
    private static self $instance;

    protected function __construct()
    {
    }

    /**
     * @return self
     */
    public static function getInstance(): self
    {
        return self::$instance ??= new self;
    }

    /**
     * Регистрация зависимости (transient)
     *
     * @param string $id Идентификатор сервиса (class-string или alias)
     * @param callable|class-string $concrete Фабрика или конкретный класс
     *
     * @return $this
     */
    public function bind(string $id, callable|string $concrete): self
    {
        $this->bindings[$id] = $concrete;
        return $this;
    }

    /**
     * Регистрация singleton-зависимости
     *
     * Экземпляр будет создан один раз
     * и переиспользован при последующих вызовах.
     *
     * @param string $id Идентификатор сервиса
     * @param callable|class-string $concrete Реализация singleton-а
     *
     * @return $this
     * @example $container->singleton(RobotsValidator::class, fn() => new RobotsValidator)
     */
    public function singleton(string $id, callable|string $concrete): self
    {
        $this->singletons[$id] = $concrete;
        return $this;
    }

    /**
     * Регистрация уже созданного экземпляра
     *
     * @param string $id Идентификатор сервиса
     * @param object $instance Готовый объект
     *
     * @return $this
     * @example $container->instance(RobotsValidator::class, new RobotsValidator)
     */
    public function instance(string $id, object $instance): self
    {
        $this->instances[$id] = $instance;
        return $this;
    }

    /**
     * Регистрация alias для сервиса
     *
     * @param string $alias Альтернативное имя сервиса
     * @param string $id Реальный идентификатор сервиса
     *
     * @return $this
     *
     * @throws RuntimeException
     * @example $container->alias('validator.robots', RobotsValidator::class);
     */
    public function alias(string $alias, string $id): self
    {
        if ($alias === $id) {
            throw new RuntimeException("Alias [$alias] cannot reference itself");
        }

        $this->aliases[$alias] = $id;

        return $this;
    }

    /**
     * Проверка, может ли контейнер создать зависимость
     *
     * @param string $id Идентификатор сервиса
     *
     * @return bool
     *
     * @example $container->has(RobotsValidator::class)
     * @example $container->has('validator.robots')
     */
    public function has(string $id): bool
    {
        $id = $this->resolveAlias($id);

        return isset($this->bindings[$id])
            || isset($this->singletons[$id])
            || isset($this->instances[$id])
            || class_exists($id);
    }

    /**
     * Создание объекта (аналог make() в Laravel)
     *
     * @param string $id Идентификатор сервиса или class-string
     * @param array<string, mixed> $parameters Явно передаваемые параметры конструктора
     *
     * @return object
     *
     * @throws RuntimeException
     *
     * @example $container->create('validator.robots', ['siteId' => 's1'])
     * @example $container->create(RobotsValidator::class, ['siteId' => 's1'])
     */
    public function create(string $id, array $parameters = []): object
    {
        $id = $this->resolveAlias($id);

        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        if (isset($this->singletons[$id])) {
            $object = $this->buildFromConcrete(
                $this->singletons[$id],
                $parameters
            );

            $this->instances[$id] = $object;

            return $object;
        }

        if (isset($this->bindings[$id])) {
            return $this->buildFromConcrete(
                $this->bindings[$id],
                $parameters
            );
        }

        if (class_exists($id)) {
            return $this->buildClass($id, $parameters);
        }

        throw new RuntimeException("Container: cannot resolve [$id]");
    }

    /**
     * Разрешение alias → реальный id
     *
     * @param string $id
     *
     * @return string
     *
     * @throws RuntimeException
     */
    protected function resolveAlias(string $id): string
    {
        $visited = [];

        while (isset($this->aliases[$id])) {
            if (isset($visited[$id])) {
                throw new RuntimeException("Circular alias detected for [$id]");
            }

            $visited[$id] = true;
            $id = $this->aliases[$id];
        }

        return $id;
    }

    /**
     * Создание объекта из concrete-определения
     *
     * @param callable|class-string $concrete Реализация бинда
     * @param array<string, mixed> $parameters Параметры конструктора
     *
     * @return object
     */
    protected function buildFromConcrete(callable|string $concrete, array $parameters): object
    {
        if (is_callable($concrete)) {
            return $concrete($this, $parameters);
        }

        if (is_string($concrete)) {
            return $this->buildClass($concrete, $parameters);
        }

        throw new RuntimeException('Invalid container binding');
    }

    /**
     * Автоматическая сборка класса через Reflection
     *
     * @param class-string $class Имя класса
     * @param array<string, mixed> $parameters Переопределяемые параметры конструктора
     *
     * @return object
     *
     * @throws ReflectionException
     * @throws RuntimeException
     */
    protected function buildClass(string $class, array $parameters): object
    {
        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new RuntimeException("Class [$class] is not instantiable");
        }

        $constructor = $reflection->getConstructor();

        if ($constructor === null) {
            return new $class();
        }

        $dependencies = [];

        foreach ($constructor->getParameters() as $parameter) {
            $dependencies[] = $this->resolveParameter(
                $parameter,
                $parameters
            );
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Разрешение параметра конструктора
     *
     * Приоритет:
     * 1. Явно переданный параметр
     * 2. Типизированная зависимость из контейнера
     * 3. Значение по умолчанию
     *
     * @param ReflectionParameter $parameter Параметр конструктора
     * @param array<string, mixed> $parameters Явно переданные параметры
     *
     * @return mixed
     *
     * @throws RuntimeException
     */
    protected function resolveParameter(ReflectionParameter $parameter, array $parameters): mixed
    {
        $name = $parameter->getName();

        // Явно переданный параметр
        if (array_key_exists($name, $parameters)) {
            return $parameters[$name];
        }

        $type = $parameter->getType();

        // Типизированная зависимость
        if ($type && !$type->isBuiltin()) {
            return $this->create($type->getName());
        }

        // Значение по умолчанию
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        throw new RuntimeException(
            "Unresolvable dependency [\${$name}]"
        );
    }
}
