<?php

namespace TwoFAS\Api\tests\TwoFAS;

use TwoFAS\Api\HttpCodes;
use TwoFAS\Api\Response\ResponseGenerator;

class StatisticsTest extends LiveAndMockBase
{
    public function testGet()
    {
        $this->setUpTwoFAS(getenv('second_login'), getenv('second_key'), $this->mockedMethods);

        $expected = array(
            'total' => 3,
            'sms'   => array(
                'enabled'    => 1,
                'configured' => 3
            ),
            'call'  => array(
                'enabled'    => 1,
                'configured' => 3
            ),
            'totp'  => array(
                'enabled'    => 1,
                'configured' => 2
            ),
            'email' => array(
                'enabled'    => 0,
                'configured' => 3
            )
        );

        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($expected), HttpCodes::OK));
        }

        $statistics = $this->twoFAS->getStatistics();
        $this->assertInstanceOf('\TwoFAS\Api\Statistics', $statistics);
        $this->assertEquals($expected, $statistics->getAll());
        $this->assertEquals(3, $statistics->getTotal());
    }

    public function testAfterApiChange()
    {
        $this->setUpTwoFAS(getenv('second_login'), getenv('second_key'), $this->mockedMethods);

        $expected = array(
            'total' => 3,
            'sms'   => array(
                'enabled'    => 1,
                'configured' => 3
            ),
            'call'  => array(
                'enabled'    => 1,
                'configured' => 3
            ),
            'totp'  => array(
                'enabled'    => 1,
                'configured' => 2
            ),
            'email' => array(
                'enabled'    => 0,
                'configured' => 3
            )
        );

        if ($this->isDevelopmentEnvironment()) {
            $result = array_merge($expected, array('new' => array(
                'enabled'    => 0,
                'configured' => 0
            )));

            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($result), HttpCodes::OK));
        }

        $statistics = $this->twoFAS->getStatistics();
        $this->assertInstanceOf('\TwoFAS\Api\Statistics', $statistics);
        $this->assertEquals($expected, $statistics->getAll());
        $this->assertEquals(3, $statistics->getTotal());
    }
}
