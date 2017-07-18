<?php

namespace TwoFAS\Api\QrCode;

/**
 * Interface QrClientInterface
 *
 * @package TwoFAS\Api\QrCode
 */
interface QrClientInterface
{
    const SIZE = 300;

    /**
     * @param string $text
     *
     * @return string
     */
    public function base64($text);
}