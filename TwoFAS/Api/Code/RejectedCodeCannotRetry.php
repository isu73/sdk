<?php

namespace TwoFAS\Api\Code;

/**
 * Class RejectedCodeCannotRetry
 *
 * @package TwoFAS\Api\Code
 */
final class RejectedCodeCannotRetry implements Code
{
    /**
     * @var array
     */
    private $authentications;

    /**
     * @param array $authentications
     */
    public function __construct(array $authentications)
    {
        $this->authentications = $authentications;
    }

    /**
     * @inheritdoc
     */
    public function authentications()
    {
        return $this->authentications;
    }

    /**
     * @inheritdoc
     */
    public function accepted()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function canRetry()
    {
        return false;
    }
}
