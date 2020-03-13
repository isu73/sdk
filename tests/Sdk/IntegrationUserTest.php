<?php

namespace TwoFAS\Api\Sdk;

use TwoFAS\Api\Exception\Exception as ApiException;
use TwoFAS\Api\FormattedNumber;
use TwoFAS\Api\HttpCodes;
use TwoFAS\Api\IntegrationUser;

class IntegrationUserTest extends LiveAndMockBase
{
    protected $mockedMethods = ['formatNumber'];

    public function testSetValues()
    {
        $id               = 123;
        $externalId       = 321;
        $phoneNumber      = '+48600700800';
        $totpSecret       = 'PEHMPSDNLXIOG65U';
        $email            = 'aaa@2fas.com';
        $backupCodesCount = 3;

        $user = new IntegrationUser();
        $user
            ->setId($id)
            ->setExternalId($externalId)
            ->setPhoneNumber($phoneNumber)
            ->setTotpSecret($totpSecret)
            ->setEmail($email)
            ->setBackupCodesCount($backupCodesCount);

        $this->assertEquals($id, $user->getId());
        $this->assertEquals($externalId, $user->getExternalId());
        $this->assertEquals($phoneNumber, $user->getPhoneNumber()->phoneNumber());
        $this->assertEquals($totpSecret, $user->getTotpSecret());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($backupCodesCount, $user->getBackupCodesCount());
    }

    public function testSetNullValues()
    {
        $externalId  = 321;
        $phoneNumber = '+48600700800';
        $totpSecret  = 'PEHMPSDNLXIOG65U';
        $email       = 'aaa@2fas.com';

        $user = new IntegrationUser();
        $user
            ->setExternalId($externalId)
            ->setPhoneNumber($phoneNumber)
            ->setTotpSecret($totpSecret)
            ->setEmail($email);

        $user
            ->setExternalId(null)
            ->setPhoneNumber(null)
            ->setTotpSecret(null)
            ->setEmail(null);

        $this->assertNull($user->getExternalId());
        $this->assertNull($user->getPhoneNumber()->phoneNumber());
        $this->assertNull($user->getTotpSecret());
        $this->assertNull($user->getEmail());
    }

    public function testEmptyStringValues()
    {
        $externalId  = 321;
        $phoneNumber = '+48600700800';
        $totpSecret  = 'PEHMPSDNLXIOG65U';
        $email       = 'aaa@2fas.com';

        $user = new IntegrationUser();
        $user
            ->setExternalId($externalId)
            ->setPhoneNumber($phoneNumber)
            ->setTotpSecret($totpSecret)
            ->setEmail($email);

        $user
            ->setExternalId('')
            ->setPhoneNumber('')
            ->setTotpSecret('')
            ->setEmail('');

        $this->assertNull($user->getExternalId());
        $this->assertNull($user->getPhoneNumber()->phoneNumber());
        $this->assertNull($user->getTotpSecret());
        $this->assertNull($user->getEmail());
    }

    public function testCastToString()
    {
        $user = new IntegrationUser();
        $user
            ->setId(123)
            ->setExternalId(123)
            ->setTotpSecret(123)
            ->setEmail(123);

        $this->assertInternalType('string', $user->getId());
        $this->assertInternalType('string', $user->getExternalId());
        $this->assertInternalType('string', $user->getTotpSecret());
        $this->assertInternalType('string', $user->getEmail());
    }

    public function testSetEmptyId()
    {
        $this->setExpectedException('InvalidArgumentException', 'Id of Integration User should not be empty');
        $user = new IntegrationUser();
        $user->setId(null);
    }

    public function testSetBackupCodesCount()
    {
        $this->setExpectedException('InvalidArgumentException', 'Backup Codes count should be a number');
        $user = new IntegrationUser();
        $user->setBackupCodesCount(null);
    }

