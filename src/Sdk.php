<?php

namespace TwoFAS\Api;

use InvalidArgumentException;
use TwoFAS\Api\CacheClient\ArrayCache;
use TwoFAS\Api\CacheClient\CacheInterface;
use TwoFAS\Api\Code\AcceptedCode;
use TwoFAS\Api\Code\Code;
use TwoFAS\Api\Code\RejectedCodeCannotRetry;
use TwoFAS\Api\Code\RejectedCodeCanRetry;
use TwoFAS\Api\Exception\AuthorizationException;
use TwoFAS\Api\Exception\CountryIsBlockedException;
use TwoFAS\Api\Exception\Exception;
use TwoFAS\Api\Exception\IntegrationUserNotFoundException;
use TwoFAS\Api\Exception\InvalidDateException;
use TwoFAS\Api\Exception\InvalidNumberException;
use TwoFAS\Api\Exception\PaymentException;
use TwoFAS\Api\Exception\SmsToLandlineException;
use TwoFAS\Api\Exception\ValidationException;
use TwoFAS\Api\HttpClient\ClientInterface;
use TwoFAS\Api\HttpClient\CurlClient;
use TwoFAS\Api\Response\Response;
use TwoFAS\Encryption\Cryptographer;
use TwoFAS\Encryption\Interfaces\ReadKey;

/**
 * This is the main SDK class that is used to interact with the API.
 *
 * @package TwoFAS\Api
 */
class Sdk
{
    /**
     * @var string
     */
    const VERSION = '7.0.1';

    /**
     * @var string
     */
    private $token;

    /**
     * @var string
     */
    private $baseUrl = 'https://api.2fas.com';

    /**
     * @var string
     */
    private $version = '/v2';

    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var CacheInterface
     */
    private $cache;

    /**
     * @var Hydrator
     */
    private $hydrator;

    /**
     * @var array
     */
    private $headers = [
        'Content-Type' => 'application/json',
        'Sdk-Version'  => self::VERSION
    ];

    /**
     * @param string $token
     * @param array  $headers
     */
    public function __construct($token, array $headers = [])
    {
        $this->token      = $token;
        $this->httpClient = new CurlClient();
        $this->cache      = new ArrayCache();
        $this->hydrator   = new Hydrator();
        $this->addHeaders($headers);
    }

    /**
     * Set API url.
     *
     * @param string $url
     *
     * @return Sdk
     */
    public function setBaseUrl($url)
    {
        $this->baseUrl = $url;
        return $this;
    }

