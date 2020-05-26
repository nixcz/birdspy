<?php

namespace App\Service;

use App\Bird\CommandParameters;
use App\Bird\ShowCommand;
use App\Data\BgpCommunityData;
use App\Data\BgpExtendedCommunityData;
use App\Data\BgpLargeCommunityData;
use App\Data\RouteServerData;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;


class BirdReader implements BirdReaderInterface
{

    /**
     * @var ParameterBagInterface
     */
    private $parameters;


    /**
     * @var array
     */
    private $birdCommand = [];


    /**
     * BirdReader constructor.
     *
     * @param ParameterBagInterface $parameters
     */
    public function __construct(ParameterBagInterface $parameters)
    {
        $this->parameters  = $parameters;
        $this->birdCommand = $parameters->get('bird.cmd');
    }


    public function getCommandOutput(RouteServerData $server, string $cmd)
    {
        $process = new Process(array_merge($this->birdCommand, ['-s', $server->getSocket(), $cmd]));
        $process->setTimeout(180);
        $process->run();

        if (! $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $output = $process->getOutput();

        if ($output === null) {
            throw new LogicException(sprintf('Error querying BIRD on server %s.', $server->getName()));
        }

        /*if (! preg_match("/^BIRD\s+[0-9.]+\s+ready/", $output)) {
            throw new LogicException(sprintf('Server %s is not ready.', $server->getName()));
        }*/

        return $output;
    }


    public function getStatus(RouteServerData $server)
    {
        return $this->getCommandOutput($server, ShowCommand::showStatus());
    }


    public function getSymbols(RouteServerData $server)
    {
        return $this->getCommandOutput($server, ShowCommand::showSymbols());
    }


    public function getBfdSessions(RouteServerData $server)
    {
        return $this->getCommandOutput($server, ShowCommand::showBfdSessions());
    }


    public function getBgpProtocols(RouteServerData $server, bool $count = false)
    {
        return $this->getCommandOutput($server, ShowCommand::showProtocols($count));
    }


    public function getRoutes(RouteServerData $server, bool $count = false)
    {
        return $this->getCommandOutput($server, ShowCommand::showRoute($count));
    }


    public function getInvalidRoutes(RouteServerData $server, bool $count = false)
    {
        $invalid = $this->parameters->get('bird.invalid');

        $parameters = $this->getFilteredParameters($invalid, $count);

        return $this->getCommandOutput($server, ShowCommand::showRouteTableFilteredByCommunity($parameters));
    }


    public function getFilteredRoutes(RouteServerData $server, bool $count = false)
    {
        $selected = $this->parameters->get('bird.filtered');

        $parameters = $this->getFilteredParameters($selected, $count);

        return $this->getCommandOutput($server, ShowCommand::showRouteTableFilteredByCommunity($parameters));
    }


    public function getTableRoutes(RouteServerData $server, CommandParameters $parameters)
    {
        return $this->getCommandOutput($server, ShowCommand::showRouteTable($parameters));
    }


    public function getImportedRoutes(RouteServerData $server, CommandParameters $parameters)
    {
        return $this->getCommandOutput($server, ShowCommand::showRouteProtocol($parameters));
    }


    public function getExportedRoutes(RouteServerData $server, CommandParameters $parameters)
    {
        return $this->getCommandOutput($server, ShowCommand::showRouteExport($parameters));
    }


    public function getTableRoutesForPrefix(RouteServerData $server, CommandParameters $parameters)
    {
        return $this->getCommandOutput($server, ShowCommand::showRouteForPrefixAndTable($parameters));
    }


    public function getImportedRoutesForPrefix(RouteServerData $server, CommandParameters $parameters)
    {
        return $this->getCommandOutput($server, ShowCommand::showRouteForPrefixAndProtocol($parameters));
    }


    public function getExportedRoutesForPrefix(RouteServerData $server, CommandParameters $parameters)
    {
        return $this->getCommandOutput($server, ShowCommand::showRouteForPrefixAndExport($parameters));
    }


    public function getTableRoutesFilteredByCommunity(RouteServerData $server, CommandParameters $parameters)
    {
        return $this->getCommandOutput($server, ShowCommand::showRouteTableFilteredByCommunity($parameters));
    }


    private function getFilteredParameters(array $values, bool $count = false)
    {
        $parameters = new CommandParameters();
        $parameters->setTable(CommandParameters::TABLE_ALL);
        $parameters->setCount($count);

        if (isset($values['communities']) && is_array($values['communities'])) {
            foreach ($values['communities'] as $community) {
                $dto = new BgpCommunityData($community);
                $parameters->addBgpCommunity($dto);
            }
        }

        if (isset($values['large_communities']) && is_array($values['large_communities'])) {
            foreach ($values['large_communities'] as $community) {
                $dto = new BgpLargeCommunityData($community);
                $parameters->addBgpCommunity($dto);
            }
        }

        if (isset($values['extended_communities']) && is_array($values['extended_communities'])) {
            foreach ($values['extended_communities'] as $community) {
                $dto = new BgpExtendedCommunityData($community);
                $parameters->addBgpCommunity($dto);
            }
        }

        return $parameters;
    }

}
