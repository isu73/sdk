<?php

namespace TwoFAS\Api;

use TwoFAS\Api\Exception\InvalidDateException;
use TwoFAS\Api\Response\Response;
use TwoFAS\Encryption\Cryptographer;
use TwoFAS\Encryption\Interfaces\ReadKey;

class Hydrator
{
    /**
     * @param ReadKey  $keyStorage
     * @param Response $response
     *
     * @return IntegrationUser
     */
    public function getIntegrationUserFromResponse(ReadKey $keyStorage, Response $response)
    {
        $data          = $response->getData();
        $cryptographer = Cryptographer::getInstance($keyStorage);
        $user          = new IntegrationUser();
        $user
            ->setId($data['id'])
            ->setExternalId($data['external_id'])
            ->setMobileSecret($data['mobile_secret'])
            ->setBackupCodesCount($data['backup_codes_count'])
            ->setHasMobileUser($data['has_mobile_user'])
            ->setPhoneNumber($cryptographer->decrypt($data['phone_number']))
            ->setEmail($cryptographer->decrypt($data['email']))
            ->setTotpSecret($cryptographer->decrypt($data['totp_secret']));

        return $user;
    }

    /**
     * @param Response $response
     *
     * @return Authentication
     *
     * @throws InvalidDateException
     */
    public function getAuthenticationFromResponse(Response $response)
    {
        $data = $response->getData();

        return new Authentication(
            $data['id'],
            Dates::convertUTCFormatToLocal($data['created_at']),
            Dates::convertUTCFormatToLocal($data['valid_to'])
        );
    }
}