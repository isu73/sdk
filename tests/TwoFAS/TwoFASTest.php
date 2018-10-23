<?php

namespace TwoFAS\Api\tests\TwoFAS;

use TwoFAS\Api\HttpCodes;
use TwoFAS\Api\Response\ResponseGenerator;

class TwoFASTest extends LiveAndMockBase
{
    public function testResponse()
    {
        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom('{}', HttpCodes::OK));
        }

        $response = $this->httpClient->request('GET', $this->baseUrl, 'login', 'password');
        $this->assertInstanceOf('\TwoFAS\Api\Response\Response', $response);
    }

    public function testAuthRequestViaSms()
    {
        $phoneNumber = '+48512256400';

        if ($this->isDevelopmentEnvironment()) {
            $response = array_merge(
                $this->getNewAuthenticationResponse(),
                array('phone_number' => $phoneNumber)
            );

            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::OK));
        }

        $authentication = $this->twoFAS->requestAuthViaSms($phoneNumber);
        $this->assertInstanceOf('\TwoFAS\Api\Authentication', $authentication);
        $this->assertNotNull($authentication->id());
        $this->assertNotNull($authentication->createdAt());
        $this->assertNotNull($authentication->validTo());
    }

    public function testAuthRequestViaCall()
    {
        $phoneNumber = '+48512256400';

        if ($this->isDevelopmentEnvironment()) {
            $response = array_merge(
                $this->getNewAuthenticationResponse(),
                array('phone_number' => $phoneNumber)
            );

            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::OK));
        }

        $authentication = $this->twoFAS->requestAuthViaCall($phoneNumber);
        $this->assertInstanceOf('\TwoFAS\Api\Authentication', $authentication);
        $this->assertNotNull($authentication->id());
        $this->assertNotNull($authentication->createdAt());
        $this->assertNotNull($authentication->validTo());
    }

    public function testAuthRequestViaEmail()
    {
        if ($this->isDevelopmentEnvironment()) {
            $response = $this->getNewAuthenticationResponse();

            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::OK));
        }

        $authentication = $this->twoFAS->requestAuthViaEmail('aaa@2fas.com');
        $this->assertInstanceOf('\TwoFAS\Api\Authentication', $authentication);
        $this->assertNotNull($authentication->id());
        $this->assertNotNull($authentication->createdAt());
        $this->assertNotNull($authentication->validTo());
    }

    public function testAuthRequestViaTotp()
    {
        if ($this->isDevelopmentEnvironment()) {
            $response = $this->getNewAuthenticationResponse();

            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::OK));
        }

        $authentication = $this->twoFAS->requestAuthViaTotp('PEHMPSDNLXIOG65U');
        $this->assertInstanceOf('\TwoFAS\Api\Authentication', $authentication);
        $this->assertNotNull($authentication->id());
        $this->assertNotNull($authentication->createdAt());
        $this->assertNotNull($authentication->validTo());
    }

    public function testAuthRequestViaTotpWithMobileSupport()
    {
        $this->setUpTwoFAS(getenv('second_login'), getenv('second_key'), $this->mockedMethods);

        $totpSecret   = 'PEHMPSDNLXIOG65U';
        $mobileSecret = '9e3d5538259e283b2e6f3ecb29a0d269';

        if ($this->isDevelopmentEnvironment()) {
            $response = $this->getNewAuthenticationResponse();

            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::OK));
        }

        $authentication = $this->twoFAS->requestAuthViaTotpWithMobileSupport(
            $totpSecret,
            $mobileSecret,
            uniqid('remaining_characters'),
            'Chrome 56, macOS Sierra'
        );

        $this->assertInstanceOf('\TwoFAS\Api\Authentication', $authentication);
        $this->assertNotNull($authentication->id());
        $this->assertNotNull($authentication->createdAt());
        $this->assertNotNull($authentication->validTo());
    }

    public function testAuthRequestViaSmsWithoutCard()
    {
        $this->setUpTwoFAS(getenv('second_login'), getenv('second_key'), $this->mockedMethods);

        if ($this->isDevelopmentEnvironment()) {
            $response = array('error' => array(
                'code' => 9020,
                'msg'  => 'Payment required'
            ));

            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::PAYMENT_REQUIRED));
        }

        $this->setExpectedException('\TwoFAS\Api\Exception\PaymentException', 'Payment required');

        $this->twoFAS->requestAuthViaSms('+48512256400');
    }

    public function testAuthRequestWithInvalidData()
    {
        $response = array('error' => array(
            'code' => 9030,
            'msg'  => array(
                'totp_secret' => array(
                    'validation.required'
                )
            )
        ));

        $this->setExpectedException('\TwoFAS\Api\Exception\ValidationException', 'Validation exception');

        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::BAD_REQUEST));
        }

        $this->twoFAS->requestAuthViaTotp('');
    }

    public function testAuthenticatingChannel()
    {
        $integrationId = 1;
        $sessionId     = '4b3403665fea6';
        $socketId      = '216020.12250337';

        if ($this->isDevelopmentEnvironment()) {
            $response = array(
                'auth' => 'e4436932665a96ba8ce6:3b1b3bf7865d1a552811aa82525a9c1ab63bff750aacfc118a6d06b32538ca73'
            );

            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::OK));

            $authArray = $this->twoFAS->authenticateChannel($integrationId, $sessionId, $socketId);
            $this->assertArrayHasKey('auth', $authArray);
            $this->assertTrue(is_string($authArray['auth']));
        }
    }

    public function testUpdateChannelStatus()
    {
        $channelName = 'private-wp_33_81fcd7e41d3bcd1ec4181d187197241e';
        $statusId    = 144;
        $status      = 'resolved';

        if ($this->isDevelopmentEnvironment()) {
            $channelStatusResponse = array(
                'id'           => $statusId,
                'channel_name' => $channelName,
                'status'       => $status,
                'created_at'   => $this->getDate()->format('Y-m-d H:i:s')
            );
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($channelStatusResponse), HttpCodes::OK));

            $response = $this->twoFAS->updateChannelStatus($channelName, $statusId, $status);
            $this->assertEquals($channelStatusResponse, $response);
        }
    }

    /**
     * @return array
     */
    private function getNewAuthenticationResponse()
    {
        return array(
            'id'         => uniqid(),
            'created_at' => $this->getDate()->format('Y-m-d H:i:s'),
            'valid_to'   => $this->getDateIn15InFormat()->format('Y-m-d H:i:s')
        );
    }
}