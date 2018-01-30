<?php

namespace TwoFAS\Api;

/**
 * It simply Value Object that store backup code.
 *
 * @package TwoFAS\Api
 */
final class BackupCode
{
    /**
     * @var string
     */
    private $code;

    /**
     * @param string $code
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function code()
    {
        return $this->code;
    }
}
