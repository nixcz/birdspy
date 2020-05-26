<?php

namespace App\Service;

use App\Bird\CommandParameters;
use App\Data\RouteData;
use App\Data\RouteServerData;
use App\Data\RouteTableData;
use DateTime;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Cache\ItemInterface;


class RouteTableService
{

    const KEY_INVALID_COUNTS  = '%s-invalid-counts';
    const KEY_INVALID_ROUTES  = '%s-invalid-routes';
    const KEY_FILTERED_COUNTS = '%s-filtered-counts';
    const KEY_FILTERED_ROUTES = '%s-filtered-routes';

    /**
     * @var BirdReaderInterface
     */
    private $birdReader;

    /**
     * @var RouteTableParser
     */
    private $routeTableParser;

    /**
     * @var RouteServerService
     */
    private $routeServerService;

    /**
     * @var PeerService
     */
    private $peerService;

    /**
     * @var CacheItemPoolInterface
     */
    private $appCacheRoutes;

    /**
     * @var CacheItemPoolInterface
     */
    private $appCacheRouteTables;

    /**
     * @var CacheItemPoolInterface
     */
    private $appCacheSelectedRoutes;


    /**
     * RouteTableService constructor.
     *
     * @param BirdReaderInterface    $birdReader
     * @param RouteTableParser       $routeTableParser
     * @param RouteServerService     $routeServerService
     * @param PeerService            $peerService
     * @param CacheItemPoolInterface $appCacheRoutes
     * @param CacheItemPoolInterface $appCacheRouteTables
     * @param CacheItemPoolInterface $appCacheSelectedRoutes
     */
    public function __construct(
        BirdReaderInterface $birdReader,
        RouteTableParser $routeTableParser,
        RouteServerService $routeServerService,
        PeerService $peerService,
        CacheItemPoolInterface $appCacheRoutes,
        CacheItemPoolInterface $appCacheRouteTables,
        CacheItemPoolInterface $appCacheSelectedRoutes
    ) {
        $this->birdReader             = $birdReader;
        $this->routeTableParser       = $routeTableParser;
        $this->routeServerService     = $routeServerService;
        $this->peerService            = $peerService;
        $this->appCacheRoutes         = $appCacheRoutes;
        $this->appCacheRouteTables    = $appCacheRouteTables;
        $this->appCacheSelectedRoutes = $appCacheSelectedRoutes;
    }


    public function getTableRoutes(RouteServerData $server, CommandParameters $parameters)
    {
        $results = $this->appCacheRoutes->get(
            sprintf(
                '%s-table-routes-%s',
                $server->getId(),
                $parameters->getTable()
            ),
            function (ItemInterface $item) use ($server, $parameters) {
                $data = $this->birdReader->getTableRoutes($server, $parameters);

                return $this->createRouteTables($server, $data);
            }
        );

        return $this->getCachedRoutes($results);
    }


    public function getTableRoutesForPrefix(RouteServerData $server, CommandParameters $parameters)
    {
        $results = $this->appCacheRoutes->get(
            sprintf(
                '%s-table-routes-%s-%s',
                $server->getId(),
                $parameters->getTable(),
                $parameters->getPrefixKey()
            ),
            function (ItemInterface $item) use ($server, $parameters) {
                $data = $this->birdReader->getTableRoutesForPrefix($server, $parameters);

                return $this->createRouteTables($server, $data);
            }
        );

        return $this->getCachedRoutes($results);
    }


    public function getTableRoutesFilteredByCommunity(RouteServerData $server, CommandParameters $parameters)
    {
        $results = $this->appCacheRoutes->get(
            sprintf(
                '%s-table-%s-community-routes-%s',
                $server->getId(),
                $parameters->getTable(),
                $parameters->getBgpCommunitiesKey()
            ),
            function (ItemInterface $item) use ($server, $parameters) {
                $data = $this->birdReader->getTableRoutesFilteredByCommunity($server, $parameters);

                return $this->createRouteTables($server, $data);
            }
        );

        return $this->getCachedRoutes($results);
    }


    public function getImportedRoutes(RouteServerData $server, CommandParameters $parameters)
    {
        $results = $this->appCacheRoutes->get(
            sprintf(
                '%s-imported-routes-%s',
                $server->getId(),
                $parameters->getProtocol()
            ),
            function (ItemInterface $item) use ($server, $parameters) {
                $data = $this->birdReader->getImportedRoutes($server, $parameters);

                return $this->createRouteTables($server, $data);
            }
        );

        return $this->getCachedRoutes($results);
    }


