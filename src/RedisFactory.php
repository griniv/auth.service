<?php

declare(strict_types = 1);

namespace AuthService;

use Noodlehaus\Config;
use Redis;

class RedisFactory {

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config) {
        $this->config = $config;
    }

    /**
     * @return Redis
     */
    public function create(): Redis {
        $redis = new Redis();
        $redis->connect($this->config->get('redis.host'), $this->config->get('redis.port'));
        return $redis;
    }

}
