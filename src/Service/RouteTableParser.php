<?php

namespace App\Service;

use App\Data\BgpCommunityData;
use App\Data\BgpExtendedCommunityData;
use App\Data\BgpLargeCommunityData;
use App\Data\RouteData;
use App\Data\RouteNetworkData;
use App\Data\RouteTableData;


class RouteTableParser
{

    const PATTERN_FIRST_ROUTE = "/^([0-9a-f.:\/]+)\s+(via\s+([0-9a-f.:]+)\s+on\s+([a-zA-Z0-9_.\-\/]+)|\w+)\s+\[(\w+)\s+([0-9\-:]+(?:\s[0-9\-:]+){0,1})(?:\s+from\s+([0-9a-f.:\/]+)){0,1}]\s+(?:(\*)\s+){0,1}\((\d+)(?:\/(-|\d*)){0,1}\).*$/";
    const PATTERN_NEXT_ROUTE = "/^\s+(via\s+([0-9a-f.:]+)\s+on\s+([a-zA-Z0-9_.\-\/]+)|\w+)\s+\[(\w+)\s+([0-9\-:]+(?:\s[0-9\-:]+){0,1})(?:\s+from\s+([0-9a-f.:\/]+)){0,1}]\s+(?:(\*)\s+){0,1}\((\d+)(?:\/-\d*){0,1}\).*$/";
    const PATTERN_AS_PATH = "/^\s+BGP.as_path:\s+(.*)\s*(.*)$/";
    const PATTERN_NEXT_HOP = "/^\s+BGP.next_hop:\s+([0-9a-f.:\/]+)\s*(.*)$/";
    const PATTERN_COMMUNITY = "/^\s+BGP.community:\s*(.*)$/";
    const PATTERN_COMMUNITY_NEW_LINE = "/^\s+(\(\d+,\s?\d+\).*)$/";
    const PATTERN_LARGE_COMMUNITY = "/^\s+BGP.large_community:\s*(.*)$/";
    const PATTERN_LARGE_COMMUNITY_NEW_LINE = "/^\s+(\(\d+,\s?\d+,\s?\d+\).*)$/";
    const PATTERN_EXT_COMMUNITY = "/^\s+BGP.ext_community:\s*(.*)$/";

    /**
     * @var array
     */
    private $flags = [];

    /**
     * @var array
     */
    private $communityFlagPatterns = [];

    /**
     * @var array
     */
    private $largeCommunityFlagPatterns = [];

    /**
     * @var array
     */
    private $extendedCommunityFlagPatterns = [];

    /**
     * @var array
     */
    private $communityArray = [];

    /**
     * @var array
     */
    private $communityArrayX = [];

    /**
     * @var array
     */
    private $largeCommunityArray = [];

    /**
     * @var array
     */
    private $largeCommunityArrayX = [];

    /**
     * @var array
     */
    private $largeCommunityArrayXX = [];

    /**
     * @var array
     */
    private $extendedCommunityArray = [];

    /**
     * @var array
     */
    private $extendedCommunityArrayX = [];

    /**
     * @var array
     */
    private $extendedCommunityArrayXX = [];


    /**
     * RouteTableParser constructor.
     *
     * @param FlagService         $flagService
     * @param BgpCommunityService $communityService
     */
    public function __construct(FlagService $flagService, BgpCommunityService $communityService)
    {
        $this->flags                         = $flagService->getFlags();
        $this->communityFlagPatterns         = $flagService->getCommunityFlagPatterns();
        $this->largeCommunityFlagPatterns    = $flagService->getLargeCommunityFlagPatterns();
        $this->extendedCommunityFlagPatterns = $flagService->getExtendedCommunityFlagPatterns();

        $this->communityArray  = $communityService->getKnownCommunitiesArray();
        $this->communityArrayX = $communityService->getKnownCommunitiesArrayX();

        $this->largeCommunityArray   = $communityService->getKnownLargeCommunitiesArray();
        $this->largeCommunityArrayX  = $communityService->getKnownLargeCommunitiesArrayX();
        $this->largeCommunityArrayXX = $communityService->getKnownLargeCommunitiesArrayXX();

        $this->extendedCommunityArray   = $communityService->getKnownExtendedCommunitiesArray();
        $this->extendedCommunityArrayX  = $communityService->getKnownExtendedCommunitiesArrayX();
        $this->extendedCommunityArrayXX = $communityService->getKnownExtendedCommunitiesArrayXX();
    }


