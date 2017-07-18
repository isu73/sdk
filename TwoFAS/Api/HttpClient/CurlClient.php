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

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->mapHeaders($headers));
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $login . ':' . $password);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonInput);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return ResponseGenerator::createFrom($response, $httpCode);
    }
}