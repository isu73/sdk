<?php

namespace TwoFAS\Api\tests\TwoFAS;

use DateTime;
use TwoFAS\Api\Authentication;
use TwoFAS\Api\AuthenticationCollection;
use TwoFAS\Api\BackupCode;
use TwoFAS\Api\BackupCodesCollection;
use TwoFAS\Api\Code\AcceptedCode;
use TwoFAS\Api\Code\RejectedCodeCannotRetry;
use TwoFAS\Api\Code\RejectedCodeCanRetry;
use TwoFAS\Api\Exception\IntegrationUserNotFoundException;
use TwoFAS\Api\Exception\ValidationException;
use TwoFAS\Api\FormattedNumber;
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
        $this->createIntegrationUser(array(
            'id'            => uniqid(),
            'active_method' => 'sms',
            'phone_number'  => '+48603322424',
            'email'         => '',
            'totp_secret'   => ''
        ));

        if ($this->isDevelopmentEnvironment()) {
            $response = array_merge(
                $this->getNewAuthenticationResponse(),
                array('phone_number' => '+48603322424')
            );

            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::OK));
            $this->twoFAS->method('formatNumber')->willReturn(new FormattedNumber($this->user->getPhoneNumber()->phoneNumber()));
            $this->twoFAS->method('getIntegrationUser')->willReturn($this->user);
        }

        $authentication = $this->twoFAS->requestAuth($this->keyStorage, $this->user->getId());
        $this->assertInstanceOf('\TwoFAS\Api\Authentication', $authentication);
        $this->assertNotNull($authentication->id());
        $this->assertNotNull($authentication->createdAt());
        $this->assertNotNull($authentication->validTo());
    }

    public function testAuthRequestViaCall()
    {
        $this->createIntegrationUser(array(
            'id'            => uniqid(),
            'active_method' => 'call',
            'phone_number'  => '+48603322424',
            'email'         => '',
            'totp_secret'   => ''
        ));

        if ($this->isDevelopmentEnvironment()) {
            $response = array_merge(
                $this->getNewAuthenticationResponse(),
                array('phone_number' => '+48603322424')
            );

            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::OK));
            $this->twoFAS->method('formatNumber')->willReturn(new FormattedNumber($this->user->getPhoneNumber()->phoneNumber()));
            $this->twoFAS->method('getIntegrationUser')->willReturn($this->user);
        }

        $authentication = $this->twoFAS->requestAuth($this->keyStorage, $this->user->getId());
        $this->assertInstanceOf('\TwoFAS\Api\Authentication', $authentication);
        $this->assertNotNull($authentication->id());
        $this->assertNotNull($authentication->createdAt());
        $this->assertNotNull($authentication->validTo());
    }

    public function testAuthRequestViaEmail()
    {
        $this->createIntegrationUser(array(
            'id'            => uniqid(),
            'active_method' => 'email',
            'phone_number'  => '',
            'email'         => 'aaa@2fas.com',
            'totp_secret'   => ''
        ));

        if ($this->isDevelopmentEnvironment()) {
            $response = $this->getNewAuthenticationResponse();

            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::OK));
            $this->twoFAS->method('getIntegrationUser')->willReturn($this->user);
        }

        $authentication = $this->twoFAS->requestAuth($this->keyStorage, $this->user->getId());
        $this->assertInstanceOf('\TwoFAS\Api\Authentication', $authentication);
        $this->assertNotNull($authentication->id());
        $this->assertNotNull($authentication->createdAt());
        $this->assertNotNull($authentication->validTo());
    }

    public function testAuthRequestViaTotp()
    {
        $this->createIntegrationUser(array(
            'id'            => uniqid(),
            'active_method' => 'totp',
            'phone_number'  => '',
            'email'         => '',
            'totp_secret'   => 'PEHMPSDNLXIOG65U'
        ));

        if ($this->isDevelopmentEnvironment()) {
            $response = $this->getNewAuthenticationResponse();

            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::OK));
            $this->twoFAS->method('getIntegrationUser')->willReturn($this->user);
        }

        $authentication = $this->twoFAS->requestAuth($this->keyStorage, $this->user->getId());
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

        $this->createIntegrationUser(array(
            'id'            => uniqid(),
            'active_method' => 'totp',
            'phone_number'  => '',
            'email'         => '',
            'totp_secret'   => $totpSecret,
            'mobile_secret' => $mobileSecret
        ));

        if ($this->isDevelopmentEnvironment()) {
            $response = $this->getNewAuthenticationResponse();

            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::OK));
            $this->twoFAS->method('getIntegrationUser')->willReturn($this->user);
        }

        $authentication = $this->twoFAS->requestAuthViaTotpWithMobileSupport(
            $totpSecret,
            $mobileSecret,
            uniqid(),
            'Chrome 56, macOS Sierra'
        );

        $this->assertInstanceOf('\TwoFAS\Api\Authentication', $authentication);
        $this->assertNotNull($authentication->id());
        $this->assertNotNull($authentication->createdAt());
        $this->assertNotNull($authentication->validTo());
    }

    public function testAuthRequestWithNonExistentUserId()
    {
        $this->setExpectedException('\TwoFAS\Api\Exception\IntegrationUserNotFoundException', 'Integration user not found');

        $userId   = 'abc123';
        $response = array('error' => array(
            "code" => 9031,
            "msg"  => "Integration user not found"
        ));

        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::NOT_FOUND));
            $this->twoFAS->method('getIntegrationUser')->will($this->throwException(new IntegrationUserNotFoundException('Integration user not found')));
        }

        $this->twoFAS->requestAuth($this->keyStorage, $userId);
    }

    public function testAuthRequestWithUserWhoHasNoActiveMethod()
    {
        $this->setExpectedException('\TwoFAS\Api\Exception\IntegrationUserHasNoActiveMethodException');

        $this->createIntegrationUser(array(
            'id'            => uniqid(),
            'active_method' => 'email',
            'phone_number'  => '',
            'email'         => 'aaa@2fas.com',
            'totp_secret'   => ''
        ));

        $this->user->setActiveMethod(null);

        if ($this->isDevelopmentEnvironment()) {
            $this->twoFAS->method('getIntegrationUser')->willReturn($this->user);
            $this->twoFAS->method('updateIntegrationUser')->willReturn($this->user);
        }

        $this->twoFAS->updateIntegrationUser($this->keyStorage, $this->user);
        $this->twoFAS->requestAuth($this->keyStorage, $this->user->getId());
    }

    public function testAuthRequestWithInvalidData()
    {
        $response = array('error' => array(
            "code" => 9030,
            "msg"  => array(
                "totp_secret" => array(
                    "validation.required"
                )
            )
        ));

        $this->setExpectedException('\TwoFAS\Api\Exception\ValidationException', 'Validation exception');

        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::BAD_REQUEST));
        }

        $this->twoFAS->requestAuthViaTotp("");
    }

    public function testAuthRequestViaSmsOnInactiveChannel()
    {
        $this->setExpectedException('\TwoFAS\Api\Exception\ChannelNotActiveException');
        $this->setUpTwoFAS(getenv('second_login'), getenv('second_key'), $this->mockedMethods);

        $this->createIntegrationUser(array(
            'id'            => uniqid(),
            'active_method' => 'totp',
            'phone_number'  => '',
            'email'         => '',
            'totp_secret'   => 'PEHMPSDNLXIOG65U'
        ));

        $response = array('error' => array(
            "code" => 9014,
            "msg"  => "Channel is not active"
        ));

        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::FORBIDDEN));
        }

        $this->twoFAS->requestAuthViaSms("+48603322424");
    }

    public function testAuthenticationCollection()
    {
        $ids        = array('1', '2', '3');
        $collection = $this->makeAuthenticationCollection($ids);

        $this->assertEquals($ids, $collection->getIds());
    }

    public function testBackupCodesCollection()
    {
        $codes      = $this->getBackupCodesArray();
        $collection = new BackupCodesCollection();
        foreach ($codes as $code) {
            $collection->add(new BackupCode($code));
        }

        $this->assertEquals($codes, $collection->getCodes());
    }

    public function testCheckValidCode()
    {
        $ids        = array('5800c43d69e60', '5800c43d69e70');
        $collection = $this->makeAuthenticationCollection($ids);

        $expectedResponse = new AcceptedCode($collection->getIds());

        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode(array()), HttpCodes::NO_CONTENT));
        }

        $actualResponse = $this->twoFAS->checkCode($collection, "123456");
        $this->assertEquals($expectedResponse, $actualResponse);
    }

    public function testCheckInvalidCodeFormat()
    {
        $this->setExpectedException('\TwoFAS\Api\Exception\ValidationException');

        $response = array('error' => array(
            "code" => 9030,
            "msg"  => array(
                "code" => array(
                    "validation.digits"
                )
            )
        ));

        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::BAD_REQUEST));
        }

        try {
            $collection = $this->makeAuthenticationCollection(array('5800c43d69e80'));
            $this->twoFAS->checkCode($collection, "ABC!@#");
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
            "code" => 9061,
            "msg"  => "Invalid code can retry"
        ));

        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::FORBIDDEN));
        }

        $result = $this->twoFAS->checkCode($collection, "654321");
        $this->assertEquals(new RejectedCodeCanRetry($collection->getIds()), $result);
    }

    public function testCheckCodeCannotRetry()
    {
        $ids        = array('1', '2', '3');
        $collection = $this->makeAuthenticationCollection($ids);

        $response = array('error' => array(
            "code" => 9062,
            "msg"  => "Invalid code can not retry"
        ));

        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::FORBIDDEN));
        }

        $result = $this->twoFAS->checkCode($collection, "123456");
        $this->assertEquals(new RejectedCodeCannotRetry($collection->getIds()), $result);
    }

    public function testCheckValidBackupCode()
    {
        $this->createIntegrationUser(array(
            'id'            => uniqid(),
            'active_method' => 'sms',
            'phone_number'  => '+48603322424',
            'email'         => '',
            'totp_secret'   => ''
        ));

        $ids              = array('5800c43d69b10', '5800c43d69b20');
        $collection       = $this->makeAuthenticationCollection($ids);
        $expectedResponse = new AcceptedCode($collection->getIds());

        if ($this->isDevelopmentEnvironment()) {
            $response = array(
                'codes' => $this->getBackupCodesArray()
            );

            $this->httpClient->method('request')->willReturnOnConsecutiveCalls(
                ResponseGenerator::createFrom(json_encode($response), HttpCodes::OK),
                ResponseGenerator::createFrom(json_encode(array()), HttpCodes::NO_CONTENT)
            );
        }

        $backupCodes = $this->twoFAS->regenerateBackupCodes($this->user);
        $codes       = $backupCodes->getCodes();
        $code        = array_pop($codes);

        $actualResponse = $this->twoFAS->checkBackupCode($this->user, $collection, $code);
        $this->assertEquals($expectedResponse, $actualResponse);
    }

    public function testCheckInvalidBackupCodeFormat()
    {
        $this->createIntegrationUser(array(
            'id'            => uniqid(),
            'active_method' => 'sms',
            'phone_number'  => '+48603322424',
            'email'         => '',
            'totp_secret'   => ''
        ));

        if ($this->isDevelopmentEnvironment()) {
            $response = array('error' => array(
                "code" => 9030,
                "msg"  => array(
                    "code" => array(
                        "validation.backup_code"
                    )
                )
            ));

            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::BAD_REQUEST));
        }

        $this->setExpectedException('\TwoFAS\Api\Exception\ValidationException');

        try {
            $collection = $this->makeAuthenticationCollection(array('5800c43d69b30'));
            $this->twoFAS->checkBackupCode($this->user, $collection, "ABC!@#");
        } catch (ValidationException $e) {
            $this->assertFalse($e->hasKey('authentications'));
            throw $e;
        }
    }

    public function testCheckBackupCodeCanRetry()
    {
        $this->createIntegrationUser(array(
            'id'            => uniqid(),
            'active_method' => 'sms',
            'phone_number'  => '+48603322424',
            'email'         => '',
            'totp_secret'   => ''
        ));

        $ids        = array('5800c43d69b30');
        $collection = $this->makeAuthenticationCollection($ids);

        if ($this->isDevelopmentEnvironment()) {
            $checkResponse = array('error' => array(
                "code" => 9061,
                "msg"  => "Invalid code can retry"
            ));

            $this->httpClient->method('request')->willReturn(
                ResponseGenerator::createFrom(json_encode($checkResponse), HttpCodes::FORBIDDEN)
            );
        }

        $result = $this->twoFAS->checkBackupCode($this->user, $collection, "aaaa-bbbb-cccc");
        $this->assertEquals(new RejectedCodeCanRetry($collection->getIds()), $result);
    }

    public function testCheckBackupCodeCannotRetry()
    {
        $this->createIntegrationUser(array(
            'id'            => uniqid(),
            'active_method' => 'sms',
            'phone_number'  => '+48603322424',
            'email'         => '',
            'totp_secret'   => ''
        ));

        $ids        = array('1', '2', '3');
        $collection = $this->makeAuthenticationCollection($ids);

        $response = array('error' => array(
            "code" => 9062,
            "msg"  => "Invalid code can not retry"
        ));

        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::FORBIDDEN));
        }

        $result = $this->twoFAS->checkBackupCode($this->user, $collection, 'aaaa-bbbb-cccc');
        $this->assertEquals(new RejectedCodeCannotRetry($collection->getIds()), $result);
    }

    public function testGenerateNewBackupCodes()
    {
        $this->createIntegrationUser(array(
            'id'            => uniqid(),
            'active_method' => 'totp',
            'phone_number'  => '',
            'email'         => '',
            'totp_secret'   => 'PEHMPSDNLXIOG65U'
        ));

        if ($this->isDevelopmentEnvironment()) {
            $response = array(
                'codes' => $this->getBackupCodesArray()
            );

            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::OK));
        }

        $backupCodes = $this->twoFAS->regenerateBackupCodes($this->user);
        $this->assertInstanceOf('\TwoFAS\Api\BackupCodesCollection', $backupCodes);
        $this->assertCount(5, $backupCodes->getCodes());
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
    private function getBackupCodesArray()
    {
        return array(
            'yx5w-Xhui-JzMN',
            'kAec-t6PD-eIsL',
            '5au1-IOiH-ksBq',
            '5au1-t6PD-ksBq',
            '5au1-Xhui-ksBq'
        );
    }

    /**
     * @param array $ids
     *
     * @return AuthenticationCollection
     */
    private function makeAuthenticationCollection(array $ids)
    {
        $collection = new AuthenticationCollection();

        foreach ($ids as $id) {
            $collection->add(new Authentication($id, $this->getDate(), $this->getDateIn15InFormat()));
        }

        return $collection;
    }

    private function getNewAuthenticationResponse()
    {
        return array(
            'id'         => uniqid(),
            'created_at' => $this->getDate()->format('Y-m-d H:i:s'),
            'valid_to'   => $this->getDateIn15InFormat()->format('Y-m-d H:i:s')
        );
    }

    /**
     * @return DateTime
     */
    private function getDate()
    {
        return new DateTime();
    }

    /**
     * @return DateTime
     */
    private function getDateIn15InFormat()
    {
        $time = time() + 60 * 15;

        $date = new DateTime();

        return $date->setTimestamp($time);
    }
}