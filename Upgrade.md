# Upgrade

### 6.0.0 > 7.0.0
Main class renamed to `Sdk`.
Php version requirement increased to `>=5.4`.
Uses OAuth, you can generate token in [dashboard](https://dashboard.2fas.com).
Added optional caching for `formatNumber` method, by default uses regular array.
Class `AuthenticationCollection` has been removed, all methods previously using it take array of ids instead.

### 5.0.0 > 6.0.0

#### ValidationRules
Deprecated class has been removed, use `TwoFAS\ValidationRules\ValidationRules` instead.

#### IntegrationUser
Field 'active_method' has been removed.

#### RequestAuth
Method has been removed, use specific type of authentication instead.

### 4.0.6 > 5.0.0

#### Storage
KeyStorage interface is split into ReadKey and WriteKey, so you don't have to implement 
`write` when you don't need it.

### 3.0.15 > 4.0.0

#### Encryption Keys
You will need to regenerate Integrations public_key and private_key. 
You can do it in Integration edit in [dashboard](https://dashboard.2fas.com).

#### Exceptions
AESCipher, AESGeneratedKey, AESIVGenerator and Cryptographer methods will 
throw AesException on openssl failure.