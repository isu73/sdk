<?php

namespace TwoFAS\Api\Sdk;

use TwoFAS\Api\HttpCodes;

class TwoFASTest extends LiveAndMockBase
{
    public function testResponse()
    {
        if ($this->isDevelopmentEnvironment()) {
            $this->nextApiCallWillReturn([], HttpCodes::OK);
        }

        $response = $this->httpClient->request('GET', $this->baseUrl, 'token');
        $this->assertInstanceOf('\TwoFAS\Api\Response\Response', $response);
    }

    public function testAuthRequestViaSms()
    {
        $phoneNumber = '+48512256400';

        if ($this->isDevelopmentEnvironment()) {
            $response = array_merge(
                $this->getNewAuthenticationResponse(),
                ['phone_number' => $phoneNumber]
            );

            $this->nextApiCallWillReturn($response, HttpCodes::CREATED);
        }

        $authentication = $this->sdk->requestAuthViaSms($phoneNumber);
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
                ['phone_number' => $phoneNumber]
            );

            $this->nextApiCallWillReturn($response, HttpCodes::CREATED);
        }

        $authentication = $this->sdk->requestAuthViaCall($phoneNumber);
        $this->assertInstanceOf('\TwoFAS\Api\Authentication', $authentication);
        $this->assertNotNull($authentication->id());
        $this->assertNotNull($authentication->createdAt());
        $this->assertNotNull($authentication->validTo());
    }

    public function testAuthRequestViaEmail()
    {
        if ($this->isDevelopmentEnvironment()) {
            $response = $this->getNewAuthenticationResponse();

            $this->nextApiCallWillReturn($response, HttpCodes::CREATED);
        }

        $authentication = $this->sdk->requestAuthViaEmail('aaa@2fas.com');
        $this->assertInstanceOf('\TwoFAS\Api\Authentication', $authentication);
        $this->assertNotNull($authentication->id());
        $this->assertNotNull($authentication->createdAt());
        $this->assertNotNull($authentication->validTo());
    }

    public function testAuthRequestViaTotp()
    {
        if ($this->isDevelopmentEnvironment()) {
            $response = $this->getNewAuthenticationResponse();

            $this->nextApiCallWillReturn($response, HttpCodes::CREATED);
        }

        $authentication = $this->sdk->requestAuthViaTotp('PEHMPSDNLXIOG65U');
        $this->assertInstanceOf('\TwoFAS\Api\Authentication', $authentication);
        $this->assertNotNull($authentication->id());
        $this->assertNotNull($authentication->createdAt());
        $this->assertNotNull($authentication->validTo());
    }

    public function testAuthRequestViaTotpWithMobileSupport()
    {
        $this->setUpTwoFAS(getenv('second_oauth_token'), $this->mockedMethods);

        $totpSecret = 'PEHMPSDNLXIOG65U';
        $pushId     = '9e3d5538259e283b2e6f3ecb29a0d269';

        if ($this->isDevelopmentEnvironment()) {
            $response = $this->getNewAuthenticationResponse();

            $this->nextApiCallWillReturn($response, HttpCodes::CREATED);
        }

        $authentication = $this->sdk->requestAuthViaTotpWithMobileSupport(
            $totpSecret,
            $pushId,
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
        $this->setUpTwoFAS(getenv('second_oauth_token'), $this->mockedMethods);

        if ($this->isDevelopmentEnvironment()) {
            $response = ['error' => [
                'code' => 9020,
                'msg'  => 'Payment required'
            ]];

            $this->nextApiCallWillReturn($response, HttpCodes::PAYMENT_REQUIRED);
        }

        $this->setExpectedException('\TwoFAS\Api\Exception\PaymentException', 'Payment required');

        $this->sdk->requestAuthViaSms('+48512256400');
    }

    public function testAuthRequestWithInvalidData()
    {
        $response = ['error' => [
            'code' => 9030,
            'msg'  => [
                'totp_secret' => [
                    'validation.required'
                ]
            ]
        ]];

        $this->setExpectedException('\TwoFAS\Api\Exception\ValidationException', 'Validation exception');

        if ($this->isDevelopmentEnvironment()) {
            $this->nextApiCallWillReturn($response, HttpCodes::BAD_REQUEST);
        }

        $this->sdk->requestAuthViaTotp('');
    }

    public function testAuthenticatingChannel()
    {
        $integrationId = 1;
        $sessionId     = '4b3403665fea6';
        $socketId      = '216020.12250337';

        if ($this->isDevelopmentEnvironment()) {
            $response = [
                'auth' => 'e4436932665a96ba8ce6:3b1b3bf7865d1a552811aa82525a9c1ab63bff750aacfc118a6d06b32538ca73'
            ];

            $this->nextApiCallWillReturn($response, HttpCodes::OK);

            $authArray = $this->sdk->authenticateChannel($integrationId, $sessionId, $socketId);
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
            $channelStatusResponse = [
                'id'           => $statusId,
                'channel_name' => $channelName,
                'status'       => $status,
                'created_at'   => $this->getDate()->format('Y-m-d H:i:s')
            ];
            $this->nextApiCallWillReturn($channelStatusResponse, HttpCodes::OK);

            $response = $this->sdk->updateChannelStatus($channelName, $statusId, $status);
            $this->assertEquals($channelStatusResponse, $response);
        }
    }

    /**
     * @return array
     */
    private function getNewAuthenticationResponse()
    {
        return [
            'id'         => uniqid(),
            'created_at' => $this->getDate()->format('Y-m-d H:i:s'),
            'valid_to'   => $this->getDateIn15InFormat()->format('Y-m-d H:i:s')
        ];
    }
}
