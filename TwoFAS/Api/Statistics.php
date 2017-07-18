<?php

namespace TwoFAS\Api;

/**
 * Class Statistics
 *
 * @package TwoFAS\Api
 */
class Statistics
{
    /**
     * @var int
     */
    private $total;

    /**
     * @var array
     */
    private $sms;

    /**
     * @var array
     */
    private $call;

    /**
     * @var array
     */
    private $totp;

    /**
     * @var array
     */
    private $email;

    /**
     * Statistics constructor.
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->total = $data['total'];
        $this->sms   = array(
            'enabled'    => $data['sms']['enabled'],
            'configured' => $data['sms']['configured']
        );
        $this->call  = array(
            'enabled'    => $data['call']['enabled'],
            'configured' => $data['call']['configured']
        );
        $this->totp  = array(
            'enabled'    => $data['totp']['enabled'],
            'configured' => $data['totp']['configured']
        );
        $this->email = array(
            'enabled'    => $data['email']['enabled'],
            'configured' => $data['email']['configured']
        );
    }

    /**
     * @return array
     */
    public function getAll()
    {
        return array(
            'total' => $this->total,
            'sms'   => array(
                'enabled'    => $this->sms['enabled'],
                'configured' => $this->sms['configured']
            ),
            'call'  => array(
                'enabled'    => $this->call['enabled'],
                'configured' => $this->call['configured']
            ),
            'totp'  => array(
                'enabled'    => $this->totp['enabled'],
                'configured' => $this->totp['configured']
            ),
            'email' => array(
                'enabled'    => $this->email['enabled'],
                'configured' => $this->email['configured']
            )
        );
    }

    /**
     * @return int
     */
    public function getTotal()
    {
        return $this->total;
    }
}
