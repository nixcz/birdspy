<?php

namespace App\Form;

use App\Data\BgpCommunityData;
use App\Data\BgpCommunityDataInterface;
use App\Data\BgpExtendedCommunityData;
use App\Data\BgpLargeCommunityData;
use App\Data\SymbolData;
use App\Service\BgpCommunityService;
use App\Service\RouteServerService;
use App\Service\SymbolService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\Translation\TranslatorInterface;


class CommunityLookupFormType extends AbstractType
{

    /**
     * @var RouteServerService
     */
    private $routeServerService;

    /**
     * @var SymbolService
     */
    private $symbolService;

    /**
     * @var BgpCommunityService
     */
    private $communityService;

    /**
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * CommunityLookupFormType constructor.
     *
     * @param RouteServerService  $routeServerService
     * @param SymbolService       $symbolService
     * @param BgpCommunityService $communityService
     * @param TranslatorInterface $translator
     */
    public function __construct(
        RouteServerService $routeServerService,
        SymbolService $symbolService,
        BgpCommunityService $communityService,
        TranslatorInterface $translator
    ) {
        $this->routeServerService = $routeServerService;
        $this->symbolService      = $symbolService;
        $this->communityService   = $communityService;
        $this->translator         = $translator;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'table',
                ChoiceType::class,
                [
                    'label'                     => 'label.table',
                    'choices'                   => $this->symbolService->getTables($options['server']),
                    'choice_value'              => function (SymbolData $symbol = null) {
                        return $symbol ? $symbol->getId() : '';
                    },
                    'choice_label'              => function (SymbolData $symbol, $key, $value) {
                        return $symbol->getId();
                    },
                    'choice_attr'               => function (SymbolData $symbol, $key, $value) {
                        return [
                            'data-subtext' => $symbol->getPeer()->getName(),
                        ];
                    },
                    'data'                      => $options['table'],
                    'choice_translation_domain' => false,
                    'constraints'               => [
                        new NotBlank(),
                        new NotNull(),
                    ],
                ]
            )
            ->add(
                'community',
                ChoiceType::class,
                [
                    'label'                     => 'label.community',
                    'choices'                   => $this->getCommunityChoices(),
                    'choice_label'              => function (BgpCommunityDataInterface $community, $key, $value) {
                        return $community->getRawValue();
                    },
                    'choice_attr'               => function (BgpCommunityDataInterface $community, $key, $value) {
                        return [
                            'data-content' => sprintf(
                                '%s <span class="badge badge-%s">%s</span>',
                                $community->getRawValue(),
                                $community->getLabel(),
                                $community->getName()
                            ),
                        ];
                    },
                    'group_by'                  => function (BgpCommunityDataInterface $community, $key, $value) {
                        if ($community instanceof BgpLargeCommunityData) {
                            return $this->translator->trans('label.large_community');
                        }

                        if ($community instanceof BgpExtendedCommunityData) {
                            return $this->translator->trans('label.extended_community');
                        }

                        return $this->translator->trans('label.community');
                    },
                    'choice_translation_domain' => false,
                    'multiple'                  => true,
                    'constraints'               => [
                        new NotBlank(),
                        new NotNull(),
                    ],
                ]
            );
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'server'             => null,
                'table'              => null,
                'translation_domain' => 'messages',
            ]
        );

        $resolver->setAllowedTypes('server', 'App\Data\RouteServerData');
        $resolver->setAllowedTypes('table', ['null', 'App\Data\SymbolData']);
    }


    private function getCommunityChoices()
    {
        $communities         = $this->communityService->getKnownCommunities();
        $largeCommunities    = $this->communityService->getKnownLargeCommunities();
        $extendedCommunities = $this->communityService->getKnownExtendedCommunities();

        $choices = [];

        /** @var BgpCommunityData $community */
        foreach ($communities as $community) {
            $choices[] = $community;
        }

        /** @var BgpLargeCommunityData $largeCommunity */
        foreach ($largeCommunities as $largeCommunity) {
            $choices[] = $largeCommunity;
        }

        /** @var BgpExtendedCommunityData $extendedCommunity */
        foreach ($extendedCommunities as $extendedCommunity) {
            $choices[] = $extendedCommunity;
        }

        return $choices;
    }

}
