<?php

namespace TwoFAS\Api;

/**
 * This class helps you generate totp secret.
 *
 * @package TwoFAS\Api
 */
class TotpSecretGenerator
{
    /**
     * @var int
     */
    const LENGTH = 16;

    /**
     * @var string
     */
    const ALPHABET = '234567QWERTYUIOPASDFGHJKLZXCVBNM';

    /**
     * Generates a 16 digit secret key in base32 format
     *
     * @return string
     */
    public static function generate()
    {
        $secret = '';

        while (strlen($secret) < self::LENGTH) {
            $secret .= substr(str_shuffle(self::ALPHABET), -1);
        }

        return $secret;
    }
}