<?php

namespace TwoFAS\Api\tests\TwoFAS;

use TwoFAS\Api\BackupCode;
use TwoFAS\Api\BackupCodesCollection;

class CollectionsTest extends LiveAndMockBase
{
    public function testAuthenticationCollection()
    {
        $ids        = array('1', '2', '3');
        $collection = $this->makeAuthenticationCollection($ids);

        $this->assertEquals($ids, $collection->getIds());
    }

    public function testBackupCodesCollection()
    {
        $codes      = $this->getBackupCodesArray();
        $collection = new BackupCodesCollection();
        foreach ($codes as $code) {
            $collection->add(new BackupCode($code));
        }

        $this->assertEquals($codes, $collection->getCodes());
    }
}