    public function getImportedRoutesForPrefix(RouteServerData $server, CommandParameters $parameters)
    {
        $results = $this->appCacheRoutes->get(
            sprintf(
                '%s-imported-routes-%s-%s',
                $server->getId(),
                $parameters->getProtocol(),
                $parameters->getPrefixKey()
            ),
            function (ItemInterface $item) use ($server, $parameters) {
                $data = $this->birdReader->getImportedRoutesForPrefix($server, $parameters);

                return $this->createRouteTables($server, $data);
            }
        );

        return $this->getCachedRoutes($results);
    }


    public function getExportedRoutes(RouteServerData $server, CommandParameters $parameters)
    {
        $results = $this->appCacheRoutes->get(
            sprintf(
                '%s-exported-routes-%s',
                $server->getId(),
                $parameters->getExport()
            ),
            function (ItemInterface $item) use ($server, $parameters) {
                $data = $this->birdReader->getExportedRoutes($server, $parameters);

                return $this->createRouteTables($server, $data);
            }
        );

        return $this->getCachedRoutes($results);
    }


    public function getExportedRoutesForPrefix(RouteServerData $server, CommandParameters $parameters)
    {
        $results = $this->appCacheRoutes->get(
            sprintf(
                '%s-exported-routes-%s-%s',
                $server->getId(),
                $parameters->getExport(),
                $parameters->getPrefixKey()
            ),
            function (ItemInterface $item) use ($server, $parameters) {
                $data = $this->birdReader->getExportedRoutesForPrefix($server, $parameters);

                return $this->createRouteTables($server, $data);
            }
        );

        return $this->getCachedRoutes($results);
    }


    private function getFilteredRouteTablesKeys(RouteServerData $server)
    {
        return $this->appCacheSelectedRoutes->get(
            sprintf(self::KEY_FILTERED_ROUTES, $server->getId()),
            function (ItemInterface $item) use ($server) {
                $data = $this->birdReader->getFilteredRoutes($server);

                $results = $this->createRouteTables($server, $data);

                $this->cacheFilteredCounts($server, $results);

                return $results;
            }
        );
    }


    public function getFilteredRoutes(RouteServerData $server)
    {
        $results = $this->getFilteredRouteTablesKeys($server);

        return $this->getCachedRoutes($results);
    }


    public function getTableFilteredRoutes(RouteServerData $server, string $name)
    {
        $results = $this->getFilteredRouteTablesKeys($server);

        return $this->getCachedRoutes($this->narrowResultsForTable($results, $name));
    }


    private function getInvalidRouteTablesKeys(RouteServerData $server)
    {
        return $this->appCacheSelectedRoutes->get(
            sprintf(self::KEY_INVALID_ROUTES, $server->getId()),
            function (ItemInterface $item) use ($server) {
                $data = $this->birdReader->getInvalidRoutes($server);

                $results = $this->createRouteTables($server, $data);

                $this->cacheInvalidCounts($server, $results);

                return $results;
            }
        );
    }


    public function getInvalidRoutes(RouteServerData $server)
    {
        $results = $this->getInvalidRouteTablesKeys($server);

        return $this->getCachedRoutes($results);
    }


    public function getTableInvalidRoutes(RouteServerData $server, string $name)
    {
        $results = $this->getInvalidRouteTablesKeys($server);

        return $this->getCachedRoutes($this->narrowResultsForTable($results, $name));
    }


    private function createRouteTables(RouteServerData $server, string $data)
    {
        $createdAt = new DateTime();

        $neighborNames = $this->peerService->getPeerNamesIndexedByIp($server);
        $neighborAsns  = $this->peerService->getPeerAsnsIndexedByIp($server);

        $keys = [];

        foreach ($this->routeTableParser->getRouteTables($data) as $table) {

            if (preg_match("/(T4_|T6_|master4|master6)/", $table->getName())) {
                $peer = $this->peerService->getPeerByTable($server, $table->getName());
                $table->setPeer($peer);
                $table->setCreatedAt($createdAt);

                foreach ($this->routeTableParser->getRoutes($table->getBlob()) as $route) {

                    if (isset($neighborNames[$route->getNextHop()])) {
                        $route->setNeighborName($neighborNames[$route->getNextHop()]);
                    }

                    if (isset($neighborAsns[$route->getNextHop()])) {
                        $route->setNeighborAsn($neighborAsns[$route->getNextHop()]);
                    }

                    $route->setTableName($table->getName());
                    $route->setTableId($table->getUuid());
                    $route->setPeerName($table->getPeer()->getName());
                    $route->setPeerAsn($table->getPeer()->getAsn());
                    $route->setDescription($table->getPeer()->getDescription());

                    $table->addRoute($route);

                }

                $table->setBlob(null);

                $this->cacheRouteTable($table);

                $keys[$table->getName()] = $table->getUuid();
            }

        }

        return [
            'data'      => $keys,
            'timestamp' => (int) $createdAt->format('U'),
        ];
    }


