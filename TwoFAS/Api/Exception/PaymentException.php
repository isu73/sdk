<?php

namespace TwoFAS\Api\Exception;

/**
 * This exception will be thrown if you used a method that requires payment and you cannot be charged.
 *
 * @package TwoFAS\Api\Exception
 */
class PaymentException extends Exception
{
}
