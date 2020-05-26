<?php

namespace App\Controller;

use App\Service\RouteServerService;
use App\Service\RouteTableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


class FilteredRouteController extends AbstractController
{

    /**
     * @var RouteServerService
     */
    private $routeServerService;

    /**
     * @var RouteTableService
     */
    private $routeTableService;


    /**
     * FilteredRouteController constructor.
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
    }


    /**
     * @Route("/{server}/filtered-routes",
     *     name="filtered_routes")
     *
     * @param string              $server
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function filteredRoutes($server, TranslatorInterface $translator)
    {
        try {
            $serverData = $this->routeServerService->getServerById($server);

        } catch (NotFoundHttpException $e) {
            $this->addFlash('warning', $e->getMessage());

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'server/filtered_routes.html.twig',
            [
                'title'        => $translator->trans('title.filtered_routes'),
                'subtitle'     => $serverData->getName(),
                'heading_text' => $translator->trans(
                    'subtitle.filtered_routes',
                    [
                        '%server%' => $serverData->getName(),
                    ]
                ),
                'data_url'     => $this->generateUrl(
                    'filtered_routes_api',
                    [
                        'server' => $serverData->getId(),
                    ]
                ),
            ]
        );
    }


    /**
     * @Route("/api/{server}/filtered-routes",
     *     name="filtered_routes_api",
     *     methods={"GET"})
     *
     * @param string $server
     *
     * @return JsonResponse
     */
    public function filteredRoutesApi($server)
    {
        $serverData = $this->routeServerService->getServerById($server);

        return $this->json(
            $this->routeTableService->getRoutesJson(
                $this->routeTableService->getFilteredRoutes($serverData)
            )
        );
    }


    /**
     * @Route("/{server}/table/{table}/filtered-routes",
     *     name="table_filtered_routes",
     *     defaults={"_locale"="en"},
     *     options={"expose"=true}))
     *
     * @param string              $server
     * @param string              $table
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function tableFilteredRoutes($server, $table, TranslatorInterface $translator)
    {
        try {
            $serverData = $this->routeServerService->getServerById($server);
            $tableData  = $this->routeServerService->getTableById($serverData, $table);

        } catch (NotFoundHttpException $e) {
            $this->addFlash('warning', $e->getMessage());

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'server/server_routes.html.twig',
            [
                'title'        => $translator->trans('title.table_filtered_routes'),
                'subtitle'     => $serverData->getName(),
                'heading_text' => $translator->trans(
                    'subtitle.table_filtered_routes',
                    [
                        '%server%' => $serverData->getName(),
                        '%table%'  => $tableData->getId(),
                        '%peer%'   => $tableData->getPeer()->getName(),
                    ]
                ),
                'data_url'     => $this->generateUrl(
                    'table_filtered_routes_api',
                    [
                        'server' => $serverData->getId(),
                        'table'  => $tableData->getId(),
                    ]
                ),
            ]
        );
    }


    /**
     * @Route("/api/{server}/table/{table}/filtered-routes",
     *     name="table_filtered_routes_api",
     *     methods={"GET"})
     *
     * @param string $server
     * @param string $table
     *
     * @return JsonResponse
     */
    public function tableFilteredRoutesApi($server, $table)
    {
        $serverData = $this->routeServerService->getServerById($server);
        $tableData  = $this->routeServerService->getTableById($serverData, $table);

        return $this->json(
            $this->routeTableService->getRoutesJson(
                $this->routeTableService->getTableFilteredRoutes($serverData, $tableData->getId())
            )
        );
    }

}
