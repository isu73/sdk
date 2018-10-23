<?php

namespace TwoFAS\Api\tests\TwoFAS;

use CurlClientProxy;
use DateTime;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use TwoFAS\Api\Authentication;
use TwoFAS\Api\AuthenticationCollection;
use TwoFAS\Api\HttpClient\ClientInterface;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\Api\TwoFAS;
use TwoFAS\Encryption\AESKey;
use TwoFAS\Encryption\DummyKeyStorage;
use TwoFAS\Encryption\Interfaces\ReadKey;

class LiveAndMockBase extends PHPUnit_Framework_TestCase
{
    /**
     * @var TwoFAS|PHPUnit_Framework_MockObject_MockObject
     */
    protected $twoFAS;

    /**
     * @var ClientInterface|PHPUnit_Framework_MockObject_MockObject
     */
    protected $httpClient;

    /**
     * @var ReadKey
     */
    protected $keyStorage;

    /**
     * @var IntegrationUser
     */
    protected $user;

    /**
     * @var string
     */
    private $env;

    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var array
     */
    protected $mockedMethods = array('formatNumber', 'getIntegrationUser', 'updateIntegrationUser');

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->env     = getenv('env');
        $this->baseUrl = getenv('base_url');
        $this->setUpTwoFAS(getenv('login'), getenv('key'), $this->mockedMethods);
        $key              = new AESKey(base64_decode(getenv('aes_key')));
        $this->keyStorage = new DummyKeyStorage();
        $this->keyStorage->store($key);
    }

    protected function tearDown()
    {
        parent::tearDown();

        if (!$this->isDevelopmentEnvironment() && !is_null($this->user)) {
            $this->twoFAS->deleteIntegrationUser($this->user->getId());
        }
    }

    /**
     * @param string $login
     * @param string $key
     * @param array  $methods
     */
    protected function setUpTwoFAS($login, $key, array $methods)
    {
        if ($this->isDevelopmentEnvironment()) {
            $this->twoFAS     = $this->getMockBuilder('TwoFAS\Api\TwoFAS')
                ->setConstructorArgs(array($login, $key))
                ->setMethods($methods)
                ->getMock();
            $this->httpClient = $this->getHttpClientStub();
        } else {
            $this->httpClient = new CurlClientProxy();
            $this->twoFAS     = new TwoFAS($login, $key);
            $this->twoFAS->setBaseUrl($this->baseUrl);
        }

        $this->twoFAS->setHttpClient($this->httpClient);
    }

    /**
     * @param array $data
     *
     * @throws \TwoFAS\Api\Exception\Exception
     */
    protected function createIntegrationUser(array $data)
    {
        $user = new IntegrationUser();
        $user
            ->setId($data['id'])
            ->setPhoneNumber($data['phone_number'])
            ->setEmail($data['email'])
            ->setTotpSecret($data['totp_secret'])
            ->setBackupCodesCount(0);

        if ($this->isDevelopmentEnvironment()) {
            $this->user = $user;
        } else {
            $this->user = $this->twoFAS->addIntegrationUser($this->keyStorage, $user);
        }
    }

    /**
     * @return PHPUnit_Framework_MockObject_MockObject|ClientInterface
     */
    private function getHttpClientStub()
    {
        return $this->getMockBuilder('\TwoFAS\Api\HttpClient\CurlClient')->getMock();
    }

    /**
     * @return bool
     */
    protected function isDevelopmentEnvironment()
    {
        return $this->env === 'dev';
    }

    /**
     * @return array
     */
    protected function getBackupCodesArray()
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
    protected function makeAuthenticationCollection(array $ids)
    {
        $collection = new AuthenticationCollection();

        foreach ($ids as $id) {
            $collection->add(new Authentication($id, $this->getDate(), $this->getDateIn15InFormat()));
        }

        return $collection;
    }

    /**
     * @return DateTime
     */
    protected function getDate()
    {
        return new DateTime();
    }

    /**
     * @return DateTime
     */
    protected function getDateIn15InFormat()
    {
        $time = time() + 60 * 15;

        $date = new DateTime();

        return $date->setTimestamp($time);
    }
}