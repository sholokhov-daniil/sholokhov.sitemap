<?php

namespace Sholokhov\Sitemap\Events;

use Bitrix\Main\EventResult;
use Exception;

use Bitrix\Main\Event;
use Bitrix\Main\EventManager as BXEventManager;
use Bitrix\Main\ArgumentTypeException;

/**
 * Менеджер событий модуля
 *
 * Является оберткой над {@see BXEventManager}
 */
class EventManager
{
    protected string $moduleID;
    protected static self $instance;

    /**
     * Зарегистрированные события.
     *
     * @var array[]
     * @author Daniil S. GlobalArts
     */
    protected array $events = [];

    protected function __construct()
    {
        $this->moduleID = 'sholokhov.sitemap';
    }

    /**
     * Вызов события.
     *
     * @param string $id
     * @param array $parameters
     * @return EventResult[]
     * @throws ArgumentTypeException
     * @throws Exception
     */
    public function call(string $id, array &$parameters = []): array
    {
        $data = $this->get($id);
        $data['event']->setParameters($parameters);
        $data['event']->send();

        if (is_callable($data['callback'])) {
            foreach ($data['event']->getResults() as $eventResult) {
                call_user_func($data['callback'], $eventResult);
            }
        }

        return $data['event']->getResults();
    }

    /**
     * Подписка на событие.
     *
     * @param string $id
     * @param callable $callback
     * @param int $sort
     * @return void
     */
    public function subscribe(string $id, callable $callback, int $sort = 500): void
    {
        BXEventManager::getInstance()->addEventHandler(
            $this->moduleID,
            $id,
            $callback,
            false,
            $sort
        );
    }

    /**
     * Регистрация события.
     *
     * @param string $id Тип события
     * @param callable|null $callback Обработчик результатов события
     * @return self
     */
    public function registration(string $id, callable $callback = null): self
    {
        $this->events[$id] = [
            'event' => new Event($this->moduleID, $id),
            'callback' => $callback
        ];

        return $this;
    }

    /**
     * Вызов события.
     *
     * @param string $id
     * @return array{event: Event, callback: callable|null}
     * @throws Exception
     */
    public function get(string $id): array
    {
        $this->check($id);
        return $this->events[$id];
    }

    /**
     * Проверка наличия события.
     *
     * @param string $id
     * @return bool
     */
    public function has(string $id): bool
    {
        return array_key_exists($id, $this->events);
    }

    /**
     * Проверка наличия события.
     *
     * @param string $id
     * @return void
     * @throws Exception
     */
    protected function check(string $id): void
    {
        if (!$this->has($id)) {
            throw new Exception('Event not registered');
        }
    }

    /**
     * Получение менеджера события.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        return static::$instance ??= new static;
    }
}