<?php

namespace TwoFAS\Api;

class ChannelStatuses
{
    const RESOLVED = 'resolved';
    const REJECTED = 'rejected';

    /**
     * @return array
     */
    public static function getAllowedStatuses()
    {
        return array(
            self::RESOLVED,
            self::REJECTED
        );
    }
}