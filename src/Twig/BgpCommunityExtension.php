<?php

namespace App\Twig;

use App\Data\BgpCommunityData;
use App\Data\BgpExtendedCommunityData;
use App\Data\BgpLargeCommunityData;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;


class BgpCommunityExtension extends AbstractExtension
{

    public function getFilters()
    {
        return [
            new TwigFilter('bgpCommunity', [$this, 'formatBgpCommunity']),
            new TwigFilter('bgpLargeCommunity', [$this, 'formatBgpLargeCommunity']),
            new TwigFilter('bgpExtendedCommunity', [$this, 'formatBgpExtendedCommunity']),
        ];
    }


    public function formatBgpCommunity($key)
    {
        $community = new BgpCommunityData(explode(':', $key));

        return $community->getRawValue();
    }

    public function formatBgpLargeCommunity($key)
    {
        $community = new BgpLargeCommunityData(explode(':', $key));

        return $community->getRawValue();
    }

    public function formatBgpExtendedCommunity($key)
    {
        $community = new BgpExtendedCommunityData(explode(':', $key));

        return $community->getRawValue();
    }

}
