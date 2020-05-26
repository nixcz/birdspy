<?php

namespace App\Data;


class SymbolData
{

    const TYPE_PROTOCOL = 'protocol';
    const TYPE_TABLE    = 'table';

    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $type;

    /**
     * @var PeerData
     */
    private $peer;


    /**
     * SymbolData constructor.
     *
     * @param string $id
     * @param string $type
     */
    public function __construct(string $id, string $type)
    {
        $this->id   = $id;
        $this->type = $type;
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
    public function getType(): string
    {
        return $this->type;
    }


    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
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

}
