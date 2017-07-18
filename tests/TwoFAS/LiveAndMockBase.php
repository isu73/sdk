<?php

namespace TwoFAS\Api\tests\TwoFAS;

use CurlClientProxy;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use TwoFAS\Api\HttpClient\ClientInterface;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\Api\TwoFAS;
use TwoFAS\Encryption\AESGeneratedKey;
use TwoFAS\Encryption\DummyKeyStorage;
use TwoFAS\Encryption\Interfaces\KeyStorage as KeyStorageInterface;

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
     * @var KeyStorageInterface
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
        $this->keyStorage = new DummyKeyStorage(new AESGeneratedKey());
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
            ->setActiveMethod($data['active_method'])
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
        if ($this->env === 'dev') {
            return true;
        }

        return false;
    }
}