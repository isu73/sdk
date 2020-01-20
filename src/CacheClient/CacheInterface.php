<?php

namespace TwoFAS\Api\CacheClient;

use InvalidArgumentException;

interface CacheInterface
{
    /**
     * @param string $key
     *
     * @return bool
     */
    public function has($key);

    /**
     * @param string $key
     *
     * @return string
     *
     * @throws InvalidArgumentException
     */
    public function get($key);

    /**
     * @param string $key
     * @param string $value
     */
    public function set($key, $value);
}