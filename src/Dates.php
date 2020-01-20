<?php

namespace TwoFAS\Api;

use DateTime;
use DateTimeZone;
use TwoFAS\Api\Exception\InvalidDateException;

/**
 * Dates object helps converting API date to DateTime object with correct timezone.
 *
 * @package TwoFAS\Api
 */
class Dates
{
    /**
     * @var string
     */
    const TIME_ZONE = 'UTC';

    /**
     * @var string
     */
    const DATE_TIME_FORMAT = 'Y-m-d H:i:s';

    /**
     * @param string $date
     *
     * @return DateTime
     *
     * @throws InvalidDateException
     */
    public static function convertUTCFormatToLocal($date)
    {
        $validDateTime = DateTime::createFromFormat(self::DATE_TIME_FORMAT, $date, self::getUTCTimezone());

        if (!$validDateTime instanceof DateTime) {
            throw new InvalidDateException;
        }

        $validDateTime->setTimezone(self::getLocalTimezone());

        return $validDateTime;
    }

    /**
     * @return DateTimeZone
     */
    public static function getUTCTimezone()
    {
        return new DateTimeZone(self::TIME_ZONE);
    }

    /**
     * @return DateTimeZone
     */
    public static function getLocalTimezone()
    {
        return new DateTimeZone(date_default_timezone_get());
    }

    /**
     * @return string
     */
    public static function getCurrentTimeInFormat()
    {
        return date(self::DATE_TIME_FORMAT);
    }
}