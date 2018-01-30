<?php

namespace TwoFAS\Api\Code;

/**
 * This class is returned if code that you're checking is incorrect, but you can try send request again with different code.
 *
 * @package TwoFAS\Api\Code
 */
final class RejectedCodeCanRetry implements Code
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
        return true;
    }
}