    private function narrowResultsForTable(array $results, string $name)
    {
        if (! isset($results['data'][$name])) {
            throw new NotFoundHttpException(sprintf('The route table %s does not exist in dataset!', $name));
        }

        return [
            'data'      => [$name => $results['data'][$name]],
            'timestamp' => $results['timestamp'],
        ];
    }


    private function getCachedRoutes(array $results)
    {
        $routes = [];
        foreach ($results['data'] as $key) {

            $item = $this->appCacheRouteTables->getItem($key);

            if ($item->isHit()) {
                /** @var RouteTableData $table */
                $table = $item->get();

                foreach ($table->getRoutes() as $route) {

                    $routes[] = $route;

                }
            }

        }

        return [
            'data'      => $routes,
            'timestamp' => $results['timestamp'],
        ];
    }


    public function getRouteDetail($tableId, $routeId)
    {
        $item = $this->appCacheRouteTables->getItem($tableId);

        if ($item->isHit()) {

            /** @var RouteTableData $table */
            $table = $item->get();
            $route = $table->getRoute($routeId);

            return [
                'data'      => ['blob' => $route->getBlob()],
                'timestamp' => (int) $table->getCreatedAt()->format('U'),
            ];
        }

        return [
            'data'      => ['blob' => 'Cache expired. Try reload page.'],
            'timestamp' => (int) (new DateTime())->format('U'),
        ];
    }


    public function getRoutesJson(array $results)
    {
        $data = [];

        /** @var RouteData $route */
        foreach ($results['data'] as $route) {

            $data[] = $route->toFormattedArray();

        }

        return [
            'data'      => $data,
            'timestamp' => $results['timestamp'],
        ];
    }


    private function cacheRouteTable(RouteTableData $table)
    {
        $item = $this->appCacheRouteTables->getItem($table->getUuid());
        $item->set($table);
        $this->appCacheRouteTables->save($item);
    }


    private function getCounts(array $results)
    {
        $counts = [];

        foreach ($results['data'] as $key) {

            $item = $this->appCacheRouteTables->getItem($key);

            if ($item->isHit()) {
                /** @var RouteTableData $table */
                $table = $item->get();

                $counts[$table->getName()] = count($table->getRoutes());

            }

        }

        return $counts;
    }


    private function cacheFilteredCounts(RouteServerData $server, $results)
    {
        $counts = $this->getCounts($results);

        $item = $this->appCacheSelectedRoutes->getItem(
            sprintf(self::KEY_FILTERED_COUNTS, $server->getId())
        );
        $item->set($counts);
        $this->appCacheSelectedRoutes->save($item);
    }


    private function cacheInvalidCounts(RouteServerData $server, $results)
    {
        $counts = $this->getCounts($results);

        $item = $this->appCacheSelectedRoutes->getItem(
            sprintf(self::KEY_INVALID_COUNTS, $server->getId())
        );
        $item->set($counts);
        $this->appCacheSelectedRoutes->save($item);
    }


    public function saveFilteredRoutes(RouteServerData $server)
    {
        $data    = $this->birdReader->getFilteredRoutes($server);
        $results = $this->createRouteTables($server, $data);

        $item = $this->appCacheSelectedRoutes->getItem(
            sprintf(self::KEY_FILTERED_ROUTES, $server->getId())
        );

        $item->set($results);
        $this->appCacheSelectedRoutes->save($item);

        $this->cacheFilteredCounts($server, $results);
    }


    public function saveInvalidRoutes(RouteServerData $server)
    {
        $data    = $this->birdReader->getInvalidRoutes($server);
        $results = $this->createRouteTables($server, $data);

        $item = $this->appCacheSelectedRoutes->getItem(
            sprintf(self::KEY_INVALID_ROUTES, $server->getId())
        );

        $item->set($results);
        $this->appCacheSelectedRoutes->save($item);

        $this->cacheInvalidCounts($server, $results);
    }

}
