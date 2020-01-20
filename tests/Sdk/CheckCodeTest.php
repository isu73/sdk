<?php

namespace TwoFAS\Api\Sdk;

use TwoFAS\Api\Code\AcceptedCode;
use TwoFAS\Api\Code\RejectedCodeCannotRetry;
use TwoFAS\Api\Code\RejectedCodeCanRetry;
use TwoFAS\Api\Exception\ValidationException;
use TwoFAS\Api\HttpCodes;

class CheckCodeTest extends LiveAndMockBase
{
    public function testCheckValidCode()
    {
        $ids              = ['5800c43d69e60', '5800c43d69e70'];
        $expectedResponse = new AcceptedCode($ids);

        if ($this->isDevelopmentEnvironment()) {
            $this->nextApiCallWillReturn([], HttpCodes::NO_CONTENT);
        }

        $actualResponse = $this->sdk->checkCode($ids, '123456');
        $this->assertEquals($expectedResponse, $actualResponse);
    }

    public function testCheckInvalidCodeFormat()
    {
        $this->setExpectedException('\TwoFAS\Api\Exception\ValidationException');

        $response = ['error' => [
            'code' => 9030,
            'msg'  => [
                'code' => [
                    'validation.digits'
                ]
            ]
        ]];

        if ($this->isDevelopmentEnvironment()) {
            $this->nextApiCallWillReturn($response, HttpCodes::BAD_REQUEST);
        }

        try {
            $this->sdk->checkCode(['5800c43d69e80'], 'ABC!@#');
        } catch (ValidationException $e) {
            $this->assertFalse($e->hasKey('authentications'));
            throw $e;
        }
    }

    public function testCheckCodeCanRetry()
    {
        $ids = ['5800c43d69e80'];

        $response = ['error' => [
            'code' => 9061,
            'msg'  => 'Invalid code can retry'
        ]];

        if ($this->isDevelopmentEnvironment()) {
            $this->nextApiCallWillReturn($response, HttpCodes::FORBIDDEN);
        }

        $result = $this->sdk->checkCode($ids, '654321');
        $this->assertEquals(new RejectedCodeCanRetry($ids), $result);
    }

    public function testCheckCodeCannotRetry()
    {
        $ids = ['1', '2', '3'];

        $response = ['error' => [
            'code' => 9062,
            'msg'  => 'Invalid code can not retry'
        ]];

        if ($this->isDevelopmentEnvironment()) {
            $this->nextApiCallWillReturn($response, HttpCodes::FORBIDDEN);
        }

        $result = $this->sdk->checkCode($ids, '123456');
        $this->assertEquals(new RejectedCodeCannotRetry($ids), $result);
    }
}