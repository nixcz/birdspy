<?php

namespace App\Data;

use App\Data\Traits\UniversallyUniqueIdentifierTrait;


class RouteNetworkData
{

    use UniversallyUniqueIdentifierTrait;

    /**
     * @var string
     */
    private $table;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string|null
     */
    private $blob;


    /**
     * RouteNetworkData constructor.
     */
    public function __construct()
    {
        $this->id = UniversallyUniqueIdentifierTrait::createUuid();
    }


    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }


    /**
     * @param string $table
     */
    public function setTable(string $table): void
    {
        $this->table = $table;
    }


    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }


    /**
     * @param string $prefix
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }


    /**
     * @return string|null
     */
    public function getBlob(): ?string
    {
        return $this->blob;
    }


    /**
     * @param string|null $blob
     */
    public function setBlob(?string $blob = null): void
    {
        $this->blob = $blob;
    }

}
