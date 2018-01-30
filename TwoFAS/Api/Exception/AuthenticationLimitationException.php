<?php

namespace TwoFAS\Api\Exception;

/**
 * This exception will be thrown if you make too many authentications in one hour
 * (affects only development keys and phone based authentication types)
 *
 * @package TwoFAS\Api\Exception
 */
class AuthenticationLimitationException extends Exception
{
}
