<?php

namespace App\Service;

use App\Data\BgpCommunityData;
use App\Data\BgpCommunityDataInterface;
use App\Data\BgpExtendedCommunityData;
use App\Data\BgpLargeCommunityData;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;


class BgpCommunityService
{

    const KEY_KNOWN_COMMUNITIES          = 'known-communities';
    const KEY_KNOWN_LARGE_COMMUNITIES    = 'known-large-communities';
    const KEY_KNOWN_EXTENDED_COMMUNITIES = 'known-extended-communities';

    const KEY_ARRAY_COMMUNITIES             = 'array-communities';
    const KEY_ARRAY_COMMUNITIES_X           = 'array-communities-x';
    const KEY_ARRAY_LARGE_COMMUNITIES       = 'array-large-communities';
    const KEY_ARRAY_LARGE_COMMUNITIES_X     = 'array-large-communities-x';
    const KEY_ARRAY_LARGE_COMMUNITIES_XX    = 'array-large-communities-xx';
    const KEY_ARRAY_EXTENDED_COMMUNITIES    = 'array-extended-communities';
    const KEY_ARRAY_EXTENDED_COMMUNITIES_X  = 'array-extended-communities-x';
    const KEY_ARRAY_EXTENDED_COMMUNITIES_XX = 'array-extended-communities-xx';

    /**
     * @var ParameterBagInterface
     */
    private $parameters;

    /**
     * @var CacheInterface
     */
    private $appCacheCommunities;


    /**
     * BgpCommunityService constructor.
     *
     * @param ParameterBagInterface $parameters
     * @param CacheInterface        $appCacheCommunities
     */
    public function __construct(ParameterBagInterface $parameters, CacheInterface $appCacheCommunities)
    {
        $this->parameters          = $parameters;
        $this->appCacheCommunities = $appCacheCommunities;
    }


    public function createKnownCommunities()
    {
        $known = $this->parameters->get('bird.known');

        $communities = [];

        if (isset($known['communities'])) {
            foreach ($known['communities'] as $key => $values) {

                $dto = new BgpCommunityData(explode(':', $key));
                $dto->setName($values['name']);
                $dto->setLabel($values['label']);

                $communities[] = $dto;

            }
        }

        return $communities;
    }


    public function getKnownCommunities()
    {
        return $this->appCacheCommunities->get(
            self::KEY_KNOWN_COMMUNITIES,
            function (ItemInterface $item) {
                return $this->createKnownCommunities();
            }
        );
    }


    public function getKnownCommunitiesArray()
    {
        return $this->appCacheCommunities->get(
            self::KEY_ARRAY_COMMUNITIES,
            function (ItemInterface $item) {
                $communities = $this->getKnownCommunities();

                $results = [];

                /** @var BgpCommunityData $community */
                foreach ($communities as $community) {

                    if (! $community->hasWildCardValue()) {
                        $results[$community->getFilterValue()] = self::simpleFormat($community);
                    }

                }

                return $results;
            }
        );
    }


    public function getKnownCommunitiesArrayX()
    {
        return $this->appCacheCommunities->get(
            self::KEY_ARRAY_COMMUNITIES_X,
            function (ItemInterface $item) {
                $communities = $this->getKnownCommunities();

                $results = [];

                /** @var BgpCommunityData $community */
                foreach ($communities as $community) {

                    if ($community->hasWildCardValue()) {
                        $results[$community->getFilterValue()] = self::simpleFormat($community);
                    }

                }

                return $results;
            }
        );
    }


    public function createKnownLargeCommunities()
    {
        $known = $this->parameters->get('bird.known');

        $communities = [];

        if (isset($known['large_communities'])) {
            foreach ($known['large_communities'] as $key => $values) {

                $dto = new BgpLargeCommunityData(explode(':', $key));
                $dto->setName($values['name']);
                $dto->setLabel($values['label']);

                $communities[] = $dto;

            }
        }

        return $communities;
    }


    public function getKnownLargeCommunities()
    {
        return $this->appCacheCommunities->get(
            self::KEY_KNOWN_LARGE_COMMUNITIES,
            function (ItemInterface $item) {
                return $this->createKnownLargeCommunities();
            }
        );
    }


