<?php

namespace App\Service;

use App\Data\BfdSessionData;
use App\Data\RouteServerData;
use DateTime;
use Psr\Cache\CacheItemPoolInterface;


class BfdSessionService
{

    const KEY_BFD_SESSIONS = '%s-bfd-sessions';

    /**
     * @var BirdReaderInterface
     */
    private $birdReader;

    /**
     * @var BfdSessionParser
     */
    private $bfdSessionParser;

    /**
     * @var PeerService
     */
    private $peerService;

    /**
     * @var CacheItemPoolInterface
     */
    private $appCacheBfdSessions;


    /**
     * BfdSessionService constructor.
     *
     * @param BirdReaderInterface    $birdReader
     * @param BfdSessionParser       $bfdSessionParser
     * @param PeerService            $peerService
     * @param CacheItemPoolInterface $appCacheBfdSessions
     */
    public function __construct(
        BirdReaderInterface $birdReader,
        BfdSessionParser $bfdSessionParser,
        PeerService $peerService,
        CacheItemPoolInterface $appCacheBfdSessions
    ) {
        $this->birdReader          = $birdReader;
        $this->bfdSessionParser    = $bfdSessionParser;
        $this->peerService         = $peerService;
        $this->appCacheBfdSessions = $appCacheBfdSessions;
    }


    public function getBfdSessions(RouteServerData $server)
    {
        return $this->appCacheBfdSessions->get(
            sprintf(self::KEY_BFD_SESSIONS, $server->getId()),
            function () use ($server) {
                return $this->createBfdSessions($server);
            }
        );
    }


    public function getBfdSessionsJson(RouteServerData $server)
    {
        $data = [];

        $results = $this->getBfdSessions($server);

        /** @var BfdSessionData $session */
        foreach ($results['data'] as $session) {
            $data[] = $session->toFormattedArray();
        }

        return [
            'data'      => $data,
            'timestamp' => $results['timestamp'],
        ];
    }


    private function createBfdSessions(RouteServerData $server)
    {
        $createdAt = new DateTime();

        $data     = $this->birdReader->getBfdSessions($server);
        $sessions = $this->bfdSessionParser->getBfdSessions($data);

        /** @var BfdSessionData $session */
        foreach ($sessions as $session) {

            $peer = $this->peerService->getPeerByIp($server, $session->getIpAddress());

            $session->setPeer($peer);

        }

        return [
            'data'      => $sessions,
            'timestamp' => (int) $createdAt->format('U'),
        ];
    }


    public function saveBfdSessions(RouteServerData $server)
    {
        $key     = sprintf(self::KEY_BFD_SESSIONS, $server->getId());
        $results = $this->createBfdSessions($server);

        $item = $this->appCacheBfdSessions->getItem($key);
        $item->set($results);

        $this->appCacheBfdSessions->save($item);
    }

}
