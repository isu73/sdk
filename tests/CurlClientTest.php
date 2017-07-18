<?php

namespace TwoFAS\Api\tests;

use PHPUnit_Framework_TestCase;
use ReflectionMethod;
use TwoFAS\Api\HttpClient\CurlClient;

class CurlClientTest extends PHPUnit_Framework_TestCase
{
    public function testMapHeaders()
    {
        $sourceHeaders = array(
            'Foo'          => 'Bar',
            'Content-Type' => 'application/json'
        );

        $expectedHeaders = array(
            'Foo: Bar',
            'Content-Type: application/json'
        );

        $method = new ReflectionMethod('\TwoFAS\Api\HttpClient\CurlClient', 'mapHeaders');
        $method->setAccessible(true);

        $this->assertEquals($expectedHeaders, $method->invoke(new CurlClient(), $sourceHeaders));
    }
}
