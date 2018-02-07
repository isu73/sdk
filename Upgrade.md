# Upgrade

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