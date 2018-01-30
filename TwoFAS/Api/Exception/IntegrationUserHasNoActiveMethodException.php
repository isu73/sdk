<?php

namespace TwoFAS\Api\Exception;

/**
 * This exception is thrown when requesting authentication using saved method without setting it.
 *
 * @package TwoFAS\Api\Exception
 */
class IntegrationUserHasNoActiveMethodException extends Exception
{
}