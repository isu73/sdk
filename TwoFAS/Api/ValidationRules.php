<?php

namespace TwoFAS\Api;

/**
 * Class ValidationRules
 *
 * @package TwoFAS\Api
 */
class ValidationRules
{
    const STRING = 'validation.string';
    const DIGITS = 'validation.digits';
    const BOOL = 'validation.boolean';
    const ARR = 'validation.array';
    const REQUIRED_WITH = 'validation.required_with';
    const REQUIRED_IF = 'validation.required_if';
    const REQUIRED = 'validation.required';
    const UNIQUE_PHONE_NUMBER = 'validation.unique_phone_number';
    const UNIQUE = 'validation.unique';
    const TWOFAS_FORMATTABLE = 'validation.two_f_a_s_formattable';
    const EMAIL = 'validation.email';
    const NULL_OR_FILLED = 'validation.null_or_filled';
    const NULL_OR_PRESENT = 'validation.null_or_present';
    const MAX = 'validation.max';
    const MIN = 'validation.min';
    const SIZE = 'validation.size';
    const REGEX = 'validation.regex';
    const AES_CIPHER = 'validation.aes_cipher';
    const BACKUP_CODE = 'validation.backup_code';
    const UNSUPPORTED = 'validation.unsupported';

    /**
     * @param string $rule
     *
     * @return string
     */
    public static function getContainingRule($rule)
    {
        $reflection = new \ReflectionClass(__CLASS__);

        foreach ($reflection->getConstants() as $constant => $value) {
            if (preg_match('/^' . str_replace('.', '\.', $value) . '(?::{1}[a-zA-Z0-9,_]*)?$/', $rule)) {
                return $value;
            }
        }

        return ValidationRules::UNSUPPORTED;
    }
}