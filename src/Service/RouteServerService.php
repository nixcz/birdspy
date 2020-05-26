<?php

namespace App\Service;

use App\Data\RouteServerData;
use App\Data\SymbolData;
use DateTime;
use Exception;
use IPTools;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Cache\ItemInterface;


class RouteServerService
{

    const KEY_SERVER  = 'server-%s';
    const KEY_SERVERS = 'servers';

    /**
     * @var ParameterBagInterface
     */
    private $parameters;

    /**
     * @var BirdReaderInterface
     */
    private $birdReader;

    /**
     * @var RouteServerParser
     */
    private $routeServerParser;

    /**
     * @var SymbolService
     */
    private $symbolService;

    /**
     * @var CacheItemPoolInterface
     */
    private $appCacheRouteServers;


    /**
     * RouteServerService constructor.
     *
     * @param ParameterBagInterface  $parameters
     * @param BirdReaderInterface    $birdReader
     * @param RouteServerParser      $routeServerParser
     * @param SymbolService          $symbolService
     * @param CacheItemPoolInterface $appCacheRouteServers
     */
    public function __construct(
        ParameterBagInterface $parameters,
        BirdReaderInterface $birdReader,
        RouteServerParser $routeServerParser,
        SymbolService $symbolService,
        CacheItemPoolInterface $appCacheRouteServers
    ) {
        $this->parameters           = $parameters;
        $this->birdReader           = $birdReader;
        $this->routeServerParser    = $routeServerParser;
        $this->symbolService        = $symbolService;
        $this->appCacheRouteServers = $appCacheRouteServers;
    }


    /**
     * @return RouteServerData[]|array
     */
    public function getServers()
    {
        return $this->appCacheRouteServers->get(
            self::KEY_SERVERS,
            function (ItemInterface $item) {
                return $this->createServers();
            }
        );
    }


    /**
     * @param string $id
     *
     * @return RouteServerData
     */
    public function getServerById(string $id)
    {
        $servers = $this->getServers();

        if (! isset($servers[$id])) {
            throw new NotFoundHttpException(sprintf('The route server %s does not exist!', $id));
        }

        return $servers[$id];
    }


    public function createServers()
    {
        $createdAt = new DateTime();

        $servers = [];
        foreach ($this->parameters->get('bird.servers') as $id => $value) {
            $server = new RouteServerData($id);
            $server->setName($value['name']);
            $server->setDescription($value['description']);
            $server->setSocket($value['socket']);
            $server->setVersionMask($value['version_mask']);
            $server->setCreatedAt($createdAt);

            // Update Server Status
            $data = $this->birdReader->getStatus($server);
            $this->routeServerParser->updateServerStatus($server, $data);

            $this->cacheServer($server);

            $servers[$id] = $server;
        }

        return $servers;
    }


    private function cacheServer(RouteServerData $server)
    {
        $item = $this->appCacheRouteServers->getItem(
            sprintf(self::KEY_SERVER, $server->getId())
        );
        $item->set($server);
        $this->appCacheRouteServers->save($item);
    }


    public function saveServers()
    {
        $servers = $this->createServers();

        $item = $this->appCacheRouteServers->getItem(
            self::KEY_SERVERS
        );
        $item->set($servers);
        $this->appCacheRouteServers->save($item);
    }


    private function checkServersExistence()
    {
        $item = $this->appCacheRouteServers->getItem(
            sprintf(self::KEY_SERVERS)
        );

        if (! $item->isHit()) {
            $this->saveServers();
        }
    }


    /**
     * @param RouteServerData $server
     * @param string          $id
     *
     * @return SymbolData
     */
    public function getProtocolById(RouteServerData $server, string $id)
    {
        return $this->symbolService->getProtocolById($server, $id);
    }


    /**
     * @param RouteServerData $server
     * @param string          $id
     *
     * @return SymbolData
     */
    public function getTableById(RouteServerData $server, string $id)
    {
        return $this->symbolService->getTableById($server, $id);
    }


    /**
     * @param string $cidr
     *
     * @return string
     */
    public function getPrefixByCidr($cidr)
    {
        if (empty($cidr)) {
            throw new NotFoundHttpException(sprintf('Prefix does not exist!'));
        }

        try {
            $network = IPTools\Network::parse($cidr);

        } catch (Exception $e) {
            throw new NotFoundHttpException(sprintf('Prefix %s does not look like a valid CIDR!', $cidr));
        }

        return (string) $network;
    }

}
