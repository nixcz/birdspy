<?php

namespace App\Controller;

use App\Bird\CommandParameters;
use App\Data\BgpCommunityData;
use App\Data\BgpCommunityDataInterface;
use App\Data\BgpExtendedCommunityData;
use App\Data\BgpLargeCommunityData;
use App\Service\RouteServerService;
use App\Service\RouteTableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


class TableRouteController extends AbstractController
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
     * TableRouteController constructor.
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
     * @Route("/{server}/table/{table}/routes",
     *     name="table_routes",
     *     defaults={"_locale"="en"},
     *     options={"expose"=true})
     *
     * @param string              $server
     * @param string              $table
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function tableRoutes($server, $table, TranslatorInterface $translator)
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
                'title'        => $translator->trans('title.table_routes'),
                'subtitle'     => $serverData->getName(),
                'heading_text' => $translator->trans(
                    'subtitle.table_routes',
                    [
                        '%server%' => $serverData->getName(),
                        '%table%'  => $tableData->getId(),
                        '%peer%'   => $tableData->getPeer()->getName(),
                    ]
                ),
                'data_url'     => $this->generateUrl(
                    'table_routes_api',
                    [
                        'server' => $serverData->getId(),
                        'table'  => $tableData->getId(),
                    ]
                ),
            ]
        );
    }


    /**
     * @Route("/api/{server}/table/{table}/routes",
     *     name="table_routes_api",
     *     methods={"GET"})
     *
     * @param string $server
     * @param string $table
     *
     * @return JsonResponse
     */
    public function tableRoutesApi($server, $table)
    {
        $serverData = $this->routeServerService->getServerById($server);
        $tableData  = $this->routeServerService->getTableById($serverData, $table);

        $parameters = new CommandParameters();
        $parameters->setTable($tableData->getId());

        return $this->json(
            $this->routeTableService->getRoutesJson(
                $this->routeTableService->getTableRoutes($serverData, $parameters)
            )
        );
    }


    /**
     * @Route("/{server}/table/{table}/prefix-routes",
     *     name="prefix_table_routes")
     *
     * @param string              $server
     * @param string              $table
     * @param Request             $request
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function prefixTableRoutes($server, $table, Request $request, TranslatorInterface $translator)
    {
        try {
            $serverData = $this->routeServerService->getServerById($server);
            $tableData  = $this->routeServerService->getTableById($serverData, $table);

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
                'title'        => $translator->trans('title.prefix_table_routes'),
                'subtitle'     => $serverData->getName(),
                'heading_text' => $translator->trans(
                    'subtitle.prefix_table_routes',
                    [
                        '%server%' => $serverData->getName(),
                        '%table%'  => $tableData->getId(),
                        '%peer%'   => $tableData->getPeer()->getName(),
                        '%prefix%' => $_prefix,
                    ]
                ),
                'data_url'     => $this->generateUrl(
                    'prefix_table_routes_api',
                    [
                        'server' => $serverData->getId(),
                        'table'  => $tableData->getId(),
                        'prefix' => $_prefix,
                    ]
                ),
            ]
        );
    }


    /**
     * @Route("/api/{server}/table/{table}/prefix-routes",
     *     name="prefix_table_routes_api",
     *     methods={"GET"})
     *
     * @param string  $server
     * @param string  $table
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function prefixTableRoutesApi($server, $table, Request $request)
    {
        $serverData = $this->routeServerService->getServerById($server);
        $tableData  = $this->routeServerService->getTableById($serverData, $table);

        $parameters = new CommandParameters();
        $parameters->setTable($tableData->getId());
        $parameters->setPrefix($request->get('prefix'));

        return $this->json(
            $this->routeTableService->getRoutesJson(
                $this->routeTableService->getTableRoutesForPrefix($serverData, $parameters)
            )
        );
    }


    /**
     * @Route("/{server}/table/{table}/community-routes",
     *     name="community_table_routes")
     *
     * @param string              $server
     * @param string              $table
     * @param Request             $request
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function communityTableRoutes($server, $table, Request $request, TranslatorInterface $translator)
    {
        try {
            $serverData = $this->routeServerService->getServerById($server);
            $tableData  = $this->routeServerService->getTableById($serverData, $table);

        } catch (NotFoundHttpException $e) {
            $this->addFlash('warning', $e->getMessage());

            return $this->redirectToRoute('homepage');
        }

        try {
            $communities = $this->parseCommunities($request);

        } catch (NotFoundHttpException $e) {
            $this->addFlash('warning', $e->getMessage());

            return $this->redirectToRoute(
                'community_lookup',
                [
                    'server' => $serverData->getId(),
                ]
            );
        }

        $values = [];

        /** @var BgpCommunityDataInterface $community */
        foreach ($communities as $community) {
            $values[] = $community->getRawValue();
        }

        return $this->render(
            'server/server_routes.html.twig',
            [
                'title'        => $translator->trans('title.community_table_routes'),
                'subtitle'     => $serverData->getName(),
                'heading_text' => $translator->trans(
                    'subtitle.community_table_routes',
                    [
                        '%server%'    => $serverData->getName(),
                        '%table%'     => $tableData->getId(),
                        '%peer%'      => $tableData->getPeer()->getName(),
                        '%community%' => implode(', ', $values),
                    ]
                ),
                'data_url'     => $this->generateUrl(
                    'community_table_routes_api',
                    [
                        'server' => $serverData->getId(),
                        'table'  => $tableData->getId(),
                        'c'      => $request->get('c'),
                        'lgc'    => $request->get('lgc'),
                        'ec'     => $request->get('ec'),
                    ]
                ),
            ]
        );
    }


    /**
     * @Route("/api/{server}/table/{table}/community-routes",
     *     name="community_table_routes_api",
     *     methods={"GET"})
     *
     * @param string  $server
     * @param string  $table
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function communityTableRoutesApi($server, $table, Request $request)
    {
        $serverData  = $this->routeServerService->getServerById($server);
        $tableData   = $this->routeServerService->getTableById($serverData, $table);
        $communities = $this->parseCommunities($request);

        $parameters = new CommandParameters();
        $parameters->setTable($tableData->getId());
        $parameters->setBgpCommunitiesCondition(CommandParameters::CONDITION_AND);

        foreach ($communities as $community) {
            $parameters->addBgpCommunity($community);
        }

        return $this->json(
            $this->routeTableService->getRoutesJson(
                $this->routeTableService->getTableRoutesFilteredByCommunity($serverData, $parameters)
            )
        );
    }


    private function parseCommunities(Request $request)
    {
        $communities = [];

        if ($request->get('c')) {
            $keys = explode(',', $request->get('c'));
            foreach ($keys as $key) {
                $communities[] = new BgpCommunityData(explode(':', $key));
            }
        }

        if ($request->get('lgc')) {
            $keys = explode(',', $request->get('lgc'));
            foreach ($keys as $key) {
                $communities[] = new BgpLargeCommunityData(explode(':', $key));
            }
        }

        if ($request->get('ec')) {
            $keys = explode(',', $request->get('ec'));
            foreach ($keys as $key) {
                $communities[] = new BgpExtendedCommunityData(explode(':', $key));
            }
        }

        if (empty($communities)) {
            throw new NotFoundHttpException(sprintf('BGP Communities not found!'));
        }

        return $communities;
    }

}
