<?php

namespace App\Infrastructure\Router;

use Monolog\Logger;

class RedisRouter
{
    private $routes = [];
    private Logger $logger;

    public function __construct(private mixed $container)
    {
        $this->logger = $container->get('logger');
    }

    public function addRoute(string $channel, string $action, array $handler)
    {
        if (!isset($this->routes[$channel])) {
            $this->routes[$channel] = [];
        }
        $this->routes[$channel][$action] = $handler;
    }

    public function handle($channel, $action, $data)
    {
        if (!isset($this->routes[$channel][$action])) {
            throw new \RuntimeException("No handler found for channel: $channel, action: $action");
        }


        $handler = $this->routes[$channel][$action];
        $controller = $this->container->get($handler[0]);
        $method = $handler[1];

        return $controller->$method($data);
    }

    public function getChannels(): array
    {
        return array_keys($this->routes);
    }
}