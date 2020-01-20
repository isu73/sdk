<?php

namespace TwoFAS\Api\Sdk;

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
        $this->createIntegrationUser([
            'id'           => uniqid(),
            'phone_number' => '+48512256400',
            'email'        => '',
            'totp_secret'  => ''
        ]);

        $ids              = ['5800c43d69b10', '5800c43d69b20'];
        $expectedResponse = new AcceptedCode($ids);

        if ($this->isDevelopmentEnvironment()) {
            $response = [
                'codes' => $this->getBackupCodesArray()
            ];

            $this->httpClient->method('request')->willReturnOnConsecutiveCalls(
                ResponseGenerator::createFrom(json_encode($response), HttpCodes::OK),
                ResponseGenerator::createFrom(json_encode([]), HttpCodes::NO_CONTENT)
            );
        }

        $backupCodes = $this->sdk->regenerateBackupCodes($this->user);
        $codes       = $backupCodes->getCodes();
        $code        = array_pop($codes);

        $actualResponse = $this->sdk->checkBackupCode($this->user, $ids, $code);
        $this->assertEquals($expectedResponse, $actualResponse);
    }

    public function testCheckInvalidBackupCodeFormat()
    {
        $this->createIntegrationUser([
            'id'           => uniqid(),
            'phone_number' => '+48512256400',
            'email'        => '',
            'totp_secret'  => ''
        ]);

        if ($this->isDevelopmentEnvironment()) {
            $response = ['error' => [
                'code' => 9030,
                'msg'  => [
                    'code' => [
                        'validation.backup_code'
                    ]
                ]
            ]];

            $this->nextApiCallWillReturn($response, HttpCodes::BAD_REQUEST);
        }

        $this->setExpectedException('\TwoFAS\Api\Exception\ValidationException');

        try {
            $this->sdk->checkBackupCode($this->user, ['5800c43d69b30'], 'ABC!@#');
        } catch (ValidationException $e) {
            $this->assertFalse($e->hasKey('authentications'));
            throw $e;
        }
    }

    public function testCheckBackupCodeCanRetry()
    {
        $this->createIntegrationUser([
            'id'           => uniqid(),
            'phone_number' => '+48512256400',
            'email'        => '',
            'totp_secret'  => ''
        ]);

        $ids = ['5800c43d69b30'];

        if ($this->isDevelopmentEnvironment()) {
            $checkResponse = ['error' => [
                'code' => 9061,
                'msg'  => 'Invalid code can retry'
            ]];

            $this->nextApiCallWillReturn($checkResponse, HttpCodes::FORBIDDEN);
        }

        $result = $this->sdk->checkBackupCode($this->user, $ids, 'aaaa-bbbb-cccc');
        $this->assertEquals(new RejectedCodeCanRetry($ids), $result);
    }

    public function testCheckBackupCodeCannotRetry()
    {
        $this->createIntegrationUser([
            'id'           => uniqid(),
            'phone_number' => '+48512256400',
            'email'        => '',
            'totp_secret'  => ''
        ]);

        $ids = ['1', '2', '3'];

        $response = ['error' => [
            'code' => 9062,
            'msg'  => 'Invalid code can not retry'
        ]];

        if ($this->isDevelopmentEnvironment()) {
            $this->nextApiCallWillReturn($response, HttpCodes::FORBIDDEN);
        }

        $result = $this->sdk->checkBackupCode($this->user, $ids, 'aaaa-bbbb-cccc');
        $this->assertEquals(new RejectedCodeCannotRetry($ids), $result);
    }

    public function testGenerateNewBackupCodes()
    {
        $this->createIntegrationUser([
            'id'           => uniqid(),
            'phone_number' => '',
            'email'        => '',
            'totp_secret'  => 'PEHMPSDNLXIOG65U'
        ]);

        if ($this->isDevelopmentEnvironment()) {
            $response = [
                'codes' => $this->getBackupCodesArray()
            ];

            $this->nextApiCallWillReturn($response, HttpCodes::OK);
        }

        $backupCodes = $this->sdk->regenerateBackupCodes($this->user);
        $this->assertInstanceOf('\TwoFAS\Api\BackupCodesCollection', $backupCodes);
        $this->assertCount(5, $backupCodes->getCodes());
    }
}