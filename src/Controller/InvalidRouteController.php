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


class InvalidRouteController extends AbstractController
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
     * InvalidRouteController constructor.
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
     * @Route("/{server}/invalid-routes",
     *     name="invalid_routes")
     *
     * @param string              $server
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function invalidRoutes($server, TranslatorInterface $translator)
    {
        try {
            $serverData = $this->routeServerService->getServerById($server);

        } catch (NotFoundHttpException $e) {
            $this->addFlash('warning', $e->getMessage());

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'server/invalid_routes.html.twig',
            [
                'title'        => $translator->trans('title.invalid_routes'),
                'subtitle'     => $serverData->getName(),
                'heading_text' => $translator->trans(
                    'subtitle.invalid_routes',
                    [
                        '%server%' => $serverData->getName(),
                    ]
                ),
                'data_url'     => $this->generateUrl(
                    'invalid_routes_api',
                    [
                        'server' => $serverData->getId(),
                    ]
                ),
            ]
        );
    }


    /**
     * @Route("/api/{server}/invalid-routes",
     *     name="invalid_routes_api",
     *     methods={"GET"})
     *
     * @param string $server
     *
     * @return JsonResponse
     */
    public function invalidRoutesApi($server)
    {
        $serverData = $this->routeServerService->getServerById($server);

        return $this->json(
            $this->routeTableService->getRoutesJson(
                $this->routeTableService->getInvalidRoutes($serverData)
            )
        );
    }


    /**
     * @Route("/{server}/table/{table}/invalid-routes",
     *     name="table_invalid_routes",
     *     defaults={"_locale"="en"},
     *     options={"expose"=true}))
     *
     * @param string              $server
     * @param string              $table
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function tableInvalidRoutes($server, $table, TranslatorInterface $translator)
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
                'title'        => $translator->trans('title.table_invalid_routes'),
                'subtitle'     => $serverData->getName(),
                'heading_text' => $translator->trans(
                    'subtitle.table_invalid_routes',
                    [
                        '%server%' => $serverData->getName(),
                        '%table%'  => $tableData->getId(),
                        '%peer%'   => $tableData->getPeer()->getName(),
                    ]
                ),
                'data_url'     => $this->generateUrl(
                    'table_invalid_routes_api',
                    [
                        'server' => $serverData->getId(),
                        'table'  => $tableData->getId(),
                    ]
                ),
            ]
        );
    }


    /**
     * @Route("/api/{server}/table/{table}/invalid-routes",
     *     name="table_invalid_routes_api",
     *     methods={"GET"})
     *
     * @param string $server
     * @param string $table
     *
     * @return JsonResponse
     */
    public function tableInvalidRoutesApi($server, $table)
    {
        $serverData = $this->routeServerService->getServerById($server);
        $tableData  = $this->routeServerService->getTableById($serverData, $table);

        return $this->json(
            $this->routeTableService->getRoutesJson(
                $this->routeTableService->getTableInvalidRoutes($serverData, $tableData->getId())
            )
        );
    }

}
