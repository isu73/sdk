<?php

namespace TwoFAS\Api;

use TwoFAS\Encryption\Random\NonCryptographicalRandomIntGenerator;
use TwoFAS\Encryption\Random\RandomStringGenerator;

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
        $intGenerator    = new NonCryptographicalRandomIntGenerator();
        $stringGenerator = new RandomStringGenerator($intGenerator);

        return $stringGenerator->alphaNum(32)->toLower()->__toString();
    }
}