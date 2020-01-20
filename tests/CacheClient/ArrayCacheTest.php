<?php

namespace TwoFAS\Api\CacheClient;

use PHPUnit_Framework_TestCase;

class ArrayCacheTest extends PHPUnit_Framework_TestCase
{
    public function testSet()
    {
        $cache = new ArrayCache();
        $cache->set('key', 'value');

        return $cache;
    }

    /**
     * @depends testSet
     *
     * @param ArrayCache $cache
     */
    public function testHas(ArrayCache $cache)
    {
        $this->assertTrue(
            $cache->has('key')
        );
    }

    /**
     * @depends testSet
     *
     * @param ArrayCache $cache
     */
    public function testGet(ArrayCache $cache)
    {
        $this->assertEquals('value', $cache->get('key'));
    }

    public function testGetNonExistingValue()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $cache = new ArrayCache();
        $cache->get('key');
    }
}