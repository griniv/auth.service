<?php

declare(strict_types = 1);

namespace AuthService;

/**
 * Класс для работы с конфигом
 */
class Config {

    /**
     * @var array 
     */
    private static $storage = [];

    /**
     * @param array $storage
     */
    public static function setStorage(array $storage): void {
        self::$storage = $storage;
    }

    /**
     * @param string $key
     * @param mixed $val
     */
    public static function set(string $key, $val): void {
        self::$storage[$key] = $val;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public static function get(string $key) {
        return array_key_exists($key, self::$storage) ? self::$storage[$key] : null;
    }

}
