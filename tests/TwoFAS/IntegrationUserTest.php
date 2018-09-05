<?php

namespace TwoFAS\Api\tests\TwoFAS;

use TwoFAS\Api\FormattedNumber;
use TwoFAS\Api\HttpCodes;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\Api\Response\ResponseGenerator;

class IntegrationUserTest extends LiveAndMockBase
{
    protected $mockedMethods = array('formatNumber');

    public function testSetValues()
    {
        $id               = 123;
        $externalId       = 321;
        $phoneNumber      = '+48600700800';
        $totpSecret       = 'PEHMPSDNLXIOG65U';
        $email            = 'aaa@2fas.com';
        $activeMethod     = 'totp';
        $backupCodesCount = 3;

        $user = new IntegrationUser();
        $user
            ->setId($id)
            ->setExternalId($externalId)
            ->setPhoneNumber($phoneNumber)
            ->setTotpSecret($totpSecret)
            ->setEmail($email)
            ->setActiveMethod($activeMethod)
            ->setBackupCodesCount($backupCodesCount);

        $this->assertEquals($id, $user->getId());
        $this->assertEquals($externalId, $user->getExternalId());
        $this->assertEquals($phoneNumber, $user->getPhoneNumber()->phoneNumber());
        $this->assertEquals($totpSecret, $user->getTotpSecret());
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($activeMethod, $user->getActiveMethod());
        $this->assertEquals($backupCodesCount, $user->getBackupCodesCount());
    }

    public function testSetNullValues()
    {
        $externalId   = 321;
        $mobileUserId = 111;
        $phoneNumber  = '+48600700800';
        $totpSecret   = 'PEHMPSDNLXIOG65U';
        $email        = 'aaa@2fas.com';
        $activeMethod = 'totp';

        $user = new IntegrationUser();
        $user
            ->setExternalId($externalId)
            ->setPhoneNumber($phoneNumber)
            ->setTotpSecret($totpSecret)
            ->setEmail($email)
            ->setActiveMethod($activeMethod);

        $user
            ->setExternalId(null)
            ->setPhoneNumber(null)
            ->setTotpSecret(null)
            ->setEmail(null)
            ->setActiveMethod(null);

        $this->assertNull($user->getExternalId());
        $this->assertNull($user->getPhoneNumber()->phoneNumber());
        $this->assertNull($user->getTotpSecret());
        $this->assertNull($user->getEmail());
        $this->assertNull($user->getActiveMethod());
    }

    public function testEmptyStringValues()
    {
        $externalId   = 321;
        $phoneNumber  = '+48600700800';
        $totpSecret   = 'PEHMPSDNLXIOG65U';
        $email        = 'aaa@2fas.com';
        $activeMethod = 'totp';

        $user = new IntegrationUser();
        $user
            ->setExternalId($externalId)
            ->setPhoneNumber($phoneNumber)
            ->setTotpSecret($totpSecret)
            ->setEmail($email)
            ->setActiveMethod($activeMethod);

        $user
            ->setExternalId("")
            ->setPhoneNumber("")
            ->setTotpSecret("")
            ->setEmail("")
            ->setActiveMethod("");

        $this->assertNull($user->getExternalId());
        $this->assertNull($user->getPhoneNumber()->phoneNumber());
        $this->assertNull($user->getTotpSecret());
        $this->assertNull($user->getEmail());
        $this->assertNull($user->getActiveMethod());
    }


