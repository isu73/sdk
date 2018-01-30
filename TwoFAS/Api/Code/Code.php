<?php

namespace TwoFAS\Api\Code;

/**
 * This is a value object returned by code check.
 *
 * @package TwoFAS\Api\Code
 */
interface Code
{
    /**
     * Array of authentication ids.
     *
     * @return array
     */
    public function authentications();

    /**
     * Result of code checking.
     *
     * @return bool
     */
    public function accepted();

    /**
     * Ability to use same ids again.
     *
     * @return bool
     */
    public function canRetry();
}
