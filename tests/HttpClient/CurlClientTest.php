<?php

namespace TwoFAS\Api\HttpClient;

use PHPUnit_Framework_TestCase;
use ReflectionException;
use ReflectionMethod;

class CurlClientTest extends PHPUnit_Framework_TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testMapHeaders()
    {
        $sourceHeaders = [
            'Foo'          => 'Bar',
            'Content-Type' => 'application/json'
        ];

        $expectedHeaders = [
            'Foo: Bar',
            'Content-Type: application/json'
        ];

        $method = new ReflectionMethod('\TwoFAS\Api\HttpClient\CurlClient', 'mapHeaders');
        $method->setAccessible(true);

        $this->assertEquals($expectedHeaders, $method->invoke(new CurlClient(), $sourceHeaders));
    }
}
