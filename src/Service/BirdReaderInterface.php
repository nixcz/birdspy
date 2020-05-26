<?php

namespace App\Service;

use App\Bird\CommandParameters;
use App\Data\RouteServerData;


interface BirdReaderInterface
{

    public function getStatus(RouteServerData $server);


    public function getSymbols(RouteServerData $server);


    public function getBfdSessions(RouteServerData $server);


    public function getBgpProtocols(RouteServerData $server, bool $count = false);


    public function getRoutes(RouteServerData $server, bool $count = false);


    public function getInvalidRoutes(RouteServerData $server, bool $count = false);


    public function getFilteredRoutes(RouteServerData $server, bool $count = false);


    public function getTableRoutes(RouteServerData $server, CommandParameters $parameters);


    public function getImportedRoutes(RouteServerData $server, CommandParameters $parameters);


    public function getExportedRoutes(RouteServerData $server, CommandParameters $parameters);


    public function getTableRoutesForPrefix(RouteServerData $server, CommandParameters $parameters);


    public function getImportedRoutesForPrefix(RouteServerData $server, CommandParameters $parameters);


    public function getExportedRoutesForPrefix(RouteServerData $server, CommandParameters $parameters);


    public function getTableRoutesFilteredByCommunity(RouteServerData $server, CommandParameters $parameters);

}
