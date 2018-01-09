<?php

namespace TwoFAS\Api;

use TwoFAS\Encryption\Random\RandomGenerator;

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
        $generator = new RandomGenerator();

        return $generator->alphaNum(32)->toLower()->__toString();
    }
}