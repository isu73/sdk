<?php

namespace TwoFAS\Api\Sdk;

use DateTime;
use PHPUnit_Framework_MockObject_MockObject;
use PHPUnit_Framework_TestCase;
use TwoFAS\Api\Exception\Exception as ApiException;
use TwoFAS\Api\HttpClient\ClientInterface;
use TwoFAS\Api\IntegrationUser;
use TwoFAS\Api\Response\ResponseGenerator;
use TwoFAS\Api\Sdk;
use TwoFAS\Encryption\AESKey;
use TwoFAS\Encryption\DummyKeyStorage;
use TwoFAS\Encryption\Interfaces\ReadKey;

class LiveAndMockBase extends PHPUnit_Framework_TestCase
{
    /**
     * @var Sdk|PHPUnit_Framework_MockObject_MockObject
     */
    protected $sdk;

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
    protected $mockedMethods = ['formatNumber', 'getIntegrationUser', 'updateIntegrationUser'];

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->env     = getenv('env');
        $this->baseUrl = getenv('base_url');
        $this->setUpTwoFAS(getenv('oauth_token'), $this->mockedMethods);
        $key              = new AESKey(base64_decode(getenv('aes_key')));
        $this->keyStorage = new DummyKeyStorage();
        $this->keyStorage->store($key);
    }

    protected function tearDown()
    {
        parent::tearDown();

        if (!$this->isDevelopmentEnvironment() && !is_null($this->user)) {
            $this->sdk->deleteIntegrationUser($this->user->getId());
        }
    }

    /**
     * @param array $data
     * @param int   $httpCode
     *
     * @throws ApiException
     */
    protected function nextApiCallWillReturn(array $data, $httpCode)
    {
        $this->httpClient->method('request')->willReturn(ResponseGenerator::createFrom(json_encode($data), $httpCode));
    }

    /**
     * @param string $token
     * @param array  $methods
     */
    protected function setUpTwoFAS($token, array $methods)
    {
        if ($this->isDevelopmentEnvironment()) {
            $this->sdk        = $this->getMockBuilder('TwoFAS\Api\Sdk')
                ->setConstructorArgs([$token])
                ->setMethods($methods)
                ->getMock();
            $this->httpClient = $this->getHttpClientStub();
        } else {
            $this->httpClient = new CurlClientProxy();
            $this->sdk        = new Sdk($token);
            $this->sdk->setBaseUrl($this->baseUrl);
        }

        $this->sdk->setHttpClient($this->httpClient);
    }

    /**
     * @param array $data
     *
     * @throws ApiException
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
            $this->user = $this->sdk->addIntegrationUser($this->keyStorage, $user);
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
        return [
            'yx5w-Xhui-JzMN',
            'kAec-t6PD-eIsL',
            '5au1-IOiH-ksBq',
            '5au1-t6PD-ksBq',
            '5au1-Xhui-ksBq'
        ];
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