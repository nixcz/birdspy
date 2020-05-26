<?php

namespace App\Command;

use App\Service\RouteServerService;
use App\Service\RouteTableService;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class ImportServerInvalidRoutesCommand extends Command
{

    protected static $defaultName = 'app:import-server-invalid-routes';

    /**
     * @var RouteServerService
     */
    private $routeServerService;

    /**
     * @var RouteTableService
     */
    private $routeTableService;


    /**
     * ImportServerInvalidRoutesCommand constructor.
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
            ->setDescription('Import invalid routes from server.')
            ->setHelp('This command allows you to import invalid routes from Route Server.')
            ->addArgument('server_id', InputArgument::REQUIRED, 'Server ID (from yaml config)?');
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

            if ($helper->ask($input, $output, $question)) {
                $id = $input->getArgument('server_id');

            } else {
                return 1;
            }

            $server = $this->routeServerService->getServerById($id);

            $this->routeTableService->saveInvalidRoutes($server);

            $output->writeln(
                sprintf(
                    "<info>End of importing invalid routes from server %s, script was running %.2f s.\nUsed maximum memory %.2f MiB.\n</info>",
                    $server->getName(),
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
