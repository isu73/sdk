<?php

namespace TwoFAS\Api\tests\TwoFAS;

use TwoFAS\Api\Code\AcceptedCode;
use TwoFAS\Api\Code\RejectedCodeCannotRetry;
use TwoFAS\Api\Code\RejectedCodeCanRetry;
use TwoFAS\Api\Exception\ValidationException;
use TwoFAS\Api\HttpCodes;
use TwoFAS\Api\Response\ResponseGenerator;

class BackupCodesTest extends LiveAndMockBase
{
    public function testCheckValidBackupCode()
    {
        $this->createIntegrationUser(array(
            'id'           => uniqid(),
            'phone_number' => '+48512256400',
            'email'        => '',
            'totp_secret'  => ''
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
            'id'           => uniqid(),
            'phone_number' => '+48512256400',
            'email'        => '',
            'totp_secret'  => ''
        ));

        if ($this->isDevelopmentEnvironment()) {
            $response = array('error' => array(
                'code' => 9030,
                'msg'  => array(
                    'code' => array(
                        'validation.backup_code'
                    )
                )
            ));

            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::BAD_REQUEST));
        }

        $this->setExpectedException('\TwoFAS\Api\Exception\ValidationException');

        try {
            $collection = $this->makeAuthenticationCollection(array('5800c43d69b30'));
            $this->twoFAS->checkBackupCode($this->user, $collection, 'ABC!@#');
        } catch (ValidationException $e) {
            $this->assertFalse($e->hasKey('authentications'));
            throw $e;
        }
    }

    public function testCheckBackupCodeCanRetry()
    {
        $this->createIntegrationUser(array(
            'id'           => uniqid(),
            'phone_number' => '+48512256400',
            'email'        => '',
            'totp_secret'  => ''
        ));

        $ids        = array('5800c43d69b30');
        $collection = $this->makeAuthenticationCollection($ids);

        if ($this->isDevelopmentEnvironment()) {
            $checkResponse = array('error' => array(
                'code' => 9061,
                'msg'  => 'Invalid code can retry'
            ));

            $this->httpClient->method('request')->willReturn(
                ResponseGenerator::createFrom(json_encode($checkResponse), HttpCodes::FORBIDDEN)
            );
        }

        $result = $this->twoFAS->checkBackupCode($this->user, $collection, 'aaaa-bbbb-cccc');
        $this->assertEquals(new RejectedCodeCanRetry($collection->getIds()), $result);
    }

    public function testCheckBackupCodeCannotRetry()
    {
        $this->createIntegrationUser(array(
            'id'           => uniqid(),
            'phone_number' => '+48512256400',
            'email'        => '',
            'totp_secret'  => ''
        ));

        $ids        = array('1', '2', '3');
        $collection = $this->makeAuthenticationCollection($ids);

        $response = array('error' => array(
            'code' => 9062,
            'msg'  => 'Invalid code can not retry'
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
            'id'           => uniqid(),
            'phone_number' => '',
            'email'        => '',
            'totp_secret'  => 'PEHMPSDNLXIOG65U'
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
}