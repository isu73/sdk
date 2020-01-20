<?php

namespace TwoFAS\Api;

use PHPUnit_Framework_TestCase;
use TwoFAS\Api\Exception\InvalidDateException;

class DatesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    private $dateFormat = 'Y-m-d H:i:s';

    protected function setUp()
    {
        parent::setUp();

        $this->setLocalTimezone();
    }

    /**
     * @throws InvalidDateException
     */
    public function testCallingDatesWithDifferentFormat()
    {
        $this->setExpectedException('TwoFAS\Api\Exception\InvalidDateException');

        Dates::convertUTCFormatToLocal('19:20:34 2001/09/13');
    }

    /**
     * @throws InvalidDateException
     */
    public function testDateFromApiWillShowAsLocalDate()
    {
        $utcDate       = '2017-01-18 14:21:51';
        $convertedDate = Dates::convertUTCFormatToLocal($utcDate);

        $this->assertEquals('2017-01-18 06:21:51', $convertedDate->format($this->dateFormat));
    }

    private function setLocalTimezone()
    {
        date_default_timezone_set('America/Los_Angeles');
    }
}