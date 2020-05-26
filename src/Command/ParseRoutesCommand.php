<?php

namespace App\Command;

use App\Bird\CommandParameters;
use App\Service\RouteServerService;
use App\Service\RouteTableService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class ParseRoutesCommand extends Command
{

    protected static $defaultName = 'app:parse-routes';

    /**
     * @var RouteServerService
     */
    private $routeServerService;

    /**
     * @var RouteTableService
     */
    private $routeTableService;


    public function __construct(
        RouteServerService $routeServerService,
        RouteTableService $routeTableService
    ) {
        $this->routeServerService = $routeServerService;
        $this->routeTableService  = $routeTableService;

        parent::__construct();
    }


    protected function configure()
    {
        $this
            ->setDescription('Parse routes for testing.')
            ->setHelp('This command parse table routes for testing.');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $start = microtime(true);

            $server     = $this->routeServerService->getServerById('nix-rs-1');
            $parameters = new CommandParameters();

            $results = $this->routeTableService->getTableRoutes($server, $parameters);

            $output->writeln(
                sprintf(
                    "<info>Routes: %s</info>",
                    count($results['data'])
                )
            );

            /*$output->writeln(
                sprintf(
                    "<info>Cache key: %s</info>",
                    $routes['key']
                )
            );*/

            $output->writeln(
                sprintf(
                    "<info>Script was running %.2f s.\nUsed maximum memory %.2f MiB.\n</info>",
                    microtime(true) - $start,
                    memory_get_peak_usage() / 1024 / 1024
                )
            );

            return 0;

        } catch (Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 1;
        }
    }

}
