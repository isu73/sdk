<?php

namespace TwoFAS\Api\HttpClient;

use TwoFAS\Api\Exception\Exception;
use TwoFAS\Api\Response\Response;

/**
 * Interface ClientInterface
 *
 * @package TwoFAS\Api\HttpClient
 */
interface ClientInterface
{
    /**
     * @param string $method
     * @param string $url
     * @param string $login
     * @param string $password
     * @param array  $data
     * @param array  $headers
     *
     * @return Response
     *
     * @throws Exception
     */
    public function request($method, $url, $login, $password, array $data = array(), array $headers = array());
}