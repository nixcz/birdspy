<?php

namespace App\Form;

use App\Data\RouteServerData;
use App\Form\Data\SourceSelect;
use App\Service\RouteServerService;
use App\Service\SymbolService;
use App\Validator\Constraints\Network;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Contracts\Translation\TranslatorInterface;


class NetworkLookupFormType extends AbstractType
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
     * @var TranslatorInterface
     */
    private $translator;


    /**
     * NetworkLookupFormType constructor.
     *
     * @param RouteServerService  $routeServerService
     * @param SymbolService       $symbolService
     * @param TranslatorInterface $translator
     */
    public function __construct(
        RouteServerService $routeServerService,
        SymbolService $symbolService,
        TranslatorInterface $translator
    ) {
        $this->routeServerService = $routeServerService;
        $this->symbolService      = $symbolService;
        $this->translator         = $translator;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'source',
                ChoiceType::class,
                [
                    'label'                     => 'label.source',
                    'choices'                   => $this->getSources($options['server']),
                    'choice_label'              => function (SourceSelect $sourceSelect, $key, $value) {
                        return $sourceSelect->getSymbol()->getId();
                    },
                    'choice_attr'               => function (SourceSelect $sourceSelect, $key, $value) {
                        return [
                            'data-subtext' => $sourceSelect->getSymbol()->getPeer()->getName(),
                        ];
                    },
                    'group_by'                  => function (SourceSelect $sourceSelect, $key, $value) {
                        if ($sourceSelect->getType() === SourceSelect::TYPE_IMPORTED_PROTOCOL) {
                            return $this->translator->trans('label.imported_protocol');
                        }

                        if ($sourceSelect->getType() === SourceSelect::TYPE_EXPORTED_PROTOCOL) {
                            return $this->translator->trans('label.exported_protocol');
                        }

                        return $this->translator->trans('label.table');
                    },
                    'choice_translation_domain' => false,
                    'constraints'               => [
                        new NotBlank(),
                        new NotNull(),
                    ],
                ]
            )
            ->add(
                'network',
                TextType::class,
                [
                    'label'       => 'label.network_prefix',
                    'help'        => 'placeholder.network_prefix',
                    'constraints' => [
                        new NotBlank(),
                        new NotNull(),
                        new Network(),
                    ],
                ]
            );
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'server'             => null,
                'translation_domain' => 'messages',
            ]
        );

        $resolver->setAllowedTypes('server', 'App\Data\RouteServerData');
    }


    private function getSources(RouteServerData $server)
    {
        $protocols = $this->symbolService->getProtocols($server);
        $tables    = $this->symbolService->getTables($server);

        $sources = [];
        foreach ($tables as $table) {
            $sources[] = new SourceSelect($table, SourceSelect::TYPE_TABLE);
        }

        foreach ($protocols as $protocol) {
            $sources[] = new SourceSelect($protocol, SourceSelect::TYPE_IMPORTED_PROTOCOL);
        }

        foreach ($protocols as $protocol) {
            $sources[] = new SourceSelect($protocol, SourceSelect::TYPE_EXPORTED_PROTOCOL);
        }

        return $sources;
    }

}
