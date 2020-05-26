<?php

namespace App\Service;

use App\Data\BgpProtocolData;
use App\Data\PeerData;
use App\Data\RouteServerData;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class PeerService
{

    const KEY_PEERS          = '%s-peers';
    const KEY_PEERS_IP       = '%s-peers-ip';
    const KEY_PEERS_PROTOCOL = '%s-peers-protocol';
    const KEY_PEERS_TABLE    = '%s-peers-table';

    /**
     * @var ParameterBagInterface
     */
    private $parameters;

    /**
     * @var BgpProtocolService
     */
    private $bgpProtocolService;

    /**
     * @var CacheItemPoolInterface
     */
    private $appCachePeers;


    /**
     * PeerService constructor.
     *
     * @param ParameterBagInterface  $parameters
     * @param BgpProtocolService     $bgpProtocolService
     * @param CacheItemPoolInterface $appCachePeers
     */
    public function __construct(
        ParameterBagInterface $parameters,
        BgpProtocolService $bgpProtocolService,
        CacheItemPoolInterface $appCachePeers
    ) {
        $this->parameters         = $parameters;
        $this->bgpProtocolService = $bgpProtocolService;
        $this->appCachePeers      = $appCachePeers;
    }


    public function getPeers(RouteServerData $server)
    {
        return $this->appCachePeers->get(
            sprintf(self::KEY_PEERS, $server->getId()),
            function () use ($server) {
                return $this->createPeers($server);
            }
        );
    }


    public function getPeersIndexedByIp(RouteServerData $server)
    {
        return $this->appCachePeers->get(
            sprintf(self::KEY_PEERS_IP, $server->getId()),
            function () use ($server) {
                $results = $this->getPeers($server);

                $peers = [];

                /** @var PeerData $peer */
                foreach ($results['data'] as $peer) {
                    $peers[$peer->getIp()] = $peer;
                }

                return [
                    'data'      => $peers,
                    'timestamp' => $results['timestamp'],
                ];
            }
        );
    }


    public function getPeerNamesIndexedByIp(RouteServerData $server)
    {
        $results = $this->getPeers($server);

        $names = [];

        /** @var PeerData $peer */
        foreach ($results['data'] as $peer) {
            $names[$peer->getIp()] = $peer->getName();
        }

        return $names;
    }


    public function getPeerAsnsIndexedByIp(RouteServerData $server)
    {
        $results = $this->getPeers($server);

        $numbers = [];

        /** @var PeerData $peer */
        foreach ($results['data'] as $peer) {
            $numbers[$peer->getIp()] = $peer->getAsn();
        }

        return $numbers;
    }


    public function getPeerByIp(RouteServerData $server, $ip)
    {
        $results = $this->getPeersIndexedByIp($server);

        if (isset($results['data'][$ip])) {
            return $results['data'][$ip];
        }

        $peer = new PeerData();
        $peer->setIp($ip);

        return $peer;
    }


    public function getPeersIndexedByProtocol(RouteServerData $server)
    {
        return $this->appCachePeers->get(
            sprintf(self::KEY_PEERS_PROTOCOL, $server->getId()),
            function () use ($server) {
                $results = $this->getPeers($server);

                $peers = [];

                /** @var PeerData $peer */
                foreach ($results['data'] as $peer) {
                    $peers[$peer->getProtocol()] = $peer;
                }

                return [
                    'data'      => $peers,
                    'timestamp' => $results['timestamp'],
                ];
            }
        );
    }


    public function getPeerByProtocol(RouteServerData $server, $protocol)
    {
        $results = $this->getPeersIndexedByProtocol($server);

        if (isset($results['data'][$protocol])) {
            return $results['data'][$protocol];
        }

        $peer = new PeerData();
        $peer->setProtocol($protocol);

        return $peer;
    }


    public function getPeersIndexedByTable(RouteServerData $server)
    {
        return $this->appCachePeers->get(
            sprintf(self::KEY_PEERS_TABLE, $server->getId()),
            function () use ($server) {
                $results = $this->getPeers($server);

                $peers = [];

                /** @var PeerData $peer */
                foreach ($results['data'] as $peer) {
                    $peers[$peer->getTable()] = $peer;
                }

                return [
                    'data'      => $peers,
                    'timestamp' => $results['timestamp'],
                ];
            }
        );
    }


    public function getPeerNamesIndexedByTable(RouteServerData $server)
    {
        $results = $this->getPeers($server);

        $peers = [];

        /** @var PeerData $peer */
        foreach ($results['data'] as $peer) {
            $peers[$peer->getTable()] = $peer->getName();
        }

        return $peers;
    }


    public function getPeerByTable(RouteServerData $server, $table)
    {
        $results = $this->getPeersIndexedByTable($server);

        if (isset($results['data'][$table])) {
            return $results['data'][$table];
        }

        $peer = new PeerData();
        $peer->setTable($table);

        return $peer;
    }


    private function createPeers(RouteServerData $server)
    {
        $results = $this->bgpProtocolService->getBgpProtocols($server);

        $peers = [];

        $blackHoles = $this->parameters->get('bird.black_holes');

        foreach ($blackHoles as $ip => $blackHole) {
            $peer = new PeerData();
            $peer->setName($blackHole);
            $peer->setIp($ip);
            $peers[] = $peer;
        }

        /** @var BgpProtocolData $protocol */
        foreach ($results['data'] as $protocol) {

            $peer = new PeerData();
            $peer->setName($protocol->getPeerName());
            $peer->setTable($protocol->getTable());
            $peer->setAsn($protocol->getAsn());
            $peer->setIp($protocol->getNeighborAddress());
            $peer->setDescription($protocol->getFormattedDescription());

            $peers[] = $peer;

        }

        return [
            'data'      => $peers,
            'timestamp' => $results['timestamp'],
        ];
    }


    public function savePeers(RouteServerData $server)
    {
        $peers = $this->createPeers($server);

        $item = $this->appCachePeers->getItem(
            sprintf(self::KEY_PEERS, $server->getId())
        );
        $item->set($peers);
        $this->appCachePeers->save($item);
    }

}
