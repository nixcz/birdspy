<?php

namespace App\Bird;

use App\Data\BgpCommunityData;
use App\Data\BgpExtendedCommunityData;
use App\Data\BgpLargeCommunityData;
use Exception;


class ShowCommand
{

    public static function showStatus()
    {
        return sprintf('show status');
    }


    public static function showSymbols()
    {
        return sprintf('show symbols');
    }


    public static function showProtocols(bool $count = false)
    {
        $suffix = $count ? 'count' : 'all';

        return sprintf('show protocols %s', $suffix);
    }


    public static function showBfdSessions()
    {
        return sprintf('show bfd sessions');
    }


    public static function showRoute(bool $count = false)
    {
        $suffix = $count ? 'count' : 'all';

        return sprintf('show route %s', $suffix);
    }


    // Don't do this: 'table all all', too much results
    // Command 'table all count' ~30 s
    public static function showRouteTable(CommandParameters $parameters)
    {
        if ($parameters->isForAllTables()) {
            return new Exception('Results for all tables are not permitted!');
        }

        return sprintf(
            'show route table %s %s',
            $parameters->getTable(),
            $parameters->getSuffix()
        );
    }


    public static function showRouteProtocol(CommandParameters $parameters)
    {
        return sprintf(
            'show route protocol %s %s',
            $parameters->getProtocol(),
            $parameters->getSuffix()
        );
    }


    public static function showRouteExport(CommandParameters $parameters)
    {
        return sprintf(
            'show route export %s %s',
            $parameters->getExport(),
            $parameters->getSuffix()
        );
    }


    public static function showRouteForPrefixAndTable(CommandParameters $parameters)
    {
        return sprintf(
            'show route for %s table %s %s',
            $parameters->getPrefix(),
            $parameters->getTable(),
            $parameters->getSuffix()
        );
    }


    public static function showRouteForPrefixAndProtocol(CommandParameters $parameters)
    {
        return sprintf(
            'show route for %s protocol %s %s',
            $parameters->getPrefix(),
            $parameters->getProtocol(),
            $parameters->getSuffix()
        );
    }


    public static function showRouteForPrefixAndExport(CommandParameters $parameters)
    {
        return sprintf(
            'show route for %s export %s %s',
            $parameters->getPrefix(),
            $parameters->getExport(),
            $parameters->getSuffix()
        );
    }


    // Command 'table all count or all' ~40s
    public static function showRouteTableFilteredByCommunity(CommandParameters $parameters)
    {
        $communities = [];

        foreach ($parameters->getBgpCommunities() as $community) {

            if ($community instanceof BgpCommunityData) {
                $communities[] = sprintf(
                    '(bgp_community ~ [(%s, %s)])',
                    $community->getAsNumber(),
                    $community->getValue()
                );
            }

            if ($community instanceof BgpLargeCommunityData) {
                $communities[] = sprintf(
                    '(bgp_large_community ~ [(%s, %s, %s)])',
                    $community->getAsNumber(),
                    $community->getValue1(),
                    $community->getValue2()
                );
            }

            if ($community instanceof BgpExtendedCommunityData) {
                $communities[] = sprintf(
                    '(bgp_ext_community ~ [(%s, %s, %s)])',
                    $community->getA(),
                    $community->getB(),
                    $community->getC()
                );
            }

        }

        $glue = sprintf(' %s ', $parameters->getBgpCommunitiesCondition());

        return sprintf(
            'show route table %s filter { if %s then accept; reject; } %s',
            $parameters->getTable(),
            implode($glue, $communities),
            $parameters->getSuffix()
        );
    }

}
