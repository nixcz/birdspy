<?php

namespace App\Tests\Service;

use App\Service\RouteTableParser;
use PHPUnit\Framework\TestCase;


class RouteTableParserTest extends TestCase
{

    /**
     * @dataProvider firstRouteProvider
     *
     * @param string $subject
     * @param array  $expected
     */
    public function testFirstRoutePattern(string $subject, array $expected)
    {
        preg_match(RouteTableParser::PATTERN_FIRST_ROUTE, $subject, $matches);

        $this->assertEquals($expected['prefix'], $matches[1]);
        $this->assertEquals($expected['from_protocol'], $matches[5]);
        $this->assertEquals($expected['primary'], $matches[8]);
        $this->assertEquals($expected['metric'], $matches[9]);
    }


    public function firstRouteProvider()
    {
        return [
            [
                '188.93.0.0/21      via 193.242.111.54 on eth1   [pb_0127_as42227 2016-10-09] * (100) [AS42227i]',
                [
                    'prefix'        => '188.93.0.0/21',
                    'from_protocol' => 'pb_0127_as42227',
                    'primary'       => '*',
                    'metric'        => '100',
                ],
            ],
            [
                '2a02:2078::/32 via 2001:7f8:18:210::15 on ens160 [pb_as43760_vli226_ipv6 2016-10-13 from 2001:7f8:18:210::8] (100) [AS47720i]',
                [
                    'prefix'        => '2a02:2078::/32',
                    'from_protocol' => 'pb_as43760_vli226_ipv6',
                    'primary'       => null,
                    'metric'        => '100',
                ],
            ],
            [
                '94.247.48.52/30    via 93.92.8.65 on eth1 [pb_core_rl01 2016-10-19 from 93.92.8.20] * (100/65559) [?]',
                [
                    'prefix'        => '94.247.48.52/30',
                    'from_protocol' => 'pb_core_rl01',
                    'primary'       => '*',
                    'metric'        => '100',
                ],
            ],
            [
                '5.159.40.0/21      via 193.242.111.74 on eth1 [pb_0136_as61194 2016-03-12] * (100) [AS61194i]',
                [
                    'prefix'        => '5.159.40.0/21',
                    'from_protocol' => 'pb_0136_as61194',
                    'primary'       => '*',
                    'metric'        => '100',
                ],
            ],
            [
                '203.159.70.0/24    via 203.159.68.3 on eth0.99 [pb_0065_as63528 2018-07-01] * (100) [AS63528i]',
                [
                    'prefix'        => '203.159.70.0/24',
                    'from_protocol' => 'pb_0065_as63528',
                    'primary'       => '*',
                    'metric'        => '100',
                ],
            ],
            [
                '172.24.1.0/24        unreachable [R244x1 2019-01-23 14:18:50 from 192.0.2.244] * (100/-) [AS244i]',
                [
                    'prefix'        => '172.24.1.0/24',
                    'from_protocol' => 'R244x1',
                    'primary'       => '*',
                    'metric'        => '100',
                ],
            ],
            [
                '70.40.15.0/24        unicast [pb_0003_as42 2019-01-28 10:58:03] * (100) [AS42i]',
                [
                    'prefix'        => '70.40.15.0/24',
                    'from_protocol' => 'pb_0003_as42',
                    'primary'       => '*',
                    'metric'        => '100',
                ],
            ],
            [
                '192.175.48.0/24      blackhole [static_as112 2019-01-29 11:57:09] * (200)',
                [
                    'prefix'        => '192.175.48.0/24',
                    'from_protocol' => 'static_as112',
                    'primary'       => '*',
                    'metric'        => '200',
                ],
            ],
        ];
    }


    /**
     * @dataProvider nextRouteProvider
     *
     * @param string $subject
     * @param array  $expected
     */
    public function testNextRoutePattern(string $subject, array $expected)
    {
        preg_match(RouteTableParser::PATTERN_NEXT_ROUTE, $subject, $matches);

        $this->assertEquals($expected['from_protocol'], $matches[4]);
        $this->assertEquals($expected['primary'], $matches[7]);
        $this->assertEquals($expected['metric'], $matches[8]);
    }


    public function nextRouteProvider()
    {
        return [
            [
                '                     unicast [R4_6461x2 2019-12-10 01:02:01] (100) [AS48504i]',
                [
                    'from_protocol' => 'R4_6461x2',
                    'primary'       => null,
                    'metric'        => '100',
                ],
            ],
            [
                '                     unicast [R6_47232x1 2019-12-10 05:56:50] (100) [AS196782?]',
                [
                    'from_protocol' => 'R6_47232x1',
                    'primary'       => null,
                    'metric'        => '100',
                ],
            ],
        ];
    }

}
