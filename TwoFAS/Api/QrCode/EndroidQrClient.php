<?php

namespace TwoFAS\Api\QrCode;

use Endroid\QrCode\QrCode;

/**
 * Class EndroidQrClient
 *
 * @package TwoFAS\Api\QrCode
 */
class EndroidQrClient implements QrClientInterface
{
    /**
     * @var QrCode
     */
    private $client;

    /**
     * EndroidQrClient constructor.
     */
    public function __construct()
    {
        $this->client = new QrCode();
    }

    /**
     * @inheritdoc
     */
    public function base64($text)
    {
        $this->client
            ->setText($text)
            ->setSize(QrClientInterface::SIZE)
            ->setErrorCorrection('medium');

        return $this->client->getDataUri();
    }
}