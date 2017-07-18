<?php

namespace TwoFAS\Api\QrCode;

/**
 * Class QrClientFactory
 *
 * @package TwoFAS\Api\QrCode
 */
class QrClientFactory
{
    /**
     * @return QrClientInterface
     */
    public static function getInstance()
    {
        return new EndroidQrClient();
    }
}