<?php

namespace TwoFAS\Api;

use PHPUnit_Framework_TestCase;

class MobileSecretGeneratorTest extends PHPUnit_Framework_TestCase
{
    public function testValidMobileSecret()
    {
        for ($i = 0; $i < 10; $i++) {

            $secret = MobileSecretGenerator::generate();

            $this->assertRegExp('/^[a-z0-9]{32}$/', $secret);
        }
    }
}