    /**
     * @param string $data
     *
     * @return RouteTableData[]|array
     */
    public function getRouteTables(string $data)
    {
        $name    = '';
        $blob    = '';
        $tables  = [];
        $matches = [];

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $data) as $line) {

            if (preg_match("/^Table\s+(\w+):$/", $line, $matches)) {
                if (strlen(trim($blob)) > 0) {
                    $dto = new RouteTableData();
                    $dto->setName($name);
                    $dto->setBlob($blob);

                    $tables[] = $dto;

                    $blob = '';
                }

                $name = $matches[1];

                continue;
            }

            if (strlen(trim($line)) > 0) {
                $blob .= "{$line}\n";
            }

        }

        // Last
        if (strlen(trim($blob)) > 0) {
            $dto = new RouteTableData();
            $dto->setName($name);
            $dto->setBlob($blob);

            $tables[] = $dto;
        }

        return $tables;
    }


    /**
     * @param string $data
     *
     * @return RouteNetworkData[]|array
     */
    public function getRouteNetworks(string $data)
    {
        $prefix   = '';
        $blob     = '';
        $networks = [];
        $matches  = [];

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $data) as $line) {

            if (preg_match("/^([0-9a-f.:\/]+)([-\d+]|\s+)/", $line, $matches)) {
                if (strlen(trim($blob)) > 0) {
                    $dto = new RouteNetworkData();
                    $dto->setPrefix($prefix);
                    $dto->setBlob($blob);

                    $networks[] = $dto;

                    $blob = '';
                }

                $prefix = $matches[1];

                $blob .= "{$line}\n";

                continue;
            }

            if (strlen(trim($line)) > 0) {
                $blob .= "{$line}\n";
            }

        }

        // Last
        if (strlen(trim($blob)) > 0) {
            $dto = new RouteNetworkData();
            $dto->setPrefix($prefix);
            $dto->setBlob($blob);

            $networks[] = $dto;
        }

        return $networks;
    }


    /**
     * @param string $data
     *
     * @return RouteData[]|array
     */
    public function getRoutes(string $data)
    {
        $blob   = '';
        $prefix = '';
        $routes = [];

        $dto = new RouteData();
        $dto->addFlag($this->flags[RouteData::KEY_VALID]);

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $data) as $line) {
            $matches = [];

            // Start Network/Route
            if (preg_match(self::PATTERN_FIRST_ROUTE, $line, $matches)) {
                // 188.93.0.0/21      via 193.242.111.54 on eth1   [pb_0127_as42227 2016-10-09] * (100) [AS42227i]
                // 2a02:2078::/32 via 2001:7f8:18:210::15 on ens160 [pb_as43760_vli226_ipv6 2016-10-13 from 2001:7f8:18:210::8] (100) [AS47720i]
                // 94.247.48.52/30    via 93.92.8.65 on eth1 [pb_core_rl01 2016-10-19 from 93.92.8.20] * (100/65559) [?]
                // 5.159.40.0/21      via 193.242.111.74 on eth1 [pb_0136_as61194 2016-03-12] * (100) [AS61194i]
                // 203.159.70.0/24    via 203.159.68.3 on eth0.99 [pb_0065_as63528 2018-07-01] * (100) [AS63528i]
                // 172.24.1.0/24        unreachable [R244x1 2019-01-23 14:18:50 from 192.0.2.244] * (100/-) [AS244i]
                // 70.40.15.0/24        unicast [pb_0003_as42 2019-01-28 10:58:03] * (100) [AS42i]
                // 192.175.48.0/24      blackhole [static_as112 2019-01-29 11:57:09] * (200)

                $prefix = $matches[1];

                // Save Previous if Any
                if (strlen(trim($blob)) > 0) {
                    $dto->setBlob($blob);
                    $blob = '';

                    $routes[] = $dto;

                    $dto = new RouteData();
                    $dto->addFlag($this->flags[RouteData::KEY_VALID]);
                }

                $dto->setNetwork($prefix);
                $dto->setFromProtocol($matches[5]);
                $dto->setMetric(intval($matches[9]));

                if ($matches[8] === '*') {
                    $dto->setPrimary(true);
                    $dto->addFlag($this->flags[RouteData::KEY_PRIMARY]);

                } else {
                    $dto->setPrimary(false);
                    $dto->addFlag($this->flags[RouteData::KEY_SECONDARY]);
                }

                $blob .= "{$line}\n";

                continue;
            }

            if (preg_match(self::PATTERN_AS_PATH, $line, $matches)) {
                // 	BGP.as_path: 42227

                $dto->setAsPath(explode(' ', trim($matches[1])));

                $blob .= "{$line}\n";

                continue;
            }

            if (preg_match(self::PATTERN_NEXT_HOP, $line, $matches)) {
                // 	BGP.next_hop: 193.242.111.54

                $dto->setNextHop($matches[1]);

                $blob .= "{$line}\n";

                continue;
            }

            if (preg_match(self::PATTERN_COMMUNITY, $line, $matches)) {
                // 	BGP.community: (0,31122) (0,6543)

                $this->parseCommunitiesLine($dto, $matches[1]);

                $blob .= "{$line}\n";

                continue;
            }

            if (preg_match(self::PATTERN_COMMUNITY_NEW_LINE, $line, $matches)) {
                //    (0,31122) (0,6543)

                $this->parseCommunitiesLine($dto, $matches[1]);

                $blob .= "{$line}\n";

                continue;
            }

            if (preg_match(self::PATTERN_LARGE_COMMUNITY, $line, $matches)) {
                // BGP.large_community: (999, 1, 111) (999, 156, 111)

                $this->parseLargeCommunitiesLine($dto, $matches[1]);

                $blob .= "{$line}\n";

                continue;
            }

            if (preg_match(self::PATTERN_LARGE_COMMUNITY_NEW_LINE, $line, $matches)) {
                //    (57463, 0, 28663) (57463, 0, 32787)

                $this->parseLargeCommunitiesLine($dto, $matches[1]);

                $blob .= "{$line}\n";

                continue;
            }

            if (preg_match(self::PATTERN_EXT_COMMUNITY, $line, $matches)) {
                // BGP.ext_community: (rt, 65253, 421000000) (rt, 65253, 421010001)

                $this->parseExtendedCommunitiesLine($dto, $matches[1]);

                $blob .= "{$line}\n";

                continue;
            }

            // TODO New line for Extended Communities?

            // Next Route
            if (preg_match(self::PATTERN_NEXT_ROUTE, $line, $matches)) {
                // Save Previous
                $dto->setBlob($blob);
                $blob = '';

                $routes[] = $dto;

                // Start New One
                $dto = new RouteData();
                $dto->addFlag($this->flags[RouteData::KEY_VALID]);
                $dto->setNetwork($prefix);
                $dto->setFromProtocol($matches[4]);
                $dto->setMetric(intval($matches[8]));

                if ($matches[7] === '*') {
                    $dto->setPrimary(true);
                    $dto->addFlag($this->flags[RouteData::KEY_PRIMARY]);

                } else {
                    $dto->setPrimary(false);
                    $dto->addFlag($this->flags[RouteData::KEY_SECONDARY]);
                }

                $blob .= $dto->getNetwork();
                $blob .= substr($line, strlen($dto->getNetwork())) . "\n";

                continue;
            }

            if (strlen(trim($line)) > 0) {
                $blob .= "{$line}\n";
            }

        }

        // Save Last
        if (strlen(trim($blob)) > 0) {
            $dto->setBlob($blob);

            $routes[] = $dto;
        }

        return $routes;
    }


    /**
     * @param string $data
     *
     * @return array
     */
    public function getRouteTableCounts(string $data)
    {
        $results = [];

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $data) as $line) {
            $matches = [];

            if (preg_match(
                "/^(\d+)\s+of\s+(\d+)\s+routes\s+for\s+(\d+)\s+networks\s+in\s+table\s+(\w+).*$/",
                $line,
                $matches
            )) {
                // 0 of 95479 routes for 85116 networks in table master4
                $results[] = [
                    'quantity' => intval($matches[1]),
                    'routes'   => intval($matches[2]),
                    'networks' => intval($matches[3]),
                    'table'    => $matches[4],
                ];
            }

        }

        return $results;
    }


    /**
     * @param RouteData $route
     * @param string    $line
     */
    private function parseCommunitiesLine(RouteData $route, string $line)
    {
        foreach (explode(' ', trim($line)) as $community) {

            if (preg_match("/^\((\d+),(\d+)\)/", $community, $matches)) {
                $key = sprintf(
                    BgpCommunityData::FILTER_FORMAT,
                    $matches[1],
                    $matches[2]
                );

                $route->addCommunity($this->getCommunity($key, $matches[1], $matches[2]));

                foreach ($this->communityFlagPatterns as $item) {

                    if (preg_match($item['pattern'], $key)) {
                        $route->addFlag($item['flag']);
                    }

                }
            }

        }
    }


    /**
     * @param $key
     * @param $asn
     * @param $value
     *
     * @return array
     */
    private function getCommunity($key, $asn, $value)
    {
        // With Same Values
        if (isset($this->communityArray[$key])) {
            return $this->communityArray[$key];
        }

        $raw = sprintf(BgpCommunityData::FORMATTED, $asn, $value);

        // With Wild Card Value
        $keyX = sprintf(
            BgpCommunityData::FILTER_FORMAT,
            $asn,
            BgpCommunityData::WILD_CARD
        );

        if (isset($this->communityArrayX[$keyX])) {
            $known = $this->communityArrayX[$keyX];

            return self::formatCommunity($key, $raw, $known['name'], $known['label']);
        }

        // Unknown Community
        return self::formatCommunity($key, $raw);
    }


    /**
     * @param RouteData $route
     * @param string    $line
     */
    private function parseLargeCommunitiesLine(RouteData $route, string $line)
    {
        $str = str_replace(", ", ",", $line);

        foreach (explode(' ', $str) as $community) {

            if (preg_match("/^\((\d+),(\d+),(\d+)\)/", trim($community), $matches)) {
                $key = sprintf(
                    BgpLargeCommunityData::FILTER_FORMAT,
                    $matches[1],
                    $matches[2],
                    $matches[3]
                );

                $route->addLargeCommunity($this->getLargeCommunity($key, $matches[1], $matches[2], $matches[3]));

                foreach ($this->largeCommunityFlagPatterns as $item) {

                    if (preg_match($item['pattern'], $key)) {
                        $route->addFlag($item['flag']);
                    }

                }
            }

        }
    }


    private function getLargeCommunity($key, $asn, $value1, $value2)
    {
        // With Same Values
        if (isset($this->largeCommunityArray[$key])) {
            return $this->largeCommunityArray[$key];
        }

        $raw = sprintf(BgpLargeCommunityData::FORMATTED, $asn, $value1, $value2);

        // With Wild Card Value2
        $keyX = sprintf(
            BgpLargeCommunityData::FILTER_FORMAT,
            $asn,
            $value1,
            BgpLargeCommunityData::WILD_CARD
        );

        if (isset($this->largeCommunityArrayX[$keyX])) {
            $known = $this->largeCommunityArrayX[$keyX];

            return self::formatCommunity($key, $raw, $known['name'], $known['label']);
        }

        // With Wild Card Value1 Value2
        $keyXX = sprintf(
            BgpLargeCommunityData::FILTER_FORMAT,
            $asn,
            BgpLargeCommunityData::WILD_CARD,
            BgpLargeCommunityData::WILD_CARD
        );

        if (isset($this->largeCommunityArrayXX[$keyXX])) {
            $known = $this->largeCommunityArrayXX[$keyXX];

            return self::formatCommunity($key, $raw, $known['name'], $known['label']);
        }

        // Unknown Community
        return self::formatCommunity($key, $raw);
    }


    /**
     * @param RouteData $route
     * @param string    $line
     */
    private function parseExtendedCommunitiesLine(RouteData $route, string $line)
    {
        $str = str_replace(", ", ",", $line);

        foreach (explode(' ', $str) as $community) {

            if (preg_match("/^\((\w+),(\w+),(\w+)\)/", trim($community), $matches)) {
                $key = sprintf(
                    BgpExtendedCommunityData::FILTER_FORMAT,
                    $matches[1],
                    $matches[2],
                    $matches[3]
                );

                $route->addExtendedCommunity($this->getExtendedCommunity($key, $matches[1], $matches[2], $matches[3]));

                foreach ($this->extendedCommunityFlagPatterns as $item) {

                    if (preg_match($item['pattern'], $key)) {
                        $route->addFlag($item['flag']);
                    }

                }
            }

        }
    }


    private function getExtendedCommunity($key, $a, $b, $c)
    {
        // With Same Values
        if (isset($this->extendedCommunityArray[$key])) {
            return $this->extendedCommunityArray[$key];
        }

        $raw = sprintf(BgpExtendedCommunityData::FORMATTED, $a, $b, $c);

        // With Wild Card Value C
        $keyX = sprintf(
            BgpExtendedCommunityData::FILTER_FORMAT,
            $a,
            $b,
            BgpExtendedCommunityData::WILD_CARD
        );

        if (isset($this->extendedCommunityArrayX[$keyX])) {
            $known = $this->extendedCommunityArrayX[$keyX];

            return self::formatCommunity($key, $raw, $known['name'], $known['label']);
        }

        // With Wild Card Value B, C
        $keyXX = sprintf(
            BgpExtendedCommunityData::FILTER_FORMAT,
            $a,
            BgpExtendedCommunityData::WILD_CARD,
            BgpExtendedCommunityData::WILD_CARD
        );

        if (isset($this->extendedCommunityArrayXX[$keyXX])) {
            $known = $this->extendedCommunityArrayXX[$keyXX];

            return self::formatCommunity($key, $raw, $known['name'], $known['label']);
        }

        // Unknown Community
        return self::formatCommunity($key, $raw);
    }


    private static function formatCommunity($id, $raw, $name = null, $label = null)
    {
        return [
            'id'    => $id,
            'raw'   => $raw,
            'name'  => $name,
            'label' => $label,
        ];
    }

}
