<?php

namespace App\Service;

use App\Data\BgpProtocolData;
use App\Data\RouteServerData;
use DateTime;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\Cache\ItemInterface;


class BgpProtocolService
{

    const KEY_BGP_PROTOCOLS = '%s-bgp-protocols';

    /**
     * @var BirdReaderInterface
     */
    private $birdReader;

    /**
     * @var BgpProtocolParser
     */
    private $bgpProtocolParser;

    /**
     * @var CacheItemPoolInterface
     */
    private $appCacheBgpProtocols;

    /**
     * @var CacheItemPoolInterface
     */
    private $appCacheBgpProtocolDetails;

    /**
     * @var CacheItemPoolInterface
     */
    private $appCacheSelectedRoutes;


    /**
     * BgpProtocolService constructor.
     *
     * @param BirdReaderInterface    $birdReader
     * @param BgpProtocolParser      $bgpProtocolParser
     * @param CacheItemPoolInterface $appCacheBgpProtocols
     * @param CacheItemPoolInterface $appCacheBgpProtocolDetails
     * @param CacheItemPoolInterface $appCacheSelectedRoutes
     */
    public function __construct(
        BirdReaderInterface $birdReader,
        BgpProtocolParser $bgpProtocolParser,
        CacheItemPoolInterface $appCacheBgpProtocols,
        CacheItemPoolInterface $appCacheBgpProtocolDetails,
        CacheItemPoolInterface $appCacheSelectedRoutes
    ) {
        $this->birdReader                 = $birdReader;
        $this->bgpProtocolParser          = $bgpProtocolParser;
        $this->appCacheBgpProtocols       = $appCacheBgpProtocols;
        $this->appCacheBgpProtocolDetails = $appCacheBgpProtocolDetails;
        $this->appCacheSelectedRoutes     = $appCacheSelectedRoutes;
    }


    /**
     * @param RouteServerData $server
     *
     * @return BgpProtocolData[]|array
     */
    public function getBgpProtocols(RouteServerData $server)
    {
        return $this->appCacheBgpProtocols->get(
            sprintf(self::KEY_BGP_PROTOCOLS, $server->getId()),
            function (ItemInterface $item) use ($server) {
                return $this->createBgpProtocols($server);
            }
        );
    }


    public function getBgpProtocolsJson(RouteServerData $server)
    {
        $data = [];

        $results = $this->getBgpProtocols($server);

        /** @var BgpProtocolData $protocol */
        foreach ($results['data'] as $protocol) {
            $data[] = $protocol->toFormattedArray();
        }

        return [
            'data'      => $data,
            'timestamp' => $results['timestamp'],
        ];
    }


    public function getBgpProtocolDetail($id)
    {
        $item = $this->appCacheBgpProtocolDetails->getItem($id);

        if ($item->isHit()) {
            /** @var BgpProtocolData $protocol */
            $protocol = $item->get();

            return [
                'data'      => ['blob' => $protocol->getBlob()],
                'timestamp' => (int) $protocol->getCreatedAt()->format('U'),
            ];
        }

        return [
            'data'      => ['blob' => 'Cache expired. Try reload page.'],
            'timestamp' => (int) (new DateTime())->format('U'),
        ];
    }


    private function createBgpProtocols(RouteServerData $server)
    {
        $createdAt = new DateTime();

        $data = $this->birdReader->getBgpProtocols($server);

        $selectedCounts = $this->getSelectedRoutesCounts($server);
        $invalidCounts  = $this->getInvalidRoutesCounts($server);

        $protocols = [];

        /** @var BgpProtocolData $protocol */
        foreach ($this->bgpProtocolParser->getBgpProtocols($data) as $protocol) {

            $protocol->setCreatedAt($createdAt);

            if (isset($selectedCounts[$protocol->getTable()])) {
                $protocol->setSelectedRoutes($selectedCounts[$protocol->getTable()]);
            }

            if (isset($invalidCounts[$protocol->getTable()])) {
                $protocol->setInvalidRoutes($invalidCounts[$protocol->getTable()]);
            }

            $this->cacheBgpProtocol($protocol);

            $protocols[] = $protocol;
        }

        return [
            'data'      => $protocols,
            'timestamp' => (int) $createdAt->format('U'),
        ];
    }


    private function cacheBgpProtocol(BgpProtocolData $protocol)
    {
        $item = $this->appCacheBgpProtocolDetails->getItem(
            $protocol->getUuid()
        );
        $item->set($protocol);
        $this->appCacheBgpProtocolDetails->save($item);
    }


    public function saveBgpProtocols(RouteServerData $server)
    {
        $protocols = $this->createBgpProtocols($server);

        $item = $this->appCacheBgpProtocols->getItem(
            sprintf(self::KEY_BGP_PROTOCOLS, $server->getId())
        );
        $item->set($protocols);
        $this->appCacheBgpProtocols->save($item);
    }


    private function getSelectedRoutesCounts(RouteServerData $server)
    {
        $item = $this->appCacheSelectedRoutes->getItem(
            sprintf(RouteTableService::KEY_FILTERED_COUNTS, $server->getId())
        );

        if ($item->isHit()) {
            return $item->get();
        }

        return [];
    }


    private function getInvalidRoutesCounts(RouteServerData $server)
    {
        $item = $this->appCacheSelectedRoutes->getItem(
            sprintf(RouteTableService::KEY_INVALID_COUNTS, $server->getId())
        );

        if ($item->isHit()) {
            return $item->get();
        }

        return [];
    }

}