    public function testAddIntegrationUser()
    {
        $id         = uniqid();
        $externalId = 'my_database_id_' . uniqid();

        $user = new IntegrationUser();
        $user
            ->setExternalId($externalId)
            ->setPhoneNumber('+48500600700')
            ->setEmail('aaa@2fas.com')
            ->setTotpSecret('PEHMPSDNLXIOG65U')
            ->setBackupCodesCount(0);

        if ($this->isDevelopmentEnvironment()) {
            $response = [
                'id'                 => $id,
                'external_id'        => $externalId,
                'phone_number'       => $user->getPhoneNumber()->phoneNumber(),
                'email'              => $user->getEmail(),
                'totp_secret'        => $user->getTotpSecret(),
                'backup_codes_count' => $user->getBackupCodesCount()
            ];
            $this->nextApiCallWillReturn($response, HttpCodes::CREATED);
            $this->sdk->method('formatNumber')->willReturn(new FormattedNumber($user->getPhoneNumber()->phoneNumber()));
        }

        $addedUser = $this->sdk->addIntegrationUser($this->keyStorage, $user);
        $this->assertInstanceOf('\TwoFAS\Api\IntegrationUser', $addedUser);

        if ($this->isDevelopmentEnvironment()) {
            $this->assertEquals($id, $addedUser->getId());
        }

        $this->assertEquals($user->getExternalId(), $addedUser->getExternalId());
        $this->assertEquals($user->getPhoneNumber()->phoneNumber(), $addedUser->getPhoneNumber()->phoneNumber());
        $this->assertEquals($user->getEmail(), $addedUser->getEmail());
        $this->assertEquals($user->getTotpSecret(), $addedUser->getTotpSecret());
        $this->assertEquals($user->getBackupCodesCount(), $addedUser->getBackupCodesCount());

        return $user;
    }

    public function testAddIntegrationUserWithInvalidData()
    {
        if ($this->isDevelopmentEnvironment()) {
            $data = ['error' => [
                'code' => 9030,
                'msg'  => [
                    'push_id' => [
                        'validation.size.string'
                    ]
                ]
            ]];
            $this->nextApiCallWillReturn($data, HttpCodes::BAD_REQUEST);
        }

        $this->setExpectedException('\TwoFAS\Api\Exception\ValidationException');

        $user = new IntegrationUser();
        $user->setMobileSecret('invalid');

        $this->sdk->addIntegrationUser($this->keyStorage, $user);
    }

    public function testAddIntegrationUserWithNonProvidedPhoneNumber()
    {
        $user = new IntegrationUser();
        $user->setTotpSecret('PEHMPSDNLXIOG65U');

        if ($this->isDevelopmentEnvironment()) {
            $id       = uniqid();
            $response = [
                'id'                 => $id,
                'external_id'        => null,
                'phone_number'       => null,
                'email'              => null,
                'totp_secret'        => $user->getTotpSecret(),
                'backup_codes_count' => 0
            ];
            $this->nextApiCallWillReturn($response, HttpCodes::CREATED);
        }

        $addedUser = $this->sdk->addIntegrationUser($this->keyStorage, $user);
        $this->assertNull($addedUser->getPhoneNumber()->phoneNumber());
        $this->assertEquals(0, $addedUser->getBackupCodesCount());
    }

    public function testAddIntegrationUserWithEmptyPhoneNumber()
    {
        $user = new IntegrationUser();
        $user
            ->setPhoneNumber('')
            ->setEmail('')
            ->setTotpSecret('PEHMPSDNLXIOG65U');

        if ($this->isDevelopmentEnvironment()) {
            $id       = uniqid();
            $response = [
                'id'                 => $id,
                'phone_number'       => null,
                'email'              => null,
                'totp_secret'        => $user->getTotpSecret(),
                'backup_codes_count' => 0
            ];
            $this->nextApiCallWillReturn($response, HttpCodes::CREATED);
        }

        $addedUser = $this->sdk->addIntegrationUser($this->keyStorage, $user);
        $this->assertNull($addedUser->getPhoneNumber()->phoneNumber());
    }

