<?php

namespace App\Data;

use App\Data\Traits\TimestampAbleTrait;
use DateTime;
use Exception;


class RouteServerData
{

    use TimestampAbleTrait;

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $socket;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $versionMask = 'v-2.x';

    /**
     * @var string
     */
    private $routerId = 'unknown';

    /**
     * @var DateTime|null
     */
    private $serverTime;

    /**
     * @var DateTime|null
     */
    private $lastReboot;

    /**
     * @var DateTime|null
     */
    private $lastReconfiguration;

    /**
     * @var string|null
     */
    private $message;


    /**
     * RouteServerData constructor.
     *
     * @param string $id
     */
    public function __construct(string $id)
    {
        $this->id = $id;
    }


    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }


    /**
     * @param string $id
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }


    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }


    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }


    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }


    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }


    /**
     * @return string
     */
    public function getSocket(): string
    {
        return $this->socket;
    }


    /**
     * @param string $socket
     */
    public function setSocket(string $socket): void
    {
        $this->socket = $socket;
    }


    /**
     * @return string
     */
    public function getVersion(): string
    {
        return $this->version ?: $this->versionMask;
    }


    /**
     * @param string $version
     */
    public function setVersion(string $version): void
    {
        $this->version = $version;
    }


    /**
     * @return string
     */
    public function getVersionMask(): string
    {
        return $this->versionMask;
    }


    /**
     * @param string $versionMask
     */
    public function setVersionMask(string $versionMask): void
    {
        $this->versionMask = $versionMask;
    }


    /**
     * @return string
     */
    public function getRouterId(): string
    {
        return $this->routerId;
    }


    /**
     * @param string $routerId
     */
    public function setRouterId(string $routerId): void
    {
        $this->routerId = $routerId;
    }


    /**
     * @return DateTime|null
     */
    public function getServerTime(): ?DateTime
    {
        return $this->serverTime;
    }


    /**
     * @param DateTime|null $serverTime
     */
    public function setServerTime(?DateTime $serverTime): void
    {
        $this->serverTime = $serverTime;
    }


    /**
     * @return DateTime|null
     */
    public function getLastReboot(): ?DateTime
    {
        return $this->lastReboot;
    }


    /**
     * @param DateTime|null $lastReboot
     */
    public function setLastReboot(?DateTime $lastReboot): void
    {
        $this->lastReboot = $lastReboot;
    }


    /**
     * @return DateTime|null
     */
    public function getLastReconfiguration(): ?DateTime
    {
        return $this->lastReconfiguration;
    }


    /**
     * @param DateTime|null $lastReconfiguration
     */
    public function setLastReconfiguration(?DateTime $lastReconfiguration): void
    {
        $this->lastReconfiguration = $lastReconfiguration;
    }


    /**
     * @return string|null
     */
    public function getMessage(): ?string
    {
        return $this->message;
    }


    /**
     * @param string|null $message
     */
    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }


    /**
     * @return int
     */
    public function getUptime(): int
    {
        if (! $this->getLastReboot()) {
            return 0;
        }

        try {
            $lastReboot = clone $this->getLastReboot();

            return $lastReboot->diff(new DateTime)->format('%a');

        } catch (Exception $e) {
            return 0;
        }
    }

}
