<?php

namespace App\Data;

use App\Data\Traits\TimestampAbleTrait;
use App\Data\Traits\UniversallyUniqueIdentifierTrait;


class RouteTableData
{

    use TimestampAbleTrait;
    use UniversallyUniqueIdentifierTrait;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $blob;

    /**
     * @var PeerData
     */
    private $peer;

    /**
     * @var RouteData[]|array
     */
    private $routes = [];


    /**
     * RouteTableData constructor.
     */
    public function __construct()
    {
        $this->id = UniversallyUniqueIdentifierTrait::createUuid();
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
     * @return RouteData[]|array
     */
    public function getRoutes()
    {
        return $this->routes;
    }


    /**
     * @param RouteData[]|array $routes
     */
    public function setRoutes($routes): void
    {
        $this->routes = $routes;
    }


    public function addRoute(RouteData $route)
    {
        $this->routes[$route->getUuid()] = $route;
    }


    /**
     * @param string $key
     *
     * @return RouteData|null
     */
    public function getRoute(string $key)
    {
        if (isset($this->routes[$key])) {
            return $this->routes[$key];
        }

        return null;
    }

}
