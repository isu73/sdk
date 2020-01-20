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
        return [
            self::RESOLVED,
            self::REJECTED
        ];
    }
}