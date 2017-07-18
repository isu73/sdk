<?php

namespace TwoFAS\Api;

/**
 * Class MobileSecretGenerator
 *
 * @package TwoFAS\Api
 */
class MobileSecretGenerator
{
    /**
     * @return string
     */
    public static function generate()
    {
        return substr(sha1(uniqid('', true)), 0, 32);
    }
}