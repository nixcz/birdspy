<?php

namespace App\Data;

use App\Data\Traits\TimestampAbleTrait;
use App\Utils\CommonFilters;
use DateTime;


class BfdSessionData
{

    use TimestampAbleTrait;

    /**
     * @var string
     */
    private $ipAddress;

    /**
     * @var string
     */
    private $interface;

    /**
     * @var string
     */
    private $state;

    /**
     * @var DateTime
     */
    private $since;

    /**
     * @var float
     */
    private $interval;

    /**
     * @var float
     */
    private $timeout;

    /**
     * @var PeerData
     */
    private $peer;


    /**
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }


    /**
     * @param string $ipAddress
     */
    public function setIpAddress(string $ipAddress): void
    {
        $this->ipAddress = $ipAddress;
    }


    /**
     * @return string
     */
    public function getInterface(): string
    {
        return $this->interface;
    }


    /**
     * @param string $interface
     */
    public function setInterface(string $interface): void
    {
        $this->interface = $interface;
    }


    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }


    /**
     * @param string $state
     */
    public function setState(string $state): void
    {
        $this->state = $state;
    }


    /**
     * @return DateTime
     */
    public function getSince(): DateTime
    {
        return $this->since;
    }


    /**
     * @param DateTime $since
     */
    public function setSince(DateTime $since): void
    {
        $this->since = $since;
    }


    /**
     * @return float
     */
    public function getInterval(): float
    {
        return $this->interval;
    }


    /**
     * @param float $interval
     */
    public function setInterval(float $interval): void
    {
        $this->interval = $interval;
    }


    /**
     * @return float
     */
    public function getTimeout(): float
    {
        return $this->timeout;
    }


    /**
     * @param float $timeout
     */
    public function setTimeout(float $timeout): void
    {
        $this->timeout = $timeout;
    }


    /**
     * @return PeerData
     */
    public function getPeer(): PeerData
    {
        return $this->peer;
    }


    /**
     * @param PeerData $peer
     */
    public function setPeer(PeerData $peer): void
    {
        $this->peer = $peer;
    }


    /**
     * @return array
     */
    public function toFormattedArray()
    {
        return [
            'peer_name'   => $this->getPeer()->getName(),
            'table'       => $this->getPeer()->getTable(),
            'ip_address'  => $this->getIpAddress(),
            'description' => $this->getPeer()->getDescription(),
            'asn'         => $this->getPeer()->getAsn(),
            'interface'   => $this->getInterface(),
            'state'       => $this->getState(),
            'since'       => [
                'value'     => $this->getSince() ? $this->getSince()->format('Y-m-d H:i:s') : null,
                'timestamp' => $this->getSince() ? (int) $this->getSince()->format('U') : null,
            ],
            'interval'    => $this->getInterval(),
            'timeout'     => $this->getTimeout(),
        ];
    }

}
