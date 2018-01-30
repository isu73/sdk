<?php

namespace TwoFAS\Api\Exception;

/**
 * This exception will be thrown if you're trying to send sms to landline which doesn't support it.
 *
 * @package TwoFAS\Api\Exception
 */
class SmsToLandlineException extends InvalidNumberException
{
}
