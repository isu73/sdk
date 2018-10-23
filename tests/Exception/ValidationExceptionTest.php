<?php

use TwoFAS\Api\Exception\ValidationException;
use TwoFAS\ValidationRules\ValidationRules;

class ValidationExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testRequiredValidationRule()
    {
        $errors = array('error' => array(
            'code' => 9030,
            'msg'  => array(
                'code' => array(
                    'validation.required'
                )
            )
        ));

        $exception = $this->getException($errors);
        $this->assertEquals(array(ValidationRules::REQUIRED), $exception->getError('code'));

    }

    public function testRequiredWithValidationRule()
    {
        $errors = array('error' => array(
            'code' => 9030,
            'msg'  => array(
                'code' => array(
                    'validation.required_with:sms'
                )
            )
        ));

        $exception = $this->getException($errors);
        $this->assertEquals(array(ValidationRules::REQUIRED_WITH), $exception->getError('code'));
    }

    public function testRequiredIfValidationRule()
    {
        $errors = array('error' => array(
            'code' => 9030,
            'msg'  => array(
                'code' => array(
                    'validation.required_if:method,sms,call'
                )
            )
        ));

        $exception = $this->getException($errors);
        $this->assertEquals(array(ValidationRules::REQUIRED_IF), $exception->getError('code'));
    }

    public function testUniqueValidationRule()
    {
        $errors = array('error' => array(
            'code' => 9030,
            'msg'  => array(
                'code' => array(
                    'validation.unique'
                )
            )
        ));

        $exception = $this->getException($errors);
        $this->assertEquals(array(ValidationRules::UNIQUE), $exception->getError('code'));
    }

    public function testUniquePhoneNumberValidationRule()
    {
        $errors = array('error' => array(
            'code' => 9030,
            'msg'  => array(
                'code' => array(
                    'validation.unique_phone_number'
                )
            )
        ));

        $exception = $this->getException($errors);
        $this->assertEquals(array(ValidationRules::UNIQUE_PHONE_NUMBER), $exception->getError('code'));
    }

    public function testPusherSocketIdValidationRule()
    {
        $errors = array('error' => array(
            'code' => 9030,
            'msg'  => array(
                'code' => array(
                    'validation.pusher_socket_id'
                )
            )
        ));

        $exception = $this->getException($errors);
        $this->assertEquals(array(ValidationRules::PUSHER_SOCKET_ID), $exception->getError('code'));
    }

    public function testPusherChannelNameValidationRule()
    {
        $errors = array('error' => array(
            'code' => 9030,
            'msg'  => array(
                'code' => array(
                    'validation.pusher_channel_name'
                )
            )
        ));

        $exception = $this->getException($errors);
        $this->assertEquals(array(ValidationRules::PUSHER_CHANNEL_NAME), $exception->getError('code'));
    }

    public function testIntegrationChannelNameValidationRule()
    {
        $errors = array('error' => array(
            'code' => 9030,
            'msg'  => array(
                'code' => array(
                    'validation.private_integration_channel'
                )
            )
        ));

        $exception = $this->getException($errors);
        $this->assertEquals(array(ValidationRules::INTEGRATION_CHANNEL_NAME), $exception->getError('code'));
    }

    public function testRegexDot()
    {
        $errors = array('error' => array(
            'code' => 9030,
            'msg'  => array(
                'code' => array(
                    'validationDstring'
                )
            )
        ));

        $exception = $this->getException($errors);
        $this->assertEquals(array('validation.unsupported'), $exception->getError('code'));
    }

    public function testUnsupportedValidationRule()
    {
        $errors = array('error' => array(
            'code' => 9030,
            'msg'  => array(
                'code' => array(
                    'validation.new_not_existing'
                )
            )
        ));

        $exception = $this->getException($errors);
        $this->assertEquals(array('validation.unsupported'), $exception->getError('code'));
    }

    public function testGetErrors()
    {
        $errors = array('error' => array(
            'code' => 9030,
            'msg'  => array(
                'code'        => array(
                    'validation.required'
                ),
                'method'      => array(
                    'validation.string'
                ),
                'phone'       => array(
                    'validation.two_f_a_s_formattable'
                ),
                'totp_secret' => array(
                    'validation.not_exists'
                ),
                'email'       => array(
                    'validation.required_if:method,email'
                ),
                'call'        => array(
                    'validation.max,string',
                    'validation.regex'
                )
            )
        ));

        $expectedErrors = array(
            'code'        => array(
                'validation.required'
            ),
            'method'      => array(
                'validation.string'
            ),
            'phone'       => array(
                'validation.two_f_a_s_formattable'
            ),
            'totp_secret' => array(
                'validation.unsupported'
            ),
            'email'       => array(
                'validation.required_if'
            ),
            'call'        => array(
                'validation.unsupported',
                'validation.regex'
            )
        );

        $exception = $this->getException($errors);
        $this->assertEquals($expectedErrors, $exception->getErrors());
    }

    public function testGetBareError()
    {
        $errors = array('error' => array(
            'code' => 9030,
            'msg'  => array(
                'totp_secret' => array(
                    'validation.string,unique'
                ),
                'email'       => array(
                    'validation.required_if:method,email'
                ),
                'call'        => array(
                    'validation.max,string',
                    'validation.regex'
                )
            )
        ));

        $exception = $this->getException($errors);
        $this->assertEquals(array('validation.string,unique'), $exception->getBareError('totp_secret'));
        $this->assertEquals(array('validation.required_if:method,email'), $exception->getBareError('email'));
        $this->assertEquals(array('validation.max,string', 'validation.regex'), $exception->getBareError('call'));
    }

    public function testHasError()
    {
        $errors = array('error' => array(
            'code' => 9030,
            'msg'  => array(
                'totp_secret' => array(
                    'validation.string'
                ),
                'email'       => array(
                    'validation.required_if:method,email'
                ),
                'call'        => array(
                    'validation.max,string',
                    'validation.regex'
                )
            )
        ));

        $exception = $this->getException($errors);
        $this->assertTrue($exception->hasError('totp_secret', ValidationRules::STRING));
        $this->assertTrue($exception->hasError('email', ValidationRules::REQUIRED_IF));
        $this->assertFalse($exception->hasError('email', ValidationRules::REQUIRED));
        $this->assertTrue($exception->hasError('call', ValidationRules::REGEX));
        $this->assertFalse($exception->hasError('call', ValidationRules::MAX));
    }

    private function getException(array $errors)
    {
        return new ValidationException($errors['error']['msg']);
    }
}
