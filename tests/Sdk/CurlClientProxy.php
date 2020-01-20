<?php

namespace TwoFAS\Api\Sdk;

use TwoFAS\Api\HttpClient\CurlClient;

class CurlClientProxy extends CurlClient
{
    /**
     * @inheritdoc
     */
    protected function mapHeaders(array $headers)
    {
        return parent::mapHeaders(array_merge($headers, ['x-forwarded-proto' => 'https']));
    }
}