    public function testCastToString()
    {
        $user = new IntegrationUser();
        $user
            ->setId(123)
            ->setExternalId(123)
            ->setActiveMethod(123)
            ->setTotpSecret(123)
            ->setEmail(123);

        $this->assertInternalType("string", $user->getId());
        $this->assertInternalType("string", $user->getExternalId());
        $this->assertInternalType("string", $user->getActiveMethod());
        $this->assertInternalType("string", $user->getTotpSecret());
        $this->assertInternalType("string", $user->getEmail());
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
            ->setActiveMethod('totp')
            ->setPhoneNumber('+48500600700')
            ->setEmail('aaa@2fas.com')
            ->setTotpSecret('PEHMPSDNLXIOG65U')
            ->setBackupCodesCount(0);

        if ($this->isDevelopmentEnvironment()) {
            $response = array(
                'id'                 => $id,
                'external_id'        => $externalId,
                'active_method'      => $user->getActiveMethod(),
                'phone_number'       => $user->getPhoneNumber()->phoneNumber(),
                'email'              => $user->getEmail(),
                'totp_secret'        => $user->getTotpSecret(),
                'backup_codes_count' => $user->getBackupCodesCount()
            );
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::CREATED));
            $this->twoFAS->method('formatNumber')->willReturn(new FormattedNumber($user->getPhoneNumber()->phoneNumber()));
            $this->twoFAS->setHttpClient($this->httpClient);
        }

        $addedUser = $this->twoFAS->addIntegrationUser($this->keyStorage, $user);
        $this->assertInstanceOf('\TwoFAS\Api\IntegrationUser', $addedUser);

        if ($this->isDevelopmentEnvironment()) {
            $this->assertEquals($id, $addedUser->getId());
        }

        $this->assertEquals($user->getExternalId(), $addedUser->getExternalId());
        $this->assertEquals($user->getActiveMethod(), $addedUser->getActiveMethod());
        $this->assertEquals($user->getPhoneNumber()->phoneNumber(), $addedUser->getPhoneNumber()->phoneNumber());
        $this->assertEquals($user->getEmail(), $addedUser->getEmail());
        $this->assertEquals($user->getTotpSecret(), $addedUser->getTotpSecret());
        $this->assertEquals($user->getBackupCodesCount(), $addedUser->getBackupCodesCount());

        return $user;
    }

    public function testAddIntegrationUserWithInvalidData()
    {
        if ($this->isDevelopmentEnvironment()) {
            $json = json_encode(array('error' => array(
                "code" => 9030,
                "msg"  => array(
                    "active_method" => array(
                        "validation.valid_method"
                    )
                )
            )));
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom($json, HttpCodes::BAD_REQUEST));
            $this->twoFAS->setHttpClient($this->httpClient);
        }

        $this->setExpectedException('\TwoFAS\Api\Exception\ValidationException');

        $user = new IntegrationUser();
        $user->setActiveMethod('invalid');

        $this->twoFAS->addIntegrationUser($this->keyStorage, $user);
    }

    public function testAddIntegrationUserWithNonProvidedPhoneNumber()
    {
        $user = new IntegrationUser();
        $user
            ->setActiveMethod('totp')
            ->setTotpSecret('PEHMPSDNLXIOG65U');

        if ($this->isDevelopmentEnvironment()) {
            $id       = uniqid();
            $response = array(
                'id'                 => $id,
                'external_id'        => null,
                'active_method'      => $user->getActiveMethod(),
                'phone_number'       => null,
                'email'              => null,
                'totp_secret'        => $user->getTotpSecret(),
                'backup_codes_count' => 0
            );
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::CREATED));
            $this->twoFAS->setHttpClient($this->httpClient);
        }

        $addedUser = $this->twoFAS->addIntegrationUser($this->keyStorage, $user);
        $this->assertNull($addedUser->getPhoneNumber()->phoneNumber());
        $this->assertEquals(0, $addedUser->getBackupCodesCount());
    }

    public function testAddIntegrationUserWithEmptyPhoneNumber()
    {
        $user = new IntegrationUser();
        $user
            ->setActiveMethod('totp')
            ->setPhoneNumber("")
            ->setEmail("")
            ->setTotpSecret('PEHMPSDNLXIOG65U');

        if ($this->isDevelopmentEnvironment()) {
            $id       = uniqid();
            $response = array(
                'id'                 => $id,
                'active_method'      => $user->getActiveMethod(),
                'phone_number'       => null,
                'email'              => null,
                'totp_secret'        => $user->getTotpSecret(),
                'backup_codes_count' => 0
            );
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::CREATED));
            $this->twoFAS->setHttpClient($this->httpClient);
        }

        $addedUser = $this->twoFAS->addIntegrationUser($this->keyStorage, $user);
        $this->assertNull($addedUser->getPhoneNumber()->phoneNumber());
    }

    /**
     * @param IntegrationUser $user
     *
     * @depends testAddIntegrationUser
     *
     * @throws \TwoFAS\Api\Exception\Exception
     *
     * @return IntegrationUser
     */
    public function testUpdateIntegrationUser(IntegrationUser $user)
    {
        $user
            ->setActiveMethod('totp')
            ->setPhoneNumber('+48500200300')
            ->setEmail('aaa@2fas.com')
            ->setTotpSecret('PEHMPSDNLXIOG666')
            ->setBackupCodesCount(0)
            ->setHasMobileUser(false);

        $response = array(
            'id'                 => $user->getId(),
            'external_id'        => $user->getExternalId(),
            'active_method'      => $user->getActiveMethod(),
            'phone_number'       => $user->getPhoneNumber()->phoneNumber(),
            'email'              => $user->getEmail(),
            'totp_secret'        => $user->getTotpSecret(),
            'backup_codes_count' => $user->getBackupCodesCount()
        );

        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::OK));
            $this->twoFAS->method('formatNumber')->willReturn(new FormattedNumber($user->getPhoneNumber()->phoneNumber()));
            $this->twoFAS->setHttpClient($this->httpClient);
        }

        $updatedUser = $this->twoFAS->updateIntegrationUser($this->keyStorage, $user);

        $this->assertInstanceOf('\TwoFAS\Api\IntegrationUser', $updatedUser);
        $this->assertEquals($response['id'], $updatedUser->getId());
        $this->assertEquals($response['active_method'], $updatedUser->getActiveMethod());
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
     * @throws \TwoFAS\Api\Exception\Exception
     */
    public function testUpdateIntegrationUserWithInvalidData(IntegrationUser $user)
    {
        if ($this->isDevelopmentEnvironment()) {
            $json = json_encode(array('error' => array(
                "code" => 9030,
                "msg"  => array(
                    "active_method" => array(
                        "validation.valid_method"
                    )
                )
            )));
            $this->twoFAS->method('formatNumber')->willReturn(new FormattedNumber(null));
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom($json, HttpCodes::BAD_REQUEST));
            $this->twoFAS->setHttpClient($this->httpClient);
        }

        $this->setExpectedException('\TwoFAS\Api\Exception\ValidationException');

        $user->setActiveMethod('invalid_method');
        $this->twoFAS->updateIntegrationUser($this->keyStorage, $user);
    }

    public function testGetIntegrationUsers()
    {
        $expectedStructure = array(
            'total'         => 1,
            'per_page'      => 50,
            'current_page'  => 1,
            'last_page'     => 1,
            'next_page_url' => null,
            'prev_page_url' => null,
            'from'          => 1,
            'to'            => 1,
            'data'          => array(
                array(),
                array(),
                array()
            )
        );

        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($expectedStructure), HttpCodes::OK));
            $this->twoFAS->setHttpClient($this->httpClient);
        }

        $response = $this->twoFAS->getIntegrationUsers();

        $this->assertEquals($this->sortedArrayKeys($response), $this->sortedArrayKeys($expectedStructure));
        $this->assertCount(3, $response['data']);
    }

    public function testCannotGetIntegrationUsersWhenUseInvalidPage()
    {
        $this->setExpectedException('InvalidArgumentException', 'Page number is not valid.');

        $this->twoFAS->getIntegrationUsers('foobar');
    }

    /**
     * @param IntegrationUser $user
     *
     * @depends testUpdateIntegrationUser
     */
    public function testGetIntegrationUser(IntegrationUser $user)
    {
        $response = array(
            'id'                 => uniqid(),
            'external_id'        => null,
            'active_method'      => 'totp',
            'phone_number'       => 'ZWZ6dExNUktRZExjMXVHcXVKcjdBdz09:RjQ5ToYrddzUWTqREMMJMA==',
            'email'              => 'dzdrMkxOMk9pa2JjUlR5d1YyUnVuUT09:lxbNXqw7/60nlSewHKmB4w==',
            'totp_secret'        => 'UWdWdU9ZSTJIWjBkSVJTYkRWN1hYN0RqVi9qd21mMjF3UlZFNGF4d092UT0=:P95fmMxZVcWVwpAsK3q3uA==',
            'mobile_secret'      => uniqid('', true),
            'has_mobile_user'    => false,
            'mobile_user_id'     => null,
            'backup_codes_count' => 0
        );

        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::OK));
            $this->twoFAS->setHttpClient($this->httpClient);
        }

        $responseUser = $this->twoFAS->getIntegrationUser($this->keyStorage, $user->getId());
        $this->assertInstanceOf('\TwoFAS\Api\IntegrationUser', $responseUser);

        if ($this->isDevelopmentEnvironment()) {
            $this->assertEquals($response['id'], $responseUser->getId());
            $this->assertEquals($response['external_id'], $responseUser->getExternalId());
        }

        $this->assertEquals($response['active_method'], $responseUser->getActiveMethod());
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
        $response = array(
            'id'                 => uniqid(),
            'external_id'        => 'my_database_id_' . uniqid(),
            'active_method'      => 'totp',
            'phone_number'       => 'ZWZ6dExNUktRZExjMXVHcXVKcjdBdz09:RjQ5ToYrddzUWTqREMMJMA==',
            'email'              => 'dzdrMkxOMk9pa2JjUlR5d1YyUnVuUT09:lxbNXqw7/60nlSewHKmB4w==',
            'totp_secret'        => 'UWdWdU9ZSTJIWjBkSVJTYkRWN1hYN0RqVi9qd21mMjF3UlZFNGF4d092UT0=:P95fmMxZVcWVwpAsK3q3uA==',
            'mobile_secret'      => uniqid('', true),
            'has_mobile_user'    => false,
            'mobile_user_id'     => null,
            'backup_codes_count' => 0
        );

        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($response), HttpCodes::OK));
            $this->twoFAS->setHttpClient($this->httpClient);
        }

        $responseUser = $this->twoFAS->getIntegrationUserByExternalId($this->keyStorage, $user->getExternalId());
        $this->assertInstanceOf('\TwoFAS\Api\IntegrationUser', $responseUser);

        if ($this->isDevelopmentEnvironment()) {
            $this->assertEquals($response['id'], $responseUser->getId());
            $this->assertEquals($response['external_id'], $responseUser->getExternalId());
        }

        $this->assertEquals($response['active_method'], $responseUser->getActiveMethod());
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
     * @throws \TwoFAS\Api\Exception\Exception
     */
    public function testDeleteIntegrationUser(IntegrationUser $user)
    {
        if ($this->isDevelopmentEnvironment()) {
            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode(array()), HttpCodes::NO_CONTENT));
            $this->twoFAS->setHttpClient($this->httpClient);
        }

        $response = $this->twoFAS->deleteIntegrationUser($user->getId());
        $this->assertTrue($response);
    }

    public function testDeleteNonExistentUser()
    {
        if ($this->isDevelopmentEnvironment()) {
            $json = json_encode(array('error' => array(
                "code" => 9031,
                "msg"  => "Integration user not found"
            )));

            $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom($json, HttpCodes::NOT_FOUND));
            $this->twoFAS->setHttpClient($this->httpClient);
        }

        $this->setExpectedException('\TwoFAS\Api\Exception\IntegrationUserNotFoundException');
        $this->twoFAS->deleteIntegrationUser('abc123');
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
