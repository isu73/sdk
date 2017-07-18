<?php

namespace TwoFAS\Api;

/**
 * Class Methods
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