    /**
     * @param IntegrationUser $user
     *
     * @depends testAddIntegrationUser
     *
     * @return IntegrationUser
     * @throws ApiException
     *
     */
    public function testUpdateIntegrationUser(IntegrationUser $user)
    {
        $user
            ->setPhoneNumber('+48500200300')
            ->setEmail('aaa@2fas.com')
            ->setTotpSecret('PEHMPSDNLXIOG666')
            ->setBackupCodesCount(0)
            ->setHasMobileUser(false);

        $response = [
            'id'                 => $user->getId(),
            'external_id'        => $user->getExternalId(),
            'phone_number'       => $user->getPhoneNumber()->phoneNumber(),
            'email'              => $user->getEmail(),
            'totp_secret'        => $user->getTotpSecret(),
            'backup_codes_count' => $user->getBackupCodesCount()
        ];

        if ($this->isDevelopmentEnvironment()) {
            $this->nextApiCallWillReturn($response, HttpCodes::OK);
            $this->sdk->method('formatNumber')->willReturn(new FormattedNumber($user->getPhoneNumber()->phoneNumber()));
        }

        $updatedUser = $this->sdk->updateIntegrationUser($this->keyStorage, $user);

        $this->assertInstanceOf('\TwoFAS\Api\IntegrationUser', $updatedUser);
        $this->assertEquals($response['id'], $updatedUser->getId());
        $this->assertEquals($response['phone_number'], $updatedUser->getPhoneNumber()->phoneNumber());
        $this->assertEquals($response['totp_secret'], $updatedUser->getTotpSecret());
        $this->assertEquals($response['backup_codes_count'], $updatedUser->getBackupCodesCount());
        $this->assertFalse($updatedUser->hasMobileUser());

        return $updatedUser;
    }

    /**
     * @param IntegrationUser $user
     *
     * @depends testAddIntegrationUser
     *
     * @throws ApiException
     */
    public function testUpdateIntegrationUserWithInvalidData(IntegrationUser $user)
    {
        if ($this->isDevelopmentEnvironment()) {
            $data = ['error' => [
                'code' => 9030,
                'msg'  => [
                    'push_id' => [
                        'validation.size.string'
                    ]
                ]
            ]];
            $this->sdk->method('formatNumber')->willReturn(new FormattedNumber(null));
            $this->nextApiCallWillReturn($data, HttpCodes::BAD_REQUEST);
        }

        $this->setExpectedException('\TwoFAS\Api\Exception\ValidationException');

        $user->setMobileSecret('invalid_method');
        $this->sdk->updateIntegrationUser($this->keyStorage, $user);
    }

    public function testGetIntegrationUsers()
    {
        $expectedStructure = [
            'total'        => 1,
            'per_page'     => 50,
            'current_page' => 1,
            'last_page'    => 1,
            'from'         => 1,
            'to'           => 1,
            'data'         => [
                [],
                [],
                []
            ]
        ];

        if ($this->isDevelopmentEnvironment()) {
            $this->nextApiCallWillReturn($expectedStructure, HttpCodes::OK);
        }

        $response = $this->sdk->getIntegrationUsers();

        $this->assertEquals($this->sortedArrayKeys($expectedStructure), $this->sortedArrayKeys($response));
        $this->assertCount(3, $response['data']);
    }

    public function testCannotGetIntegrationUsersWhenUseInvalidPage()
    {
        $this->setExpectedException('InvalidArgumentException', 'Page number is not valid.');

        $this->sdk->getIntegrationUsers('foobar');
    }

