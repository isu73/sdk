<?php

namespace TwoFAS\Api;

/**
 * Class AuthenticationCollection
 *
 * @package TwoFAS\Api
 */
class AuthenticationCollection
{
    /**
     * @var Authentication[]
     */
    private $authentications = array();

    /**
     * @param Authentication $authentication
     */
    public function add(Authentication $authentication)
    {
        $this->authentications[] = $authentication;
    }

    /**
     * Return array of authentications ids
     *
     * @return array
     */
    public function getIds()
    {
        return array_map(function(Authentication $authentication) {
            return $authentication->id();
        }, $this->authentications);
    }
}