<?php

namespace TwoFAS\Api\Exception;

/**
 * This exception will be thrown if number which is used to make authentication is not on development key whitelist.
 *
 * @package TwoFAS\Api\Exception
 */
class NumberLimitationException extends Exception
{
}
