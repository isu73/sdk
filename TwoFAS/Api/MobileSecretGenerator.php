<?php

namespace TwoFAS\Api;

use TwoFAS\Encryption\Random\RandomGenerator;

/**
 * This class helps you generate mobile secret.
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
        $generator = new RandomGenerator();

        return $generator->alphaNum(32)->toLower()->__toString();
    }
}