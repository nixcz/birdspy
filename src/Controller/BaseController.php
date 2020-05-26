<?php

namespace App\Controller;

use App\Service\BgpProtocolService;
use App\Service\RouteTableService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


class BaseController extends AbstractController
{

    /**
     * @Route("/", name="homepage")
     *
     * @param array $site
     *
     * @return Response
     */
    public function index($site)
    {
        return $this->render(
            'index.html.twig',
            [
                'title' => $site['title'],
            ]
        );
    }


    /**
     * @Route("/api/protocol/detail",
     *     name="bgp_protocol_detail_api",
     *     defaults={"_locale"="en"},
     *     options={"expose"=true},
     *     methods={"GET"})
     *
     * @param Request            $request
     * @param BgpProtocolService $bgpProtocolsService
     *
     * @return JsonResponse
     */
    public function bgpProtocolDetailApi(Request $request, BgpProtocolService $bgpProtocolsService)
    {
        $protocolId = $request->get('protocol_id');

        return $this->json(
            $bgpProtocolsService->getBgpProtocolDetail($protocolId)
        );
    }


    /**
     * @Route("/api/route/detail",
     *     name="route_detail_api",
     *     defaults={"_locale"="en"},
     *     options={"expose"=true}),
     *     methods={"GET"})
     *
     * @param Request           $request
     * @param RouteTableService $routeTableService
     *
     * @return JsonResponse
     */
    public function routeDetailApi(Request $request, RouteTableService $routeTableService)
    {
        $tableId = $request->get('table_id');
        $routeId = $request->get('route_id');

        return $this->json(
            $routeTableService->getRouteDetail($tableId, $routeId)
        );
    }

}
