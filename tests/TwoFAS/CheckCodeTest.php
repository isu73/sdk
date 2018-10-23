<?php

namespace TwoFAS\Api\tests\TwoFAS;

use TwoFAS\Api\Code\AcceptedCode;
use TwoFAS\Api\Code\RejectedCodeCannotRetry;
use TwoFAS\Api\Code\RejectedCodeCanRetry;
use TwoFAS\Api\Exception\ValidationException;
use TwoFAS\Api\HttpCodes;
use TwoFAS\Api\Response\ResponseGenerator;

class CheckCodeTest extends LiveAndMockBase
{
    public function testCheckValidCode()
    {
        $ids        = array('5800c43d69e60', '5800c43d69e70');
        $collection = $this->makeAuthenticationCollection($ids);

        $expectedResponse = new AcceptedCode($collection->getIds());

        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode(array()), HttpCodes::NO_CONTENT));
        }

        $actualResponse = $this->twoFAS->checkCode($collection, '123456');
        $this->assertEquals($expectedResponse, $actualResponse);
    }

    public function testCheckInvalidCodeFormat()
    {
        $this->setExpectedException('\TwoFAS\Api\Exception\ValidationException');

        $response = array('error' => array(
            'code' => 9030,
            'msg'  => array(
                'code' => array(
                    'validation.digits'
                )
            )
        ));

        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::BAD_REQUEST));
        }

        try {
            $collection = $this->makeAuthenticationCollection(array('5800c43d69e80'));
            $this->twoFAS->checkCode($collection, 'ABC!@#');
        } catch (ValidationException $e) {
            $this->assertFalse($e->hasKey('authentications'));
            throw $e;
        }
    }

    public function testCheckCodeCanRetry()
    {
        $ids        = array('5800c43d69e80');
        $collection = $this->makeAuthenticationCollection($ids);

        $response = array('error' => array(
            'code' => 9061,
            'msg'  => 'Invalid code can retry'
        ));

        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::FORBIDDEN));
        }

        $result = $this->twoFAS->checkCode($collection, '654321');
        $this->assertEquals(new RejectedCodeCanRetry($collection->getIds()), $result);
    }

    public function testCheckCodeCannotRetry()
    {
        $ids        = array('1', '2', '3');
        $collection = $this->makeAuthenticationCollection($ids);

        $response = array('error' => array(
            'code' => 9062,
            'msg'  => 'Invalid code can not retry'
        ));

        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::FORBIDDEN));
        }

        $result = $this->twoFAS->checkCode($collection, '123456');
        $this->assertEquals(new RejectedCodeCannotRetry($collection->getIds()), $result);
    }
}