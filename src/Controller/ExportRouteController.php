<?php

namespace App\Controller;

use App\Bird\CommandParameters;
use App\Service\RouteServerService;
use App\Service\RouteTableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


class ExportRouteController extends AbstractController
{

    /**
     * @var RouteServerService
     */
    private $routeServerService;


    /**
     * ExportRouteController constructor.
     *
     * @param RouteServerService $routeServerService
     */
    public function __construct(RouteServerService $routeServerService)
    {
        $this->routeServerService = $routeServerService;
    }


    /**
     * @Route("/{server}/export/{protocol}/routes",
     *     name="exported_routes",
     *     defaults={"_locale"="en"},
     *     options={"expose"=true}))
     *
     * @param string              $server
     * @param string              $protocol
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function exportedRoutes($server, $protocol, TranslatorInterface $translator)
    {
        try {
            $serverData   = $this->routeServerService->getServerById($server);
            $protocolData = $this->routeServerService->getProtocolById($serverData, $protocol);

        } catch (NotFoundHttpException $e) {
            $this->addFlash('warning', $e->getMessage());

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'server/server_routes.html.twig',
            [
                'title'        => $translator->trans('title.exported_routes'),
                'subtitle'     => $serverData->getName(),
                'heading_text' => $translator->trans(
                    'subtitle.exported_routes',
                    [
                        '%server%'   => $serverData->getName(),
                        '%protocol%' => $protocolData->getId(),
                        '%peer%'     => $protocolData->getPeer()->getName(),
                    ]
                ),
                'data_url'     => $this->generateUrl(
                    'exported_routes_api',
                    [
                        'server'   => $serverData->getId(),
                        'protocol' => $protocolData->getId(),
                    ]
                ),
            ]
        );
    }


    /**
     * @Route("/api/{server}/export/{protocol}/routes",
     *     name="exported_routes_api",
     *     methods={"GET"})
     *
     * @param string            $server
     * @param string            $protocol
     * @param RouteTableService $routeTableService
     *
     * @return JsonResponse
     */
    public function exportedRoutesApi($server, $protocol, RouteTableService $routeTableService)
    {
        $serverData   = $this->routeServerService->getServerById($server);
        $protocolData = $this->routeServerService->getProtocolById($serverData, $protocol);

        $parameters = new CommandParameters();
        $parameters->setExport($protocolData->getId());

        return $this->json(
            $routeTableService->getRoutesJson(
                $routeTableService->getExportedRoutes($serverData, $parameters)
            )
        );
    }


    /**
     * @Route("/{server}/export/{protocol}/prefix-routes",
     *     name="prefix_exported_routes")
     *
     * @param string              $server
     * @param string              $protocol
     * @param Request             $request
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function prefixExportedRoutes($server, $protocol, Request $request, TranslatorInterface $translator)
    {
        try {
            $serverData   = $this->routeServerService->getServerById($server);
            $protocolData = $this->routeServerService->getProtocolById($serverData, $protocol);

        } catch (NotFoundHttpException $e) {
            $this->addFlash('warning', $e->getMessage());

            return $this->redirectToRoute('homepage');
        }

        try {
            $_prefix = $this->routeServerService->getPrefixByCidr($request->get('prefix'));

        } catch (NotFoundHttpException $e) {
            $this->addFlash('warning', $e->getMessage());

            return $this->redirectToRoute(
                'network_lookup',
                [
                    'server' => $serverData->getId(),
                ]
            );
        }

        return $this->render(
            'server/server_routes.html.twig',
            [
                'title'        => $translator->trans('title.prefix_exported_routes'),
                'subtitle'     => $serverData->getName(),
                'heading_text' => $translator->trans(
                    'subtitle.prefix_exported_routes',
                    [
                        '%server%'   => $serverData->getName(),
                        '%protocol%' => $protocolData->getId(),
                        '%peer%'     => $protocolData->getPeer()->getName(),
                        '%prefix%'   => $_prefix,
                    ]
                ),
                'data_url'     => $this->generateUrl(
                    'prefix_exported_routes_api',
                    [
                        'server'   => $serverData->getId(),
                        'protocol' => $protocolData->getId(),
                        'prefix'   => $_prefix,
                    ]
                ),
            ]
        );
    }


    /**
     * @Route("/api/{server}/export/{protocol}/prefix-routes",
     *     name="prefix_exported_routes_api",
     *     methods={"GET"})
     *
     * @param string            $server
     * @param string            $protocol
     * @param Request           $request
     * @param RouteTableService $routeTableService
     *
     * @return JsonResponse
     */
    public function prefixExportedRoutesApi($server, $protocol, Request $request, RouteTableService $routeTableService)
    {
        $serverData   = $this->routeServerService->getServerById($server);
        $protocolData = $this->routeServerService->getProtocolById($serverData, $protocol);

        $parameters = new CommandParameters();
        $parameters->setExport($protocolData->getId());
        $parameters->setPrefix($request->get('prefix'));

        return $this->json(
            $routeTableService->getRoutesJson(
                $routeTableService->getExportedRoutesForPrefix($serverData, $parameters)
            )
        );
    }

}
