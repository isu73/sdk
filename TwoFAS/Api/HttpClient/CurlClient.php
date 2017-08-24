<?php

namespace TwoFAS\Api\HttpClient;

use TwoFAS\Api\Response\ResponseGenerator;

/**
 * Class CurlClient
 *
 * @package TwoFAS\Api\HttpClient
 */
class CurlClient implements ClientInterface
{
    /**
     * @var resource
     */
    private $handle;

    public function __construct()
    {
        $this->handle = curl_init();
    }

    public function __destruct()
    {
        curl_close($this->handle);
    }

    /**
     * @param array $headers
     *
     * @return array
     */
    protected function mapHeaders(array $headers)
    {
        return array_map(function($value, $key) {
            return $key . ': ' . $value;
        },
            $headers,
            array_keys($headers)
        );
    }

    /**
     * @inheritdoc
     */
    public function request($method, $url, $login, $password, array $data = array(), array $headers = array())
    {
        $jsonInput = json_encode($data);

        curl_setopt($this->handle, CURLOPT_URL, $url);
        curl_setopt($this->handle, CURLOPT_HTTPHEADER, $this->mapHeaders($headers));
        curl_setopt($this->handle, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($this->handle, CURLOPT_USERPWD, $login . ':' . $password);
        curl_setopt($this->handle, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($this->handle, CURLOPT_POSTFIELDS, $jsonInput);
        curl_setopt($this->handle, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($this->handle);
        $httpCode = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);

        return ResponseGenerator::createFrom($response, $httpCode);
    }
}