    /**
     * Set custom http client.
     *
     * @param ClientInterface $httpClient
     *
     * @return Sdk
     */
    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
        return $this;
    }

    /**
     * Set custom cache.
     *
     * @param CacheInterface $cache
     *
     * @return Sdk
     */
    public function setCacheClient(CacheInterface $cache)
    {
        $this->cache = $cache;
        return $this;
    }

    /**
     * Used for checking if number is valid and to unify format.
     * You can store unified number in DB to prevent creation of multiple users with same phone number.
     *
     * @param string $phoneNumber
     *
     * @return FormattedNumber
     *
     * @throws AuthorizationException
     * @throws Exception
     * @throws InvalidNumberException
     */
    public function formatNumber($phoneNumber)
    {
        $phoneNumber = (string) $phoneNumber;

        if ($this->cache->has($phoneNumber)) {
            return new FormattedNumber($this->cache->get($phoneNumber));
        }

        $formatted = $this->formatNumberApiCall($phoneNumber);
        $this->cache->set($phoneNumber, $formatted);

        return new FormattedNumber($formatted);
    }

    /**
     * @param string $phoneNumber
     *
     * @return string
     *
     * @throws AuthorizationException
     * @throws InvalidNumberException
     * @throws Exception
     */
    protected function formatNumberApiCall($phoneNumber)
    {
        $response = $this->call(
            'POST',
            $this->createEndpoint('/format_number'),
            [
                'phone_number' => $phoneNumber
            ]
        );

        if ($response->matchesHttpCode(HttpCodes::OK)) {
            $responseData = $response->getData();
            return $responseData['phone_number'];
        }

        throw $response->getError();
    }

    /**
     * Used for requesting authentication on user via SMS.
     * Store authentication id for later use.
     *
     * @param string $phoneNumber
     *
     * @return Authentication
     *
     * @throws AuthorizationException
     * @throws CountryIsBlockedException
     * @throws InvalidDateException
     * @throws InvalidNumberException
     * @throws PaymentException
     * @throws SmsToLandlineException
     * @throws ValidationException
     * @throws Exception
     */
    public function requestAuthViaSms($phoneNumber)
    {
        $response = $this->call(
            'POST',
            $this->createEndpoint('/auth/sms'),
            [
                'phone_number' => (string) $phoneNumber
            ]
        );

        if ($response->matchesHttpCode(HttpCodes::OK)) {
            return $this->hydrator->getAuthenticationFromResponse($response);
        }

        throw $response->getError();
    }

    /**
     * Used for requesting authentication on user via CALL.
     * Store authentication id for later use.
     *
     * @param string $phoneNumber
     *
     * @return Authentication
     *
     * @throws AuthorizationException
     * @throws CountryIsBlockedException
     * @throws InvalidDateException
     * @throws InvalidNumberException
     * @throws PaymentException
     * @throws ValidationException
     * @throws Exception
     */
    public function requestAuthViaCall($phoneNumber)
    {
        $response = $this->call(
            'POST',
            $this->createEndpoint('/auth/vms'),
            [
                'phone_number' => (string) $phoneNumber
            ]
        );

        if ($response->matchesHttpCode(HttpCodes::OK)) {
            return $this->hydrator->getAuthenticationFromResponse($response);
        }

        throw $response->getError();
    }

    /**
     * Used for requesting authentication on user via email.
     * Store authentication id for later use.
     *
     * @param string $email
     *
     * @return Authentication
     *
     * @throws AuthorizationException
     * @throws InvalidDateException
     * @throws ValidationException
     * @throws Exception
     */
    public function requestAuthViaEmail($email)
    {
        $response = $this->call(
            'POST',
            $this->createEndpoint('/auth/email'),
            [
                'email' => (string) $email
            ]
        );

        if ($response->matchesHttpCode(HttpCodes::OK)) {
            return $this->hydrator->getAuthenticationFromResponse($response);
        }

        throw $response->getError();
    }

    /**
     * Used for requesting authentication on user via TOTP (Time-based One-time Password Algorithm).
     * Store authentication id for later use.
     *
     * @param string $secret
     *
     * @return Authentication
     *
     * @throws AuthorizationException
     * @throws InvalidDateException
     * @throws ValidationException
     * @throws Exception
     */
    public function requestAuthViaTotp($secret)
    {
        $response = $this->call(
            'POST',
            $this->createEndpoint('/auth/totp'),
            [
                'totp_secret' => (string) $secret
            ]
        );

        if ($response->matchesHttpCode(HttpCodes::OK)) {
            return $this->hydrator->getAuthenticationFromResponse($response);
        }

        throw $response->getError();
    }

    /**
     * @param string $secret
     * @param string $mobileSecret
     * @param string $sessionId
     * @param string $browserVersion
     *
     * @return Authentication
     *
     * @throws AuthorizationException
     * @throws InvalidDateException
     * @throws ValidationException
     * @throws Exception
     */
    public function requestAuthViaTotpWithMobileSupport($secret, $mobileSecret, $sessionId, $browserVersion)
    {
        $response = $this->call(
            'POST',
            $this->createEndpoint('/auth/totp/mobile'),
            [
                'totp_secret'     => (string) $secret,
                'mobile_secret'   => (string) $mobileSecret,
                'session_id'      => (string) $sessionId,
                'browser_version' => (string) $browserVersion,
            ]
        );

        if ($response->matchesHttpCode(HttpCodes::OK)) {
            return $this->hydrator->getAuthenticationFromResponse($response);
        }

        throw $response->getError();
    }

    /**
     * Used for validating code entered by user.
     *
     * @param array  $authentications
     * @param string $code
     *
     * @return Code
     *
     * @throws AuthorizationException
     * @throws ValidationException
     * @throws Exception
     */
    public function checkCode(array $authentications, $code)
    {
        $response = $this->call(
            'POST',
            $this->createEndpoint('/verify'),
            [
                'authentications' => $authentications,
                'code'            => (string) $code
            ]
        );

        if ($response->matchesHttpCode(HttpCodes::NO_CONTENT)) {
            return new AcceptedCode($authentications);
        }

        if (
            $response->matchesHttpAndErrorCode(HttpCodes::FORBIDDEN, Errors::INVALID_CODE_ERROR_CAN_NOT_RETRY)
            || $response->matchesHttpAndErrorCode(HttpCodes::NOT_FOUND, Errors::NO_AUTHENTICATIONS)
        ) {
            return new RejectedCodeCannotRetry($authentications);
        }

        if ($response->matchesHttpAndErrorCode(HttpCodes::FORBIDDEN, Errors::INVALID_CODE_ERROR_CAN_RETRY)) {
            return new RejectedCodeCanRetry($authentications);
        }

        throw $response->getError();
    }

    /**
     * Used for validating backup code entered by user.
     * Backup code is expected to be 12 non-omitted characters. Non-omitted characters consists of subsets:
     * - letters: abcdefghjkmnpqrstuvwxyz
     * - numbers: 23456789
     *
     * You can send code with or without - separators, code is not case-sensitive.
     *
     * @param IntegrationUser $user
     * @param array           $authentications
     * @param string          $code
     *
     * @return Code
     *
     * @throws ValidationException
     * @throws Exception
     */
    public function checkBackupCode(IntegrationUser $user, array $authentications, $code)
    {
        $response = $this->call(
            'POST',
            $this->createEndpoint("/verify/user/{$user->getId()}/backup"),
            [
                'authentications' => $authentications,
                'code'            => (string) $code
            ]
        );

        if ($response->matchesHttpCode(HttpCodes::NO_CONTENT)) {
            return new AcceptedCode($authentications);
        }

        if (
            $response->matchesHttpAndErrorCode(HttpCodes::FORBIDDEN, Errors::INVALID_CODE_ERROR_CAN_NOT_RETRY)
            || $response->matchesHttpAndErrorCode(HttpCodes::NOT_FOUND, Errors::NO_AUTHENTICATIONS)
        ) {
            return new RejectedCodeCannotRetry($authentications);
        }

        if ($response->matchesHttpAndErrorCode(HttpCodes::FORBIDDEN, Errors::INVALID_CODE_ERROR_CAN_RETRY)) {
            return new RejectedCodeCanRetry($authentications);
        }

        throw $response->getError();
    }

    /**
     * @param int    $integrationId
     * @param string $sessionId
     * @param string $socketId
     *
     * @return array
     *
     * @throws AuthorizationException
     * @throws ValidationException
     * @throws Exception
     */
    public function authenticateChannel($integrationId, $sessionId, $socketId)
    {
        $channelName = 'private-wp_' . $integrationId . '_' . $sessionId;

        $response = $this->call(
            'POST',
            $this->createEndpoint('/integration/authenticate_channel'),
            [
                'channel_name' => (string) $channelName,
                'socket_id'    => (string) $socketId
            ]
        );

        if ($response->matchesHttpCode(HttpCodes::OK)) {
            return $response->getData();
        }

        throw $response->getError();
    }

    /**
     * @param string $channelName
     * @param int    $statusId
     * @param string $status
     *
     * @return array
     *
     * @throws AuthorizationException
     * @throws InvalidArgumentException
     * @throws ValidationException
     * @throws Exception
     */
    public function updateChannelStatus($channelName, $statusId, $status)
    {
        if (!in_array($status, ChannelStatuses::getAllowedStatuses())) {
            throw new InvalidArgumentException('Channel status is not valid.');
        }

        $response = $this->call(
            'POST',
            $this->createEndpoint('/integration/channel/' . $channelName . '/status/' . $statusId),
            [
                'status' => (string) $status
            ]
        );

        if ($response->matchesHttpCode(HttpCodes::OK)) {
            return $response->getData();
        }

        throw $response->getError();
    }

    /**
     * Used for getting paginated list of integration users from 2fas.
     *
     * @param int|null $page
     *
     * @return array
     *
     * @throws AuthorizationException
     * @throws Exception
     */
    public function getIntegrationUsers($page = null)
    {
        $url = '/users';

        if (!is_null($page) && !is_int($page)) {
            throw new InvalidArgumentException('Page number is not valid.');
        }

        if (!is_null($page)) {
            $url .= '?page=' . $page;
        }

        $response = $this->call(
            'GET',
            $this->createEndpoint($url)
        );

        if ($response->matchesHttpCode(HttpCodes::OK)) {
            return $response->getData();
        }

        throw $response->getError();
    }

    /**
     * Used for getting integration user from 2fas.
     *
     * @param ReadKey $keyStorage
     * @param string  $userId
     *
     * @return IntegrationUser
     *
     * @throws AuthorizationException
     * @throws IntegrationUserNotFoundException
     * @throws Exception
     */
    public function getIntegrationUser(ReadKey $keyStorage, $userId)
    {
        $response = $this->call(
            'GET',
            $this->createEndpoint('/users/' . $userId)
        );

        if ($response->matchesHttpCode(HttpCodes::OK)) {
            return $this->hydrator->getIntegrationUserFromResponse($keyStorage, $response);
        }

        throw $response->getError();
    }

    /**
     * Used for get integration user from 2fas by your own id.
     *
     * @param ReadKey $keyStorage
     * @param string  $userExternalId
     *
     * @return IntegrationUser
     *
     * @throws AuthorizationException
     * @throws IntegrationUserNotFoundException
     * @throws Exception
     */
    public function getIntegrationUserByExternalId(ReadKey $keyStorage, $userExternalId)
    {
        $response = $this->call(
            'GET',
            $this->createEndpoint('/users_external/' . $userExternalId),
            []
        );

        if ($response->matchesHttpCode(HttpCodes::OK)) {
            return $this->hydrator->getIntegrationUserFromResponse($keyStorage, $response);
        }

        throw $response->getError();
    }

    /**
     * Used for adding integration user to 2fas.
     *
     * @param ReadKey         $keyStorage
     * @param IntegrationUser $user
     *
     * @return IntegrationUser
     *
     * @throws AuthorizationException
     * @throws ValidationException
     * @throws Exception
     */
    public function addIntegrationUser(ReadKey $keyStorage, IntegrationUser $user)
    {
        $this->reformatPhoneNumber($user);

        $response = $this->call(
            'POST',
            $this->createEndpoint('/users'),
            $user->getEncryptedDataAsArray(Cryptographer::getInstance($keyStorage))
        );

        if ($response->matchesHttpCode(HttpCodes::CREATED)) {
            $responseData = $response->getData();
            $user->setId($responseData['id']);

            return $user;
        }

        throw $response->getError();
    }

    /**
     * Used for updating integration user in 2fas.
     *
     * @param ReadKey         $keyStorage
     * @param IntegrationUser $user
     *
     * @return IntegrationUser
     *
     * @throws AuthorizationException
     * @throws IntegrationUserNotFoundException
     * @throws ValidationException
     * @throws Exception
     */
    public function updateIntegrationUser(ReadKey $keyStorage, IntegrationUser $user)
    {
        $this->reformatPhoneNumber($user);

        $response = $this->call(
            'PUT',
            $this->createEndpoint('/users/' . $user->getId()),
            $user->getEncryptedDataAsArray(Cryptographer::getInstance($keyStorage))
        );

        if ($response->matchesHttpCode(HttpCodes::OK)) {
            return $user;
        }

        throw $response->getError();
    }

    /**
     * Used for deleting integration user from 2fas.
     *
     * @param string $userId
     *
     * @return bool
     *
     * @throws AuthorizationException
     * @throws IntegrationUserNotFoundException
     * @throws Exception
     */
    public function deleteIntegrationUser($userId)
    {
        $response = $this->call(
            'DELETE',
            $this->createEndpoint('/users/' . $userId),
            []
        );

        if ($response->matchesHttpCode(HttpCodes::NO_CONTENT)) {
            return true;
        }

        throw $response->getError();
    }

    /**
     * Used for generating new backup codes for Integration Users.
     *
     * @param IntegrationUser $user
     *
     * @return BackupCodesCollection
     *
     * @throws AuthorizationException
     * @throws Exception
     */
    public function regenerateBackupCodes(IntegrationUser $user)
    {
        $response = $this->call(
            'PATCH',
            $this->createEndpoint('/users/' . $user->getId() . '/backup_codes')
        );

        if ($response->matchesHttpCode(HttpCodes::OK)) {
            $responseData = $response->getData();

            $collection = new BackupCodesCollection();

            foreach ($responseData['codes'] as $code) {
                $backupCode = new BackupCode($code);
                $collection->add($backupCode);
            }

            return $collection;
        }

        throw $response->getError();
    }

    /**
     * @param array $headers
     *
     * @throws InvalidArgumentException
     */
    private function addHeaders(array $headers)
    {
        foreach ($headers as $header => $value) {
            $key = $this->normalizeHeader($header);

            if (array_key_exists($key, $this->headers)) {
                throw new InvalidArgumentException('Existing header could not be changed: ' . $key);
            }

            $this->headers[$key] = $value;
        }
    }

    /**
     * @param string $header
     *
     * @return string
     */
    private function normalizeHeader($header)
    {
        $parts = explode('-', trim($header));

        $parts = array_map(function($part) {
            return ucfirst(strtolower($part));
        }, $parts);

        return implode('-', $parts);
    }

    /**
     * @param string $suffix
     *
     * @return string
     */
    private function createEndpoint($suffix)
    {
        return $this->baseUrl . $this->version . $suffix;
    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array  $data
     *
     * @return Response
     *
     * @throws Exception
     */
    private function call($method, $endpoint, array $data = [])
    {
        return $this->httpClient->request($method, $endpoint, $this->token, $data, $this->headers);
    }

    /**
     * @param IntegrationUser $user
     *
     * @throws AuthorizationException
     * @throws InvalidNumberException
     * @throws Exception
     */
    private function reformatPhoneNumber(IntegrationUser $user)
    {
        $phoneNumber = $user->getPhoneNumber()->phoneNumber();
        if (!empty($phoneNumber)) {
            $user->setPhoneNumber($this->formatNumber($phoneNumber)->phoneNumber());
        }
    }
}
