<?php

namespace App\Service;

use App\Data\RouteServerData;
use App\Data\SymbolData;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Contracts\Cache\ItemInterface;


class SymbolService
{

    const KEY_PROTOCOL  = '%s-protocol-%s';
    const KEY_PROTOCOLS = '%s-protocols';
    const KEY_SYMBOLS   = '%s-symbols';
    const KEY_TABLE     = '%s-table-%s';
    const KEY_TABLES    = '%s-tables';

    /**
     * @var BirdReaderInterface
     */
    private $birdReader;

    /**
     * @var SymbolParser
     */
    private $symbolParser;

    /**
     * @var PeerService
     */
    private $peerService;

    /**
     * @var CacheItemPoolInterface
     */
    private $appCacheSymbols;


    /**
     * SymbolService constructor.
     *
     * @param BirdReaderInterface    $birdReader
     * @param SymbolParser           $symbolParser
     * @param PeerService            $peerService
     * @param CacheItemPoolInterface $appCacheSymbols
     */
    public function __construct(
        BirdReaderInterface $birdReader,
        SymbolParser $symbolParser,
        PeerService $peerService,
        CacheItemPoolInterface $appCacheSymbols
    ) {
        $this->birdReader      = $birdReader;
        $this->symbolParser    = $symbolParser;
        $this->peerService     = $peerService;
        $this->appCacheSymbols = $appCacheSymbols;
    }


    public function getSymbols(RouteServerData $server)
    {
        return $this->appCacheSymbols->get(
            sprintf(self::KEY_SYMBOLS, $server->getId()),
            function (ItemInterface $item) use ($server) {
                return $this->createSymbols($server);
            }
        );
    }


    public function getProtocols(RouteServerData $server)
    {
        return $this->appCacheSymbols->get(
            sprintf(self::KEY_PROTOCOLS, $server->getId()),
            function (ItemInterface $item) use ($server) {
                return $this->getSymbols($server)[SymbolData::TYPE_PROTOCOL];
            }
        );
    }


    /**
     * @param RouteServerData $server
     * @param string          $id
     *
     * @return SymbolData
     */
    public function getProtocolById(RouteServerData $server, string $id)
    {
        $protocols = $this->getProtocols($server);

        if (! isset($protocols[$id])) {
            throw new NotFoundHttpException(sprintf('The route protocol %s does not exist!', $id));
        }

        return $protocols[$id];
    }


    public function getTables(RouteServerData $server)
    {
        return $this->appCacheSymbols->get(
            sprintf(self::KEY_TABLES, $server->getId()),
            function (ItemInterface $item) use ($server) {
                return $this->getSymbols($server)[SymbolData::TYPE_TABLE];
            }
        );
    }


    /**
     * @param RouteServerData $server
     * @param string          $id
     *
     * @return SymbolData
     */
    public function getTableById(RouteServerData $server, string $id)
    {
        $tables = $this->getTables($server);

        if (! isset($tables[$id])) {
            throw new NotFoundHttpException(sprintf('The route table %s does not exist!', $id));
        }

        return $tables[$id];
    }


    public function createSymbols(RouteServerData $server)
    {
        $data    = $this->birdReader->getSymbols($server);
        $symbols = $this->symbolParser->getSymbols($data);

        $protocols = [];
        $tables    = [];

        /** @var SymbolData $protocol */
        foreach ($symbols[SymbolData::TYPE_PROTOCOL] as $protocol) {

            if (preg_match("/(R4_|R6_)/", $protocol->getId())) {
                $peer = $this->peerService->getPeerByProtocol($server, $protocol->getId());
                $protocol->setPeer($peer);

                $this->cacheProtocol($server, $protocol);

                $protocols[$protocol->getId()] = $protocol;
            }

        }

        /** @var SymbolData $table */
        foreach ($symbols[SymbolData::TYPE_TABLE] as $table) {

            if (preg_match("/(T4_|T6_|master4|master6)/", $table->getId())) {
                $peer = $this->peerService->getPeerByTable($server, $table->getId());
                $table->setPeer($peer);

                $this->cacheTable($server, $table);

                $tables[$table->getId()] = $table;
            }
        }

        ksort($protocols, SORT_NATURAL);
        ksort($tables, SORT_NATURAL | SORT_FLAG_CASE);

        return [
            SymbolData::TYPE_PROTOCOL => $protocols,
            SymbolData::TYPE_TABLE    => $tables,
        ];
    }


    private function cacheProtocol(RouteServerData $server, SymbolData $symbol)
    {
        $item = $this->appCacheSymbols->getItem(
            sprintf(self::KEY_PROTOCOL, $server->getId(), $symbol->getId())
        );
        $item->set($server);
        $this->appCacheSymbols->save($item);

    }


    private function cacheTable(RouteServerData $server, SymbolData $symbol)
    {
        $item = $this->appCacheSymbols->getItem(
            sprintf(self::KEY_TABLE, $server->getId(), $symbol->getId())
        );
        $item->set($server);
        $this->appCacheSymbols->save($item);
    }


    public function saveSymbols(RouteServerData $server)
    {
        $symbols = $this->createSymbols($server);

        $item = $this->appCacheSymbols->getItem(
            sprintf(self::KEY_SYMBOLS, $server->getId())
        );
        $item->set($symbols);
        $this->appCacheSymbols->save($item);
    }


    private function checkSymbolsExistence(RouteServerData $server)
    {
        $item = $this->appCacheSymbols->getItem(
            sprintf(self::KEY_SYMBOLS, $server->getId())
        );

        if (! $item->isHit()) {
            $this->saveSymbols($server);
        }
    }

}
