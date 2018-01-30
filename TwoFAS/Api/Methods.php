<?php

namespace TwoFAS\Api;

/**
 * This class contains a list of methods used as second factor in authentication.
 *
 * @package TwoFAS\Api
 */
class Methods
{
    const SMS   = 'sms';
    const CALL  = 'call';
    const EMAIL = 'email';
    const TOTP  = 'totp';

    /**
     * @return array
     */
    public static function getAllowedMethods()
    {
        return array(
            self::SMS,
            self::CALL,
            self::EMAIL,
            self::TOTP
        );
    }
}
