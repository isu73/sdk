<?php

namespace TwoFAS\Api\tests;

use PHPUnit_Framework_TestCase;
use TwoFAS\Api\MobileSecretGenerator;

class MobileSecretGeneratorTest extends PHPUnit_Framework_TestCase
{
    public function testValidMobileSecret()
    {
        for ($i = 0; $i < 10; $i++) {

            $secret = MobileSecretGenerator::generate();

            $this->assertRegExp('/^[A-Za-z0-9]{32}$/', $secret);
        }
    }
}
