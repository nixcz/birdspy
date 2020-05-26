<?php

namespace App\Service;

use App\Bird\CommandParameters;
use App\Data\RouteServerData;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


class BirdSampleReader implements BirdReaderInterface
{

    /**
     * @var ParameterBagInterface
     */
    private $parameters;

    /**
     * @var bool
     */
    private $isTiny;


    /**
     * BirdSampleReader constructor.
     *
     * @param ParameterBagInterface $parameters
     */
    public function __construct(ParameterBagInterface $parameters)
    {
        $this->parameters = $parameters;
        $this->isTiny     = $parameters->get('app.tiny_samples');
    }


    /**
     * @param RouteServerData $server
     * @param string          $filename
     *
     * @return false|string
     */
    private function getFileContent(RouteServerData $server, string $filename)
    {
        return file_get_contents(
            sprintf(
                '%s/bird-samples/%s/%s',
                $this->parameters->get('app.files_dir'),
                $server->getVersionMask(),
                $filename
            )
        );
    }


    public function getStatus(RouteServerData $server)
    {
        return $this->getFileContent($server, 'show_status.txt');
    }


    public function getSymbols(RouteServerData $server)
    {
        return $this->getFileContent($server, 'show_symbols.txt');
    }


    public function getBfdSessions(RouteServerData $server)
    {
        return $this->getFileContent($server, 'show_bfd_sessions.txt');
    }


    public function getBgpProtocols(RouteServerData $server, bool $count = false)
    {
        if ($count) {
            return $this->getFileContent($server, 'show_protocols_count.txt');
        }

        return $this->getFileContent($server, 'show_protocols_all.txt');
    }


    public function getRoutes(RouteServerData $server, bool $count = false)
    {
        if ($count) {
            return $this->getFileContent($server, 'show_route_table_all_count.txt');
        }

        return $this->getFileContent($server, 'show_route_table_all.txt');
    }


    public function getInvalidRoutes(RouteServerData $server, bool $count = false)
    {
        if ($count) {
            return $this->getFileContent($server, 'show_route_table_all_invalid_count.txt');
        }

        if ($this->isTiny) {
            return $this->getFileContent($server, 'show_route_table_all-tiny.txt');
        }

        return $this->getFileContent($server, 'show_route_table_all_invalid_all.txt');
    }


    public function getFilteredRoutes(RouteServerData $server, bool $count = false)
    {
        if ($count) {
            return $this->getFileContent($server, 'show_route_table_all_filtered_count.txt');
        }

        if ($this->isTiny) {
            return $this->getFileContent($server, 'show_route_table_all-tiny.txt');
        }

        return $this->getFileContent($server, 'show_route_table_all_filtered_all.txt');
    }


    public function getTableRoutes(RouteServerData $server, CommandParameters $parameters)
    {
        if ($parameters->isCount()) {
            return $this->getFileContent($server, 'show_route_table_count.txt');
        }

        if ($this->isTiny) {
            return $this->getFileContent($server, 'show_route_table_all-tiny.txt');
        }

        return $this->getFileContent($server, 'show_route_table_all.txt');
    }


    public function getImportedRoutes(RouteServerData $server, CommandParameters $parameters)
    {
        if ($parameters->isCount()) {
            return $this->getFileContent($server, 'show_route_protocol_count.txt');
        }

        if ($this->isTiny) {
            return $this->getFileContent($server, 'show_route_table_all-tiny.txt');
        }

        return $this->getFileContent($server, 'show_route_protocol_all.txt');
    }


    public function getExportedRoutes(RouteServerData $server, CommandParameters $parameters)
    {
        if ($parameters->isCount()) {
            return $this->getFileContent($server, 'show_route_export_count.txt');
        }

        if ($this->isTiny) {
            return $this->getFileContent($server, 'show_route_table_all-tiny.txt');
        }

        return $this->getFileContent($server, 'show_route_export_all.txt');
    }


    public function getTableRoutesForPrefix(RouteServerData $server, CommandParameters $parameters)
    {
        if ($parameters->isCount()) {
            return $this->getFileContent($server, 'show_route_for_net_table_count.txt');
        }

        if ($this->isTiny) {
            return $this->getFileContent($server, 'show_route_table_all-tiny.txt');
        }

        return $this->getFileContent($server, 'show_route_for_net_table_all.txt');
    }


    public function getImportedRoutesForPrefix(RouteServerData $server, CommandParameters $parameters)
    {
        if ($parameters->isCount()) {
            return $this->getFileContent($server, 'show_route_for_net_protocol_count.txt');
        }

        if ($this->isTiny) {
            return $this->getFileContent($server, 'show_route_table_all-tiny.txt');
        }

        return $this->getFileContent($server, 'show_route_for_net_protocol_all.txt');
    }


    public function getExportedRoutesForPrefix(RouteServerData $server, CommandParameters $parameters)
    {
        if ($parameters->isCount()) {
            return $this->getFileContent($server, 'show_route_for_net_export_count.txt');
        }

        if ($this->isTiny) {
            return $this->getFileContent($server, 'show_route_table_all-tiny.txt');
        }

        return $this->getFileContent($server, 'show_route_for_net_export_all.txt');
    }


    public function getTableRoutesFilteredByCommunity(RouteServerData $server, CommandParameters $parameters)
    {
        if ($parameters->isCount()) {
            return $this->getFileContent($server, 'show_route_table_table_filtered_count.txt');
        }

        return $this->getFileContent($server, 'show_route_table_table_filtered_all.txt');
    }

}
