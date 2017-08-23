# Install

### via composer

    composer require twofas/sdk : "3.*"

# Documentation

#### Creating client

```php
$twoFAS = new \TwoFAS\Api\TwoFAS('login', 'api_key');
```

#### All methods

All methods can throw following exceptions:

###### Unsuccessful

* `AuthorizationException` in case of invalid credentials

```php
Exception 'TwoFAS\Api\Exception\AuthorizationException'
with message 'Invalid credentials'
```
* `Exception` in case of unspecified type of exception

```php
Exception 'TwoFAS\Api\Exception\Exception'
with message 'Unsupported response'
```

Additional exceptions are described for each method

# Methods

## formatNumber

Used for checking if number is valid and to unify format.
You can store unified number in DB to prevent creation of multiple users with same phone number.

#### Parameters
Name | Type | Description
--- | --- | ---
$phoneNumber | `string` | Phone number in any format

#### Example

```php
$formatted = $twoFAS->formatNumber('5123631111');
```

#### Response

###### Successful

##### Returns [TwoFAS\Api\FormattedNumber](#formattednumber) object

###### Unsuccessful

Method can throw additional exceptions:

* `InvalidNumberException` if you passed phone number that cannot be parsed

```php
Exception 'TwoFAS\Api\Exception\InvalidNumberException'
with message 'Invalid number'
```

## requestAuthViaSms

Used for requesting authentication on user via SMS.
Store authentication id for later use.

#### Parameters
Name | Type | Description
--- | --- | ---
$phoneNumber | `string` | Phone number in any format

#### Example

```php
$authentication = $twoFAS->requestAuthViaSms('5123631111');
```

#### Response

###### Successful

##### Returns [TwoFAS\Api\Authentication](#authentication) object

###### Unsuccessful

Method can throw additional exceptions:

* `AuthenticationsLimitationException` if you make too many authentications in one hour (affects only development keys and phone based authentication types)

```php
Exception 'TwoFAS\Api\Exception\AuthenticationsLimitationException'
with message 'Too many requests'
```
* `ChannelNotActiveException` if channel which is used to make authentication is not active

```php
Exception 'TwoFAS\Api\Exception\ChannelNotActiveException'
with message 'Channel is not active'
```
* `CountryIsBlockedException` if number which is used to make authentication belongs to blocked country within integration

```php
Exception 'TwoFAS\Api\Exception\CountryIsBlockedException'
with message 'Authorization request cannot be made due to blocked country'
```
* `InvalidDateException` in case of invalid date. 
  Should only be expected when used outside of SDK.

```php
Exception 'TwoFAS\Api\Exception\InvalidDateException'
with message ''
```
* `InvalidNumberException` if you passed phone number that cannot be parsed

```php
Exception 'TwoFAS\Api\Exception\InvalidNumberException'
with message 'Invalid number'
```
* `NumbersLimitationException` if number which is used to make authentication is not on development key whitelist

```php
Exception 'TwoFAS\Api\Exception\NumbersLimitationException'
with message 'Development keys can only send to white list'
```
* `PaymentException` if you used a method that requires payment and you cannot be charged

```php
Exception 'TwoFAS\Api\Exception\PaymentException'
with message 'Payment required'
```
* `SmsToLandlineException` if you're trying to send sms to landline which doesn't support it

```php
Exception 'TwoFAS\Api\Exception\SmsToLandlineException'
with message 'Cannot send sms to landline'
```

