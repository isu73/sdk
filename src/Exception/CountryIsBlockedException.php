<?php

namespace TwoFAS\Api\Exception;

/**
 * This exception will be thrown if number provided during phone-based authentication belongs to country blocked in settings.
 *
 * @package TwoFAS\Api\Exception
 */
class CountryIsBlockedException extends Exception
{
}
