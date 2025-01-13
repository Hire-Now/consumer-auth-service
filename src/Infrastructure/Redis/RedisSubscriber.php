<?php

namespace App\Infrastructure\Redis;

use Predis\Client;

class RedisSubscriber
{
    private Client $redis;

    public function __construct(Client $redis)
    {
        $this->redis = $redis;
    }

    public function listen(string $channel, callable $callback): void
    {
        $pubSub = $this->redis->pubSubLoop();
        $pubSub->subscribe($channel);

        foreach ($pubSub as $message) {
            if ($message['kind'] === 'message') {
                $callback($message['payload']);
            }
        }
    }
}
