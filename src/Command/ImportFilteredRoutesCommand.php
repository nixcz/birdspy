<?php

namespace App\Command;

use App\Service\RouteServerService;
use App\Service\RouteTableService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class ImportFilteredRoutesCommand extends Command
{

    protected static $defaultName = 'app:import-filtered-routes';

    /**
     * @var RouteServerService
     */
    private $routeServerService;

    /**
     * @var RouteTableService
     */
    private $routeTableService;


    /**
     * ImportFilteredRoutesCommand constructor.
     *
     * @param RouteServerService $routeServerService
     * @param RouteTableService  $routeTableService
     */
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
            ->setDescription('Import filtered routes from all servers.')
            ->setHelp('This command allows you to import filtered routes from all Route Servers.');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $question = new ConfirmationQuestion(
            'Continue with importing routes? (y/n)',
            true,
            '/^(y|a)/i'
        );

        try {
            $start = microtime(true);

            if (! $helper->ask($input, $output, $question)) {
                return 1;
            }

            $servers = $this->routeServerService->getServers();

            $progressBar = new ProgressBar($output, count($servers));
            $progressBar->setFormat("%current%/%max% [%bar%] %percent:3s%%\n%message%");
            $progressBar->setMessage('Importing routes started');
            $progressBar->start();

            foreach ($servers as $server) {

                $progressBar->setMessage(
                    sprintf(
                        'Importing data from server: %s',
                        $server->getName()
                    )
                );

                $this->routeTableService->saveFilteredRoutes($server);

                $progressBar->advance();

            }

            $progressBar->setMessage('Importing routes finished');
            $progressBar->finish();

            $output->writeln(
                sprintf(
                    "\n<info>End of importing all filtered routes, script was running %.2f s.\nUsed maximum memory %.2f MiB.\n</info>",
                    microtime(true) - $start,
                    memory_get_peak_usage() / 1024 / 1024
                )
            );

            return 0;

        } catch (NotFoundHttpException $e) {

            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 1;

        } catch (Exception $e) {
            $output->writeln(sprintf('<error>%s</error>', $e->getMessage()));

            return 1;
        }
    }

}
