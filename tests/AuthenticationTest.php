<?php

namespace TwoFAS\Api\tests;

use DateTime;
use DateTimeZone;
use PHPUnit_Framework_TestCase;
use TwoFAS\Api\Authentication;

class AuthenticationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Authentication
     */
    private $authentication;

    /**
     * @var int
     */
    private $timeNow;

    /**
     * @var int
     */
    private $timeIn15;

    /**
     * @var int
     */
    private $time35Ago;

    /**
     * @var int
     */
    private $time20Ago;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);

        $this->setLocalTimezone();

        $this->timeNow        = time();
        $this->timeIn15       = $this->timeNow + 60 * 15;
        $this->time35Ago      = $this->timeNow - 60 * 35;
        $this->time20Ago      = $this->timeNow - 60 * 20;
        $this->authentication = new Authentication(
            '123',
            $this->getTimeInLocalTimezone($this->timeNow),
            $this->getTimeInLocalTimezone($this->timeIn15)
        );
    }

    public function testAuthenticationIsValid()
    {
        $this->assertTrue(
            $this->authentication->isValid()
        );
    }

    public function testAuthenticationAfter20MinutesIsInvalid()
    {
        $oldAuthentication = new Authentication(
            '123',
            $this->getTimeInLocalTimezone($this->time35Ago),
            $this->getTimeInLocalTimezone($this->time20Ago)
        );

        $this->assertFalse(
            $oldAuthentication->isValid()
        );
    }

    private function getTimeInLocalTimezone($timestamp)
    {
        return new DateTime("@$timestamp", $this->getLocalTimezone());
    }

    /**
     * @return DateTimeZone
     */
    private function getLocalTimezone()
    {
        return new DateTimeZone('America/Los_Angeles');
    }

    private function setLocalTimezone()
    {
        date_default_timezone_set('America/Los_Angeles');
    }
}