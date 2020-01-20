<?php

namespace TwoFAS\Api\CacheClient;

use InvalidArgumentException;

class ArrayCache implements CacheInterface
{
    /**
     * @var array
     */
    private $values = [];

    /**
     * @inheritDoc
     */
    public function has($key)
    {
        return array_key_exists($key, $this->values);
    }

    /**
     * @inheritDoc
     */
    public function get($key)
    {
        if (!$this->has($key)) {
            throw new InvalidArgumentException();
        }

        return $this->values[$key];
    }

    /**
     * @inheritDoc
     */
    public function set($key, $value)
    {
        $this->values[$key] = $value;
    }
}