###### SmsToLandlineException extends InvalidNumberException
* `ValidationException` if you send invalid data in request [more](#more-validationexception)

```php
Exception 'TwoFAS\Api\Exception\ValidationException'
with message 'Validation exception'
```

## requestAuthViaCall

Used for requesting authentication on user via CALL.
Store authentication id for later use.

#### Parameters
Name | Type | Description
--- | --- | ---
$phoneNumber | `string` | Phone number in any format

#### Example

```php
$authentication = $twoFAS->requestAuthViaCall('5123631111');
```

#### Response

###### Successful

##### Returns [TwoFAS\Api\Authentication](#authentication) object

###### Unsuccessful

Method can throw additional exceptions:

* `AuthenticationsLimitationException` if you make too many authentications in one hour (affects only development keys and phone based authentication types)

```php
Exception 'TwoFAS\Api\Exception\AuthenticationsLimitationException'
with message 'Too many requests'
```
* `ChannelNotActiveException` if channel which is used to make authentication is not active

```php
Exception 'TwoFAS\Api\Exception\ChannelNotActiveException'
with message 'Channel is not active'
```
* `CountryIsBlockedException` if number which is used to make authentication belongs to blocked country within integration

```php
Exception 'TwoFAS\Api\Exception\CountryIsBlockedException'
with message 'Authorization request cannot be made due to blocked country'
```
* `InvalidDateException` in case of invalid date. 
  Should only be expected when used outside of SDK.

```php
Exception 'TwoFAS\Api\Exception\InvalidDateException'
with message ''
```
* `InvalidNumberException` if you passed phone number that cannot be parsed

```php
Exception 'TwoFAS\Api\Exception\InvalidNumberException'
with message 'Invalid number'
```
* `NumbersLimitationException` if number which is used to make authentication is not on development key whitelist

```php
Exception 'TwoFAS\Api\Exception\NumbersLimitationException'
with message 'Development keys can only send to white list'
```
* `PaymentException` if you used a method that requires payment and you cannot be charged

```php
Exception 'TwoFAS\Api\Exception\PaymentException'
with message 'Payment required'
```
* `ValidationException` if you send invalid data in request [more](#more-validationexception)

```php
Exception 'TwoFAS\Api\Exception\ValidationException'
with message 'Validation exception'
```

## requestAuthViaEmail

Used for requesting authentication on user via email.
Store authentication id for later use.

#### Parameters
Name | Type | Description
--- | --- | ---
$email | `string` | Email address

#### Example

```php
$authentication = $twoFAS->requestAuthViaEmail('example@example.com');
```

#### Response

###### Successful

##### Returns [TwoFAS\Api\Authentication](#authentication) object

###### Unsuccessful

Method can throw additional exceptions:

* `ChannelNotActiveException` if channel which is used to make authentication is not active

```php
Exception 'TwoFAS\Api\Exception\ChannelNotActiveException'
with message 'Channel is not active'
```
* `InvalidDateException` in case of invalid date. 
  Should only be expected when used outside of SDK.

```php
Exception 'TwoFAS\Api\Exception\InvalidDateException'
with message ''
```
* `ValidationException` if you send invalid data in request [more](#more-validationexception)

```php
Exception 'TwoFAS\Api\Exception\ValidationException'
with message 'Validation exception'
```

## requestAuthViaTotp

Used for requesting authentication on user via TOTP (Time-based One-time Password Algorithm).
Store authentication id for later use.

#### Parameters
Name | Type | Description
--- | --- | ---
$secret | `string` | Totp secret in 16 base32 characters
$mobileSecret | `string` or `null` | Secret used for push notifications

#### Example

```php
$authentication = $twoFAS->requestAuthViaTotp('JBSWY3DPEHPK3PXP');
```

#### Response

###### Successful

##### Returns [TwoFAS\Api\Authentication](#authentication) object

###### Unsuccessful

Method can throw additional exceptions:

* `ChannelNotActiveException` if channel which is used to make authentication is not active

```php
Exception 'TwoFAS\Api\Exception\ChannelNotActiveException'
with message 'Channel is not active'
```
* `InvalidDateException` in case of invalid date. 
  Should only be expected when used outside of SDK.

```php
Exception 'TwoFAS\Api\Exception\InvalidDateException'
with message ''
```
* `ValidationException` if you send invalid data in request [more](#more-validationexception)

```php
Exception 'TwoFAS\Api\Exception\ValidationException'
with message 'Validation exception'
```

## requestAuth

Used for requesting authentication on integration user.
This method merge all previous authenticate methods.
Store authentication id for later use.

#### Parameters
Name | Type | Description
--- | --- | ---
$keyStorage | `KeyStorage` | Your class to keep Key used in encrypt/decrypt data
$userId | `string` | Id of integration user who wants to authenticate

#### Example

```php
$authentication = $twoFAS->requestAuth($keyStorage, '5788b5e5002f0');
```

#### Response

###### Successful

##### Returns [TwoFAS\Api\Authentication](#authentication) object

###### Unsuccessful

Method can throw additional exceptions:

* `AuthenticationsLimitationException` if you make too many authentications in one hour (affects only development keys and phone based authentication types)

```php
Exception 'TwoFAS\Api\Exception\AuthenticationsLimitationException'
with message 'Too many requests'
```
* `ChannelNotActiveException` if channel which is used to make authentication is not active

```php
Exception 'TwoFAS\Api\Exception\ChannelNotActiveException'
with message 'Channel is not active'
```
* `CountryIsBlockedException` if number which is used to make authentication belongs to blocked country within integration

```php
Exception 'TwoFAS\Api\Exception\CountryIsBlockedException'
with message 'Authorization request cannot be made due to blocked country'
```
* `IntegrationUserHasNoActiveMethodException` if integration user haven't got active authenticate method

```php
Exception 'TwoFAS\Api\Exception\IntegrationUserHasNoActiveMethodException'
with message 'No active method'
```
* `IntegrationUserNotFoundException` if there is no integration user with requested id

```php
Exception 'TwoFAS\Api\Exception\IntegrationUserNotFoundException'
with message 'Integration user not found'
```
* `InvalidDateException` in case of invalid date. 
  Should only be expected when used outside of SDK.

```php
Exception 'TwoFAS\Api\Exception\InvalidDateException'
with message ''
```
* `InvalidNumberException` if you passed phone number that cannot be parsed

```php
Exception 'TwoFAS\Api\Exception\InvalidNumberException'
with message 'Invalid number'
```
* `NumbersLimitationException` if number which is used to make authentication is not on development key whitelist

```php
Exception 'TwoFAS\Api\Exception\NumbersLimitationException'
with message 'Development keys can only send to white list'
```
* `PaymentException` if you used a method that requires payment and you cannot be charged

```php
Exception 'TwoFAS\Api\Exception\PaymentException'
with message 'Payment required'
```
* `SmsToLandlineException` if you're trying to send sms to landline which doesn't support it

```php
Exception 'TwoFAS\Api\Exception\SmsToLandlineException'
with message 'Cannot send sms to landline'
```

###### SmsToLandlineException extends InvalidNumberException
* `ValidationException` if you send invalid data in request [more](#more-validationexception)

```php
Exception 'TwoFAS\Api\Exception\ValidationException'
with message 'Validation exception'
```

## checkCode

Used for validating code entered by user.

#### Parameters
Name | Type | Description
--- | --- | ---
$collection | `AuthenticationCollection` | Collection of authentication ids
$code | `string` | Code provided by user

[AuthenticationCollection](#authentication-collection)

#### Example

```php
$checkCode = $twoFAS->checkCode($collection, '123456');

if ($checkCode->accepted()) {

}
```

#### Response

###### Successful

##### Returns instance of [TwoFAS\Api\Code\Code](#code-interface) interface

## checkBackupCode

Used for validating backup code entered by user.

Backup code is expected to be 12 non-omitted characters.
Non-omitted characters consists of subsets: 
  - letters: `abcdefghjkmnpqrstuvwxyz`
  - numbers: `23456789`
  
You can send code with or without `-` separators, code is not case-sensitive.

#### Parameters
Name | Type | Description
--- | --- | ---
$user | `IntegrationUser` | User that wants to use backup code
$collection | `AuthenticationCollection` | Collection of authentication ids
$code | `string` | Code provided by user

[AuthenticationCollection](#authentication-collection)

#### Example

```php
try {
    
    $checkCode = $twoFAS->checkBackupCode($user, $collection, 'aaaa-bbbb-cccc');
    
    if ($checkCode->accepted()) {
    
    }
    
} catch (ValidationException $e) {
    
}
```

#### Response

###### Successful

##### Returns instance of [TwoFAS\Api\Code\Code](#code-interface) interface

###### Unsuccessful

Method can throw additional exceptions:

* `ValidationException` if you send invalid data in request [more](#more-validationexception)

```php
Exception 'TwoFAS\Api\Exception\ValidationException'
with message 'Validation exception'
```

## getIntegrationUsers

Used for getting paginated list of integration users from 2fas.

#### Parameters
Name | Type | Description
--- | --- | ---
$page | `int` or `null` | The page number from which you want to display the results

#### Example

```php
$usersData = $twoFAS->getIntegrationUsers();
```

#### Response

###### Successful

##### Returns collection of IntegrationUsers

## getIntegrationUser

Used for get integration user from 2fas.

#### Parameters
Name | Type | Description
--- | --- | ---
$keyStorage | `KeyStorage` | Your class to keep Key used in encrypt/decrypt data
$userId | `string` | Id of integration user who wants to get


#### Example

```php
$user = $twoFAS->addIntegrationUser($keyStorage, '5788b5e5002f0');
```

#### Response

###### Successful

##### Returns [TwoFAS\Api\IntegrationUser](#integrationuser) object

###### Unsuccessful

Method can throw additional exceptions:

* `IntegrationUserNotFoundException` if there is no integration user with requested id

```php
Exception 'TwoFAS\Api\Exception\IntegrationUserNotFoundException'
with message 'Integration user not found'
```

## getIntegrationUserByExternalId

Used for get integration user from 2fas by your own id.

#### Parameters
Name | Type | Description
--- | --- | ---
$keyStorage | `KeyStorage` | Your class to keep Key used in encrypt/decrypt data
$userExternalId | `string` | External id of integration user who wants to get


#### Example

```php
$user = $twoFAS->getIntegrationUserByExternalId($keyStorage, '468');
```

#### Response

###### Successful

##### Returns [TwoFAS\Api\IntegrationUser](#integrationuser) object

###### Unsuccessful

Method can throw additional exceptions:

* `IntegrationUserNotFoundException` if there is no integration user with requested id

```php
Exception 'TwoFAS\Api\Exception\IntegrationUserNotFoundException'
with message 'Integration user not found'
```

## addIntegrationUser

Used for add integration user to 2fas.

#### Parameters
Name | Type | Description
--- | --- | ---
$keyStorage | `KeyStorage` | Your class to keep Key used in encrypt/decrypt data
$user | `IntegrationUser` | User who want to add to 2fas


#### Example

```php
$user = new IntegrationUser();
$user
    ->setActiveMethod('totp')
    ->setTotpSecret('...')
    //...
$user = $twoFAS->addIntegrationUser($keyStorage, $user);
```

#### Response

###### Successful

##### Returns [TwoFAS\Api\IntegrationUser](#integrationuser) object

###### Unsuccessful

Method can throw additional exceptions:

* `ValidationException` if you send invalid data in request [more](#more-validationexception)

```php
Exception 'TwoFAS\Api\Exception\ValidationException'
with message 'Validation exception'
```

## updateIntegrationUser

Used for update integration user in 2fas.

#### Parameters
Name | Type | Description
--- | --- | ---
$keyStorage | `KeyStorage` | Your class to keep Key used in encrypt/decrypt data
$user | `IntegrationUser` | User who want to update in 2fas


#### Example

```php
$user = $twoFAS->getIntegrationUserByExternalId($keyStorage, '468');
$user
    ->setActiveMethod('totp')
    ->setTotpSecret('...')
    //...
$user = $twoFAS->updateIntegrationUser($keyStorage, $user);
```

#### Response

###### Successful

##### Returns [TwoFAS\Api\IntegrationUser](#integrationuser) object

###### Unsuccessful

Method can throw additional exceptions:

* `IntegrationUserNotFoundException` if there is no integration user with requested id

```php
Exception 'TwoFAS\Api\Exception\IntegrationUserNotFoundException'
with message 'Integration user not found'
```
* `ValidationException` if you send invalid data in request [more](#more-validationexception)

```php
Exception 'TwoFAS\Api\Exception\ValidationException'
with message 'Validation exception'
```

## deleteIntegrationUser

Used for delete integration user from 2fas.

#### Parameters
Name | Type | Description
--- | --- | ---
$userId | `string` | Id of integration user who wants to delete

#### Example

```php
$user = $twoFAS->deleteIntegrationUser('5788b5e5002f0');
```

#### Response

###### Successful

##### Returns boolean (true)

###### Unsuccessful

Method can throw additional exceptions:

* `IntegrationUserNotFoundException` if there is no integration user with requested id

```php
Exception 'TwoFAS\Api\Exception\IntegrationUserNotFoundException'
with message 'Integration user not found'
```

## regenerateBackupCodes

Used for generating new backup codes for [Integration Users](#integrationuser)

#### Parameters
Name | Type | Description
--- | --- | ---
$user | `IntegrationUser` | User who want to get new backup codes


#### Example

```php
$backupCodes = $twoFAS->regenerateBackupCodes($user);
```

#### Response

###### Successful

##### Returns [TwoFAS\Api\BackupCodesCollection](#backup-codes-collection) object

## getStatistics

Used for displaying [Statistics](#statistics).

#### Example

```php
$statistics = $twoFAS->getStatistics();

if ($statistics->getTotal() > 10) {

}
```

#### Response

###### Successful

##### Returns [Statistics](#statistics).

# Helpers

## QrCodeGenerator

QrCodeGenerator object generates base64 encoded image of QR code,
that can be easily displayed for user to scan it with smartphone.

#### Methods
Name | Type | Description
--- | --- | ---
generateBase64($text) | `string` | Returns base64 encoded image

#### Usage
```php
$qrGen = new QrCodeGenerator(QrClientFactory::getInstance());
$qrCode = $qrGen->generateBase64($userSecret);
```
## Dates

Dates object helps converting API date to DateTime object with correct
time and timezone.

#### Methods
Name | Type | Description
--- | --- | ---
convertUTCFormatToLocal($date) | `DateTime` | Converts date format to DateTime

#### Usage
```php
$date     = '2017-01-18 14:21:51';
$dateTime = Dates::convertUTCFormatToLocal($date);
```

# Objects

## IntegrationUser

IntegrationUser object is returned by [getIntegrationUser](#getintegrationuser) method.

It is an [Entity](https://en.wikipedia.org/wiki/Entity) with methods:

#### Methods
Name | Type | Description
--- | --- | ---
getId() | `string` | id
getExternalId() | `string` | external id
getActiveMethod() | `string` | active method
getPhoneNumber() | `string` | phone number
getTotpSecret() | `string` | totp secret
getEmail() | `string` | email
getMobileSecret() | `string` | mobile secret
getBackupCodesCount() | `string` | backup codes count
hasMobileUser() | `bool` | mobile user state

#### Usage
```php
$user->getId();
$user->getPhoneNumber();
//...
```
## FormattedNumber

FormattedNumber object is returned by [formatNumber](#formatnumber) method.

It is a [Value Object](https://en.wikipedia.org/wiki/Value_object) with one method:

#### Methods
Name | Type | Description
--- | --- | ---
phoneNumber() | `string` | Formatted phone number


#### Usage
```php
$formattedNumber->phoneNumber();
```
## Code interface

Code object is returned by [checkCode](#checkcode) method.

It is a [Value Object](https://en.wikipedia.org/wiki/Value_object) with three methods:

#### Methods
Name | Type | Description
--- | --- | ---
authentications() | `array` | Array of authentication ids
accepted() | `boolean` | Result of code checking
canRetry() | `boolean` | Ability to use same ids again


#### Usage
```php
$code->accepted();
$code->authentications();
$code->canRetry();
```
## Authentication

Authentication object is returned by:

* [requestAuth](#requestauth)
* [requestAuthViaSms](#requestauthviasms)
* [requestAuthViaCall](#requestauthviacall)
* [requestAuthViaEmail](#requestauthviaemail)
* [requestAuthViaTotp](#requestauthviatotp)

It is an [Entity](https://en.wikipedia.org/wiki/Entity) with methods:

#### Methods
Name | Type | Description
--- | --- | ---
id() | `string` | Authentication id
createdAt() | `DateTime` | Date of creation (in local timezone)
validTo() | `DateTime` | Date of end of validity (in local timezone)
isValid() | `bool` | Validity date check

#### Usage
```php
$authentication->id();
$authentication->createdAt();
$authentication->validTo();
$authentication->isValid();
```
## Authentication Collection

Authentication Collection object is required by [checkCode](#checkcode) method.

#### Methods
Name | Type | Description
--- | --- | ---
add($authentication) | `void` | Adds [Authentication](#authentication) to collection
getIds() | `array` | Returns array of authentications ids

#### Usage
```php
$authenticationCollection->add($authentication);
```
## BackupCode

BackupCode object is returned in collection by:

* [regenerateBackupCodes](#regeneratebackupcodes)

It is an [Entity](https://en.wikipedia.org/wiki/Entity) with method:

#### Methods
Name | Type | Description
--- | --- | ---
code() | `string` | code

#### Usage
```php
$backupCode->code();
```
## Backup Codes Collection

Backup Codes Collection object is a result of [regenerateBackupCodes](#regeneratebackupcodes) method.

#### Methods
Name | Type | Description
--- | --- | ---
add($code) | `void` | Adds [BackupCode](#backupcode) to collection
getCodes() | `array` | Returns array of backup codes

#### Usage
```php
$codesArray = $backupCodesCollection->getCodes();
```
## Statistics

Statistics object is returned by:

* [getStatistics](#getstatistics)

It is an [Entity](https://en.wikipedia.org/wiki/Entity) with methods:

#### Methods
Name | Type | Description
--- | --- | ---
getAll() | `string` | array of all available statistics
getTotal() | `string` | count of users

#### Usage
```php
$statistics->getTotal();
```

# More about exceptions

## more ValidationException

Validation exceptions may contain multiple keys and rules.
For simplicity of integrating this exception has few methods:

#### Methods
Name | Type | Description
--- | --- | ---
getErrors() | `array` | Returns all errors as constants
getError($key) | `array` or `null` | Returns all failing rules for key (as constants), or null if key passes validation
getBareError($key) | `array` or `null` | Returns all failing rules for key (as bare strings), or null if key passes validation
hasKey($key) | `boolean` | Check if certain field failed validation
hasError($key, $rule) | `boolean` | Check if certain key failed specified rule

