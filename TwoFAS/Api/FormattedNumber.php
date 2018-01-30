<?php

namespace TwoFAS\Api;

/**
 * It's a simply Value Object that stores phone number.
 *
 * @package TwoFAS\Api
 */
final class FormattedNumber
{
    /**
     * @var string
     */
    private $phoneNumber;

    /**
     * @param string $phoneNumber
     */
    public function __construct($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return string
     */
    public function phoneNumber()
    {
        return $this->phoneNumber;
    }
}
