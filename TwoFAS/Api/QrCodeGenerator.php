<?php

namespace TwoFAS\Api;

use TwoFAS\Api\QrCode\QrClientInterface;

/**
 * Class QrCodeGenerator
 *
 * @package TwoFAS\Api
 */
class QrCodeGenerator
{
    /**
     * @var QrClientInterface
     */
    private $client;

    /**
     * QrCodeGenerator constructor.
     *
     * @param QrClientInterface $client
     */
    public function __construct(QrClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Generate base64 image that you can put in src image tag.
     *
     * @param string $text
     *
     * @return string
     */
    public function generateBase64($text)
    {
        return $this->client->base64($text);
    }
}