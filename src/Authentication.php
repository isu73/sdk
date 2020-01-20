<?php

namespace TwoFAS\Api;

use DateTime;

/**
 * This is an Entity that stores information about authentication.
 *
 * @package TwoFAS\Api
 */
final class Authentication
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var DateTime
     */
    private $createdAt;

    /**
     * @var DateTime
     */
    private $validTo;

    /**
     * @param string   $id
     * @param DateTime $createdAt
     * @param DateTime $validTo
     */
    public function __construct($id, DateTime $createdAt, DateTime $validTo)
    {
        $this->id        = $id;
        $this->createdAt = $createdAt;
        $this->validTo   = $validTo;
    }

    /**
     * @return string
     */
    public function id()
    {
        return $this->id;
    }

    /**
     * @return DateTime
     */
    public function createdAt()
    {
        return $this->createdAt;
    }

    /**
     * @return DateTime
     */
    public function validTo()
    {
        return $this->validTo;
    }

    /**
     * @return bool
     */
    public function isValid()
    {
        $currentDate = new DateTime();

        return $this->validTo > $currentDate;
    }
}
