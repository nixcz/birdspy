<?php

namespace App\Tests\Service;

use App\Service\BgpProtocolParser;
use PHPUnit\Framework\TestCase;


class BgpProtocolParserTest extends TestCase
{

    /**
     * @dataProvider bgpProtocolProvider
     *
     * @param string $subject
     * @param array  $expected
     */
    public function testBgpProtocolPattern(string $subject, array $expected)
    {
        preg_match(BgpProtocolParser::PATTERN_BGP_PROTOCOL, $subject, $matches);

        $this->assertEquals($expected['name'], $matches[1]);
        $this->assertEquals($expected['table'], $matches[2]);
        $this->assertEquals($expected['state'], $matches[3]);
        $this->assertEquals($expected['state_changed'], $matches[4]);
    }

    public function bgpProtocolProvider()
    {
        return [
            [
                'pb_0109_as42    BGP      t_0109_as42       up     2016-09-30 14:18:49  Established',
                [
                    'name'          => 'pb_0109_as42',
                    'table'         => 't_0109_as42',
                    'state'         => 'up',
                    'state_changed' => '2016-09-30 14:18:49',
                ],
            ],
            [
                'pb_0081_as30900 BGP      t_0081_as30900    start  2015-11-27 14:18:49  Active        Socket: No route to host',
                [
                    'name'          => 'pb_0081_as30900',
                    'table'         => 't_0081_as30900',
                    'state'         => 'start',
                    'state_changed' => '2015-11-27 14:18:49',
                ],
            ],
            [
                'R244x1          BGP        ---             up     2019-01-23 14:18:49  Established',
                [
                    'name'          => 'R244x1',
                    'table'         => '---',
                    'state'         => 'up',
                    'state_changed' => '2019-01-23 14:18:49',
                ],
            ],
            [
                'R244x1          BGP        ---             up     2019-01-23 14:18:49.150  Established',
                [
                    'name'          => 'R244x1',
                    'table'         => '---',
                    'state'         => 'up',
                    'state_changed' => '2019-01-23 14:18:49',
                ],
            ],
        ];
    }


    /**
     * @dataProvider bgpDescriptionProvider
     *
     * @param string $subject
     * @param string $expected
     */
    public function testBgpDescriptionPattern(string $subject, string $expected)
    {
        preg_match(BgpProtocolParser::PATTERN_DESCRIPTION, $subject, $matches);

        $this->assertEquals($expected, $matches[1]);
    }


    public function bgpDescriptionProvider()
    {
        return [
            [
                '  Description:    RIB for AS42 - Packet Clearing House DNS - VLAN Interface 109',
                'RIB for AS42 - Packet Clearing House DNS - VLAN Interface 109',
            ],
        ];
    }


    /**
     * @dataProvider bgpTableProvider
     *
     * @param string $subject
     * @param string $expected
     */
    public function testBgpTablePattern(string $subject, string $expected)
    {
        preg_match(BgpProtocolParser::PATTERN_TABLE, $subject, $matches);

        $this->assertEquals($expected, $matches[1]);
    }


    public function bgpTableProvider()
    {
        return [
            ['  Table:          t_R244x1', 't_R244x1'],
        ];
    }


    /**
     * @dataProvider bgpImportLimitProvider
     *
     * @param string $subject
     * @param string $expected
     */
    public function testBgpImportLimitPattern(string $subject, string $expected)
    {
        preg_match(BgpProtocolParser::PATTERN_IMPORT_LIMIT, $subject, $matches);

        $this->assertEquals($expected, $matches[1]);
    }


    public function bgpImportLimitProvider()
    {
        return [
            ['    Import limit:   1000', '1000'],
            ['    Import limit:   1000 [HIT]', '1000'],
        ];
    }


    /**
     * @dataProvider bgpRoutesProvider
     *
     * @param string $subject
     * @param array  $expected
     */
    public function testBgpRoutesPattern(string $subject, array $expected)
    {
        preg_match(BgpProtocolParser::PATTERN_ROUTES, $subject, $matches);

        $this->assertEquals($expected['imported'], $matches[1]);
        $this->assertEquals($expected['exported'], $matches[3]);
    }


    public function bgpRoutesProvider()
    {
        return [
            [
                '    Routes:         35 imported, 41127 exported, 2590 preferred',
                [
                    'imported' => '35',
                    'filtered' => null,
                    'exported' => '41127',
                    'preferred' => '2590',
                ],
            ],
            [
                '    Routes:         17 imported, 0 filtered, 27323 exported, 0 preferred',
                [
                    'imported' => '17',
                    'filtered' => '0',
                    'exported' => '27323',
                    'preferred' => '0',
                ],
            ],
        ];
    }


    /**
     * @dataProvider bgpStateProvider
     *
     * @param string $subject
     * @param string $expected
     */
    public function testBgpStatePattern(string $subject, string $expected)
    {
        preg_match(BgpProtocolParser::PATTERN_BGP_STATE, $subject, $matches);

        $this->assertEquals($expected, $matches[1]);
    }


    public function bgpStateProvider()
    {
        return [
            ['  BGP state:          Established', 'Established'],
        ];
    }


    /**
     * @dataProvider bgpNeighborAddressProvider
     *
     * @param string $subject
     * @param string $expected
     */
    public function testBgpNeighborAddressPattern(string $subject, string $expected)
    {
        preg_match(BgpProtocolParser::PATTERN_NEIGHBOR_ADDRESS, $subject, $matches);

        $this->assertEquals($expected, $matches[1]);
    }


    public function bgpNeighborAddressProvider()
    {
        return [
            ['    Neighbor address: 193.242.111.60', '193.242.111.60'],
        ];
    }


    /**
     * @dataProvider bgpNeighborASProvider
     *
     * @param string $subject
     * @param string $expected
     */
    public function testBgpNeighborASPattern(string $subject, string $expected)
    {
        preg_match(BgpProtocolParser::PATTERN_NEIGHBOR_AS, $subject, $matches);

        $this->assertEquals($expected, $matches[1]);
    }


    public function bgpNeighborASProvider()
    {
        return [
            ['    Neighbor AS:      42', '42'],
        ];
    }


    /**
     * @dataProvider bgpRouteLimitProvider
     *
     * @param string $subject
     * @param string $expected
     */
    public function testBgpRouteLimitPattern(string $subject, string $expected)
    {
        preg_match(BgpProtocolParser::PATTERN_ROUTE_LIMIT, $subject, $matches);

        $this->assertEquals($expected, $matches[1]);
    }


    public function bgpRouteLimitProvider()
    {
        return [
            ['    Route limit:      35/1000', '35'],
        ];
    }

}