    /**
     * @param IntegrationUser $user
     *
     * @depends testUpdateIntegrationUser
     */
    public function testGetIntegrationUser(IntegrationUser $user)
    {
        $response = [
            'id'                 => uniqid(),
            'external_id'        => null,
            'phone_number'       => 'ZWZ6dExNUktRZExjMXVHcXVKcjdBdz09:RjQ5ToYrddzUWTqREMMJMA==',
            'email'              => 'dzdrMkxOMk9pa2JjUlR5d1YyUnVuUT09:lxbNXqw7/60nlSewHKmB4w==',
            'totp_secret'        => 'UWdWdU9ZSTJIWjBkSVJTYkRWN1hYN0RqVi9qd21mMjF3UlZFNGF4d092UT0=:P95fmMxZVcWVwpAsK3q3uA==',
            'push_id'            => uniqid('', true),
            'has_mobile_user'    => false,
            'mobile_user_id'     => null,
            'backup_codes_count' => 0
        ];

        if ($this->isDevelopmentEnvironment()) {
            $this->nextApiCallWillReturn($response, HttpCodes::OK);
        }

        $responseUser = $this->sdk->getIntegrationUser($this->keyStorage, $user->getId());
        $this->assertInstanceOf('\TwoFAS\Api\IntegrationUser', $responseUser);

        if ($this->isDevelopmentEnvironment()) {
            $this->assertEquals($response['id'], $responseUser->getId());
            $this->assertEquals($response['external_id'], $responseUser->getExternalId());
        }

        $this->assertEquals('+48500200300', $responseUser->getPhoneNumber()->phoneNumber());
        $this->assertEquals('aaa@2fas.com', $responseUser->getEmail());
        $this->assertEquals('PEHMPSDNLXIOG666', $responseUser->getTotpSecret());
        $this->assertFalse($responseUser->hasMobileUser());
        $this->assertEquals(0, $responseUser->getBackupCodesCount());
    }

    /**
     * @param IntegrationUser $user
     *
     * @depends testUpdateIntegrationUser
     */
    public function testGetIntegrationUserByExternalId(IntegrationUser $user)
    {
        $response = [
            'id'                 => uniqid(),
            'external_id'        => 'my_database_id_' . uniqid(),
            'phone_number'       => 'ZWZ6dExNUktRZExjMXVHcXVKcjdBdz09:RjQ5ToYrddzUWTqREMMJMA==',
            'email'              => 'dzdrMkxOMk9pa2JjUlR5d1YyUnVuUT09:lxbNXqw7/60nlSewHKmB4w==',
            'totp_secret'        => 'UWdWdU9ZSTJIWjBkSVJTYkRWN1hYN0RqVi9qd21mMjF3UlZFNGF4d092UT0=:P95fmMxZVcWVwpAsK3q3uA==',
            'push_id'            => uniqid('', true),
            'has_mobile_user'    => false,
            'mobile_user_id'     => null,
            'backup_codes_count' => 0
        ];

        if ($this->isDevelopmentEnvironment()) {
            $this->nextApiCallWillReturn($response, HttpCodes::OK);
        }

        $responseUser = $this->sdk->getIntegrationUserByExternalId($this->keyStorage, $user->getExternalId());
        $this->assertInstanceOf('\TwoFAS\Api\IntegrationUser', $responseUser);

        if ($this->isDevelopmentEnvironment()) {
            $this->assertEquals($response['id'], $responseUser->getId());
            $this->assertEquals($response['external_id'], $responseUser->getExternalId());
        }

        $this->assertEquals($response['backup_codes_count'], $responseUser->getBackupCodesCount());
        $this->assertEquals('+48500200300', $responseUser->getPhoneNumber()->phoneNumber());
        $this->assertEquals('aaa@2fas.com', $responseUser->getEmail());
        $this->assertEquals('PEHMPSDNLXIOG666', $responseUser->getTotpSecret());
        $this->assertFalse($responseUser->hasMobileUser());
    }

    /**
     * @param IntegrationUser $user
     *
     * @depends testAddIntegrationUser
     *
     * @throws ApiException
     */
    public function testDeleteIntegrationUser(IntegrationUser $user)
    {
        if ($this->isDevelopmentEnvironment()) {
            $this->nextApiCallWillReturn([], HttpCodes::NO_CONTENT);
        }

        $response = $this->sdk->deleteIntegrationUser($user->getId());
        $this->assertTrue($response);
    }

    public function testDeleteNonExistentUser()
    {
        if ($this->isDevelopmentEnvironment()) {
            $data = ['error' => [
                'code' => 9080,
                'msg'  => 'Resource not found'
            ]];

            $this->nextApiCallWillReturn($data, HttpCodes::NOT_FOUND);
        }

        $this->setExpectedException('\TwoFAS\Api\Exception\ResourceNotFoundException');
        $this->sdk->deleteIntegrationUser('abc123');
    }

    /**
     * @param array $data
     *
     * @return array
     */
    private function sortedArrayKeys(array $data)
    {
        $data = array_keys($data);
        sort($data);

        return $data;
    }
}
