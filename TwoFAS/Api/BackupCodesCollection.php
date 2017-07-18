<?php

namespace TwoFAS\Api;

/**
 * Class BackupCodesCollection
 *
 * @package TwoFAS\Api
 */
class BackupCodesCollection
{
    /**
     * @var BackupCode[]
     */
    private $codes = array();

    /**
     * @param BackupCode $backupCode
     */
    public function add(BackupCode $backupCode)
    {
        $this->codes[] = $backupCode;
    }

    /**
     * Return array of backup codes
     *
     * @return array
     */
    public function getCodes()
    {
        return array_map(function(BackupCode $backupCode) {
            return $backupCode->code();
        }, $this->codes);
    }
}