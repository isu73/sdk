<?php

namespace TwoFAS\Api;

/**
 * It's a simple Value Object that stores phone number.
 *
 * @package TwoFAS\Api
 */
final class FormattedNumber
{
    /**
     * @var null|string
     */
    private $phoneNumber;

    /**
     * @param null|string $phoneNumber
     */
    public function __construct($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return null|string
     */
    public function phoneNumber()
    {
        return $this->phoneNumber;
    }
}
