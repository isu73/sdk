<?php

namespace TwoFAS\Api\Sdk;

use TwoFAS\Api\HttpCodes;

class FormatNumberTest extends LiveAndMockBase
{
    protected $mockedMethods = ['getIntegrationUser', 'updateIntegrationUser'];

    public function testResolved()
    {
        if ($this->isDevelopmentEnvironment()) {
            $data = ['phone_number' => '+15123638262'];
            $this->nextApiCallWillReturn($data, HttpCodes::OK);
        }

        $response = $this->sdk->formatNumber('512 363 8262');
        $this->assertInstanceOf('TwoFAS\Api\FormattedNumber', $response);
        $this->assertEquals('+15123638262', $response->phoneNumber());
    }

    public function testRejected()
    {
        if ($this->isDevelopmentEnvironment()) {
            $data = ['error' => [
                'code' => 9010,
                'msg'  => 'Invalid number'
            ]];
            $this->nextApiCallWillReturn($data, HttpCodes::NOT_ACCEPTABLE);
        }

        $this->setExpectedException('TwoFAS\Api\Exception\InvalidNumberException');

        $this->sdk->formatNumber('000000000');
    }

    public function testEmptyString()
    {
        if ($this->isDevelopmentEnvironment()) {
            $data = ['error' => [
                'code' => 9030,
                'msg'  => [
                    'phone_number' => ['validation.required']
                ]
            ]];
            $this->nextApiCallWillReturn($data, HttpCodes::BAD_REQUEST);
        }

        $this->setExpectedException('TwoFAS\Api\Exception\ValidationException', 'Validation exception');

        $this->sdk->formatNumber('');
    }

    public function testMultipleCallsWithSameData()
    {
        if ($this->isDevelopmentEnvironment()) {
            $data = ['phone_number' => '+15123638262'];
            $this->nextApiCallWillReturn($data, HttpCodes::OK);
        }

        $this->sdk->formatNumber('512 363 8262');
        $this->sdk->formatNumber('512 363 8262');
    }
}