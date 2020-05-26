<?php

namespace App\Controller;

use App\Data\BgpCommunityData;
use App\Data\BgpExtendedCommunityData;
use App\Data\BgpLargeCommunityData;
use App\Data\SymbolData;
use App\Form\CommunityLookupFormType;
use App\Form\Data\SourceSelect;
use App\Form\NetworkLookupFormType;
use App\Service\BfdSessionService;
use App\Service\BgpProtocolService;
use App\Service\RouteServerService;
use IPTools;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


class RouteServerController extends AbstractController
{

    /**
     * @var RouteServerService
     */
    private $routeServerService;


    /**
     * RouteServerController constructor.
     *
     * @param RouteServerService $routeServerService
     */
    public function __construct(RouteServerService $routeServerService)
    {
        $this->routeServerService = $routeServerService;
    }


    /**
     * @Route("/{server}/bfd/sessions",
     *     name="bfd_sessions")
     *
     * @param string              $server
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function bfdSessions($server, TranslatorInterface $translator)
    {
        try {
            $serverData = $this->routeServerService->getServerById($server);

        } catch (NotFoundHttpException $e) {
            $this->addFlash('warning', $e->getMessage());

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'server/bfd_sessions.html.twig',
            [
                'title'    => $translator->trans('title.bfd_sessions'),
                'subtitle' => $serverData->getName(),
            ]
        );
    }


    /**
     * @Route("/api/{server}/bfd/sessions",
     *     name="bfd_sessions_api",
     *     methods={"GET"})
     *
     * @param string            $server
     * @param BfdSessionService $bfdSessionsService
     *
     * @return JsonResponse
     */
    public function bfdSessionsApi($server, BfdSessionService $bfdSessionsService)
    {
        $serverData = $this->routeServerService->getServerById($server);

        return $this->json(
            $bfdSessionsService->getBfdSessionsJson($serverData)
        );
    }


    /**
     * @Route("/{server}/bgp/protocols",
     *     name="bgp_protocols")
     *
     * @param string              $server
     * @param TranslatorInterface $translator
     *
     * @return Response
     */
    public function bgpProtocols($server, TranslatorInterface $translator)
    {
        try {
            $serverData = $this->routeServerService->getServerById($server);

        } catch (NotFoundHttpException $e) {
            $this->addFlash('warning', $e->getMessage());

            return $this->redirectToRoute('homepage');
        }

        return $this->render(
            'server/bgp_protocols.html.twig',
            [
                'title'    => $translator->trans('title.bgp_protocols'),
                'subtitle' => $serverData->getName(),
            ]
        );
    }


    /**
     * @Route("/api/{server}/bgp/protocols",
     *     name="bgp_protocols_api",
     *     methods={"GET"})
     *
     * @param string             $server
     * @param BgpProtocolService $bgpProtocolsService
     *
     * @return JsonResponse
     */
    public function bgpProtocolsApi($server, BgpProtocolService $bgpProtocolsService)
    {
        $serverData = $this->routeServerService->getServerById($server);

        return $this->json(
            $bgpProtocolsService->getBgpProtocolsJson($serverData)
        );
    }


    /**
     * @Route("/{server}/community/lookup",
     *     name="community_lookup",
     *     defaults={"_locale"="en"},
     *     options={"expose"=true})
     *
     * @param string              $server
     * @param TranslatorInterface $translator
     * @param Request             $request
     *
     * @return Response
     */
    public function communityLookup($server, TranslatorInterface $translator, Request $request)
    {
        try {
            $serverData = $this->routeServerService->getServerById($server);

        } catch (NotFoundHttpException $e) {
            $this->addFlash('warning', $e->getMessage());

            return $this->redirectToRoute('homepage');
        }

        $tableData = null;
        if ($request->get('table')) {
            try {
                $tableData = $this->routeServerService->getTableById($serverData, $request->get('table'));

            } catch (NotFoundHttpException $e) {
                $this->addFlash('warning', $e->getMessage());

                return $this->redirectToRoute(
                    'community_lookup',
                    [
                        'server' => $serverData->getId(),
                        'table'  => null,
                    ]
                );
            }
        }

        $form = $this->createForm(
            CommunityLookupFormType::class,
            null,
            [
                'server' => $serverData,
                'table'  => $tableData,
            ]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SymbolData $symbolData */
            $symbolData = $form->get('table')->getData();

            $c   = [];
            $lgc = [];
            $ec  = [];

            foreach ($form->get('community')->getData() as $community) {
                if ($community instanceof BgpCommunityData) {
                    $c[] = $community->getFilterValue();
                }

                if ($community instanceof BgpLargeCommunityData) {
                    $lgc[] = $community->getFilterValue();
                }

                if ($community instanceof BgpExtendedCommunityData) {
                    $ec[] = $community->getFilterValue();
                }
            }

            return $this->redirectToRoute(
                'community_table_routes',
                [
                    'server' => $serverData->getId(),
                    'table'  => $symbolData->getId(),
                    'c'      => implode(',', $c),
                    'lgc'    => implode(',', $lgc),
                    'ec'     => implode(',', $ec),
                ]
            );
        }

        return $this->render(
            'server/community_lookup.html.twig',
            [
                'title'    => $translator->trans('title.community_lookup'),
                'subtitle' => $serverData->getName(),
                'form'     => $form->createView(),
            ]
        );
    }


    /**
     * @Route("/{server}/network/lookup",
     *     name="network_lookup")
     *
     * @param string              $server
     * @param TranslatorInterface $translator
     * @param Request             $request
     *
     * @return Response
     */
    public function networkLookup($server, TranslatorInterface $translator, Request $request)
    {
        try {
            $serverData = $this->routeServerService->getServerById($server);

        } catch (NotFoundHttpException $e) {
            $this->addFlash('warning', $e->getMessage());

            return $this->redirectToRoute('homepage');
        }

        $form = $this->createForm(
            NetworkLookupFormType::class,
            null,
            ['server' => $serverData]
        );

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            /** @var SourceSelect $sourceSelect */
            $sourceSelect = $form->get('source')->getData();

            $_prefix = (string) IPTools\Network::parse($form->get('network')->getData());

            if ($sourceSelect->getType() === SourceSelect::TYPE_IMPORTED_PROTOCOL) {
                return $this->redirectToRoute(
                    'prefix_imported_routes',
                    [
                        'server'   => $serverData->getId(),
                        'protocol' => $sourceSelect->getSymbol()->getId(),
                        'prefix'   => $_prefix,
                    ]
                );
            }

            if ($sourceSelect->getType() === SourceSelect::TYPE_EXPORTED_PROTOCOL) {
                return $this->redirectToRoute(
                    'prefix_exported_routes',
                    [
                        'server'   => $serverData->getId(),
                        'protocol' => $sourceSelect->getSymbol()->getId(),
                        'prefix'   => $_prefix,
                    ]
                );
            }

            return $this->redirectToRoute(
                'prefix_table_routes',
                [
                    'server' => $serverData->getId(),
                    'table'  => $sourceSelect->getSymbol()->getId(),
                    'prefix' => $_prefix,
                ]
            );
        }

        return $this->render(
            'server/network_lookup.html.twig',
            [
                'title'    => $translator->trans('title.network_lookup'),
                'subtitle' => $serverData->getName(),
                'form'     => $form->createView(),
            ]
        );
    }

}
