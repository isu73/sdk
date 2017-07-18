<?php

namespace TwoFAS\Api\tests;

use PHPUnit_Framework_TestCase;
use TwoFAS\Api\TotpSecretGenerator;

class TotpSecretGeneratorTest extends PHPUnit_Framework_TestCase
{
    public function testValidTotpSecret()
    {
        for ($i = 0; $i < 10; $i++) {

            $secret = TotpSecretGenerator::generate();

            $this->assertRegExp('/^[234567QWERTYUIOPASDFGHJKLZXCVBNM]{' . TotpSecretGenerator::LENGTH . '}$/', $secret);
        }
    }
}
