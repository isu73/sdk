<?php

namespace TwoFAS\Api;

use TwoFAS\Api\QrCode\QrClientInterface;

/**
 * QrCodeGenerator object generates base64 encoded image of QR code,
 * that can be easily displayed for user to scan it with smartphone.
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