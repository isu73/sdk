<?php

namespace TwoFAS\Api;

/**
 * List of error codes returned by API.
 *
 * @package TwoFAS\Api
 */
class Errors
{
    const INVALID_NUMBER_ERROR             = 9010;
    const SMS_TO_LANDLINE                  = 9011;
    const COUNTRY_IS_BLOCKED               = 9012;
    const USER_INPUT_ERROR                 = 9030;
    const INTEGRATION_USER_NOT_FOUND_ERROR = 9031;
    const INVALID_CODE_ERROR_CAN_RETRY     = 9061;
    const INVALID_CODE_ERROR_CAN_NOT_RETRY = 9062;
    const NO_AUTHENTICATIONS               = 9070;
    const AUTH_LIMITATION                  = 9110;
    const NUMBER_LIMITATION                = 9130;
    const CHANNEL_NOT_ACTIVE               = 9014;
}
