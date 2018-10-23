<?php

namespace TwoFAS\Api;

use InvalidArgumentException;
use TwoFAS\Encryption\Cryptographer;

/**
 * This is an Entity that maps your local user to 2FAS user, and stores settings.
 *
 * @package TwoFAS\Api
 */
final class IntegrationUser
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var null|string
     */
    private $externalId;

    /**
     * @var null|string
     */
    private $phoneNumber;

    /**
     * @var null|string
     */
    private $email;

    /**
     * @var null|string
     */
    private $totpSecret;

    /**
     * @var null|string
     */
    private $mobileSecret;

    /**
     * @var int
     */
    private $backupCodesCount;

    /**
     * @var bool
     */
    private $hasMobileUser;

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     *
     * @return IntegrationUser
     *
     * @throws InvalidArgumentException
     */
    public function setId($id)
    {
        if (null === $id || '' === $id) {
            throw new InvalidArgumentException('Id of Integration User should not be empty');
        }

        $this->id = (string) $id;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @param null|string $externalId
     *
     * @return IntegrationUser
     */
    public function setExternalId($externalId)
    {
        if (null === $externalId || '' === $externalId) {
            $this->externalId = null;
            return $this;
        }
        $this->externalId = (string) $externalId;
        return $this;
    }

    /**
     * @return FormattedNumber
     */
    public function getPhoneNumber()
    {
        return new FormattedNumber($this->phoneNumber);
    }

    /**
     * @param null|string $phoneNumber
     *
     * @return IntegrationUser
     */
    public function setPhoneNumber($phoneNumber)
    {
        if (null === $phoneNumber || '' === $phoneNumber) {
            $this->phoneNumber = null;
            return $this;
        }
        $this->phoneNumber = (string) $phoneNumber;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param null|string $email
     *
     * @return IntegrationUser
     */
    public function setEmail($email)
    {
        if (null === $email || '' === $email) {
            $this->email = null;
            return $this;
        }
        $this->email = (string) $email;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getTotpSecret()
    {
        return $this->totpSecret;
    }

    /**
     * @param null|string $totpSecret
     *
     * @return IntegrationUser
     */
    public function setTotpSecret($totpSecret)
    {
        if (null === $totpSecret || '' === $totpSecret) {
            $this->totpSecret = null;
            return $this;
        }
        $this->totpSecret = (string) $totpSecret;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getMobileSecret()
    {
        return $this->mobileSecret;
    }

    /**
     * @param null|string $mobileSecret
     *
     * @return IntegrationUser
     */
    public function setMobileSecret($mobileSecret)
    {
        if (null === $mobileSecret || '' === $mobileSecret) {
            $this->mobileSecret = null;
            return $this;
        }
        $this->mobileSecret = (string) $mobileSecret;
        return $this;
    }

    /**
     * @return int
     */
    public function getBackupCodesCount()
    {
        return $this->backupCodesCount;
    }

    /**
     * @param int $backupCodesCount
     *
     * @return IntegrationUser
     */
    public function setBackupCodesCount($backupCodesCount)
    {
        if (!is_int($backupCodesCount)) {
            throw new InvalidArgumentException('Backup Codes count should be a number');
        }

        $this->backupCodesCount = $backupCodesCount;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasMobileUser()
    {
        return $this->hasMobileUser;
    }

    /**
     * @param bool $hasMobileUser
     *
     * @return $this
     */
    public function setHasMobileUser($hasMobileUser)
    {
        if (!is_bool($hasMobileUser)) {
            throw new InvalidArgumentException('Has Mobile User should be a boolean');
        }

        $this->hasMobileUser = $hasMobileUser;
        return $this;
    }

    /**
     * @param Cryptographer $cryptographer
     *
     * @return array
     */
    public function getEncryptedDataAsArray(Cryptographer $cryptographer)
    {
        $data                  = array();
        $data['id']            = $this->id;
        $data['external_id']   = $this->externalId;
        $data['mobile_secret'] = $this->mobileSecret;
        $data['phone_number']  = $cryptographer->encrypt($this->getPhoneNumber()->phoneNumber());
        $data['email']         = $cryptographer->encrypt($this->getEmail());
        $data['totp_secret']   = $cryptographer->encrypt($this->getTotpSecret());

        return $data;
    }
}