    public function getKnownLargeCommunitiesArray()
    {
        return $this->appCacheCommunities->get(
            self::KEY_ARRAY_LARGE_COMMUNITIES,
            function (ItemInterface $item) {
                $communities = $this->getKnownLargeCommunities();

                $results = [];

                /** @var BgpLargeCommunityData $community */
                foreach ($communities as $community) {

                    if (! $community->hasWildCardValue1() && ! $community->hasWildCardValue2()) {
                        $results[$community->getFilterValue()] = self::simpleFormat($community);
                    }

                }

                return $results;
            }
        );
    }


    public function getKnownLargeCommunitiesArrayX()
    {
        return $this->appCacheCommunities->get(
            self::KEY_ARRAY_LARGE_COMMUNITIES_X,
            function (ItemInterface $item) {
                $communities = $this->getKnownLargeCommunities();

                $results = [];

                /** @var BgpLargeCommunityData $community */
                foreach ($communities as $community) {

                    if (! $community->hasWildCardValue1() && $community->hasWildCardValue2()) {
                        $results[$community->getFilterValue()] = self::simpleFormat($community);
                    }

                }

                return $results;
            }
        );
    }


    public function getKnownLargeCommunitiesArrayXX()
    {
        return $this->appCacheCommunities->get(
            self::KEY_ARRAY_LARGE_COMMUNITIES_XX,
            function (ItemInterface $item) {
                $communities = $this->getKnownLargeCommunities();

                $results = [];

                /** @var BgpLargeCommunityData $community */
                foreach ($communities as $community) {

                    if ($community->hasWildCardValue1() && $community->hasWildCardValue2()) {
                        $results[$community->getFilterValue()] = self::simpleFormat($community);
                    }

                }

                return $results;
            }
        );
    }


    public function createKnownExtendedCommunities()
    {
        $known = $this->parameters->get('bird.known');

        $communities = [];

        if (isset($known['extended_communities'])) {
            foreach ($known['extended_communities'] as $key => $values) {

                $dto = new BgpExtendedCommunityData(explode(':', $key));
                $dto->setName($values['name']);
                $dto->setLabel($values['label']);

                $communities[] = $dto;

            }
        }

        return $communities;
    }


    public function getKnownExtendedCommunities()
    {
        return $this->appCacheCommunities->get(
            self::KEY_KNOWN_EXTENDED_COMMUNITIES,
            function (ItemInterface $item) {
                return $this->createKnownExtendedCommunities();
            }
        );
    }


    public function getKnownExtendedCommunitiesArray()
    {
        return $this->appCacheCommunities->get(
            self::KEY_ARRAY_EXTENDED_COMMUNITIES,
            function (ItemInterface $item) {
                $communities = $this->getKnownExtendedCommunities();

                $results = [];

                /** @var BgpExtendedCommunityData $community */
                foreach ($communities as $community) {

                    if (! $community->hasWildCardB() && ! $community->hasWildCardC()) {
                        $results[$community->getFilterValue()] = self::simpleFormat($community);
                    }

                }

                return $results;
            }
        );
    }


    public function getKnownExtendedCommunitiesArrayX()
    {
        return $this->appCacheCommunities->get(
            self::KEY_ARRAY_EXTENDED_COMMUNITIES_X,
            function (ItemInterface $item) {
                $communities = $this->getKnownExtendedCommunities();

                $results = [];

                /** @var BgpExtendedCommunityData $community */
                foreach ($communities as $community) {

                    if (! $community->hasWildCardB() && $community->hasWildCardC()) {
                        $results[$community->getFilterValue()] = self::simpleFormat($community);
                    }

                }

                return $results;
            }
        );
    }


    public function getKnownExtendedCommunitiesArrayXX()
    {
        return $this->appCacheCommunities->get(
            self::KEY_ARRAY_EXTENDED_COMMUNITIES_XX,
            function (ItemInterface $item) {
                $communities = $this->getKnownExtendedCommunities();

                $results = [];

                /** @var BgpExtendedCommunityData $community */
                foreach ($communities as $community) {

                    if ($community->hasWildCardB() && $community->hasWildCardC()) {
                        $results[$community->getFilterValue()] = self::simpleFormat($community);
                    }

                }

                return $results;
            }
        );
    }


    /**
     * @param BgpCommunityDataInterface $dto
     *
     * @return array
     */
    private static function simpleFormat(BgpCommunityDataInterface $dto)
    {
        return [
            'id'    => $dto->getFilterValue(),
            'raw'   => $dto->getRawValue(),
            'name'  => $dto->getName(),
            'label' => $dto->getLabel(),
        ];
    }

}
