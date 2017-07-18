<?php

namespace TwoFAS\Api\Code;

/**
 * Interface Code
 *
 * @package TwoFAS\Api\Code
 */
interface Code
{
    /**
     * @return array
     */
    public function authentications();

    /**
     * @return bool
     */
    public function accepted();

    /**
     * @return bool
     */
    public function canRetry();
}
