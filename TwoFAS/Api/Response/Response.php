<?php

namespace TwoFAS\Api\Response;

use TwoFAS\Api\Errors;
use TwoFAS\Api\Exception\AuthenticationLimitationException;
use TwoFAS\Api\Exception\AuthorizationException;
use TwoFAS\Api\Exception\ChannelNotActiveException;
use TwoFAS\Api\Exception\CountryIsBlockedException;
use TwoFAS\Api\Exception\Exception;
use TwoFAS\Api\Exception\IntegrationUserNotFoundException;
use TwoFAS\Api\Exception\InvalidNumberException;
use TwoFAS\Api\Exception\NumberLimitationException;
use TwoFAS\Api\Exception\PaymentException;
use TwoFAS\Api\Exception\SmsToLandlineException;
use TwoFAS\Api\Exception\ValidationException;
use TwoFAS\Api\HttpCodes;

/**
 * This class stores data returned by the API
 *
 * @package TwoFAS\Api\Response
 */
class Response
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var integer
     */
    private $code;

    /**
     * @param array   $data
     * @param integer $code
     */
    public function __construct(array $data, $code)
    {
        $this->data = $data;
        $this->code = $code;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return integer
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return Exception
     */
    public function getError()
    {
        if ($this->matchesHttpCode(HttpCodes::UNAUTHORIZED)) {
            return new AuthorizationException((string) $this->data['error']['msg']);
        } else if ($this->matchesHttpCode(HttpCodes::PAYMENT_REQUIRED)) {
            return new PaymentException((string) $this->data['error']['msg']);
        } else if ($this->matchesHttpAndErrorCode(HttpCodes::NOT_ACCEPTABLE, Errors::INVALID_NUMBER_ERROR)) {
            return new InvalidNumberException((string) $this->data['error']['msg']);
        } else if ($this->matchesHttpAndErrorCode(HttpCodes::NOT_ACCEPTABLE, Errors::SMS_TO_LANDLINE)) {
            return new SmsToLandlineException((string) $this->data['error']['msg']);
        } else if ($this->matchesHttpAndErrorCode(HttpCodes::TOO_MANY_REQUESTS, Errors::AUTH_LIMITATION)) {
            return new AuthenticationLimitationException((string) $this->data['error']['msg']);
        } else if ($this->matchesHttpAndErrorCode(HttpCodes::FORBIDDEN, Errors::NUMBER_LIMITATION)) {
            return new NumberLimitationException((string) $this->data['error']['msg']);
        } else if ($this->matchesHttpAndErrorCode(HttpCodes::FORBIDDEN, Errors::COUNTRY_IS_BLOCKED)) {
            return new CountryIsBlockedException((string) $this->data['error']['msg']);
        } else if ($this->matchesHttpAndErrorCode(HttpCodes::NOT_FOUND, Errors::INTEGRATION_USER_NOT_FOUND_ERROR)) {
            return new IntegrationUserNotFoundException((string) $this->data['error']['msg']);
        } else if ($this->matchesHttpAndErrorCode(HttpCodes::FORBIDDEN, Errors::CHANNEL_NOT_ACTIVE)) {
            return new ChannelNotActiveException((string) $this->data['error']['msg']);
        } else if ($this->matchesHttpAndErrorCode(HttpCodes::BAD_REQUEST, Errors::USER_INPUT_ERROR)) {
            return new ValidationException($this->data['error']['msg'], $this->data['error']['code']);
        }

        if (isset($this->data['error']['msg'])) {
            return new Exception('Unsupported response, original message: ' . $this->data['error']['msg']);
        }

        return new Exception('Unsupported response');
    }

    /**
     * @param integer $httpCode
     * @param integer $errorCode
     *
     * @return bool
     */
    public function matchesHttpAndErrorCode($httpCode, $errorCode)
    {
        return $this->matchesHttpCode($httpCode)
            && $this->matchesErrorCode($errorCode);
    }

    /**
     * @param integer $httpCode
     *
     * @return bool
     */
    public function matchesHttpCode($httpCode)
    {
        return $this->code === $httpCode;
    }

    /**
     * @param integer $errorCode
     *
     * @return bool
     */
    public function matchesErrorCode($errorCode)
    {
        return isset($this->data['error']['code'])
            && $errorCode === $this->data['error']['code'];
    }
}
