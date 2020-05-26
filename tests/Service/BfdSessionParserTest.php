<?php

namespace App\Tests\Service;

use App\Service\BfdSessionParser;
use PHPUnit\Framework\TestCase;


class BfdSessionParserTest extends TestCase
{

    /**
     * @dataProvider bfdSessionProvider
     *
     * @param string $subject
     * @param array  $expected
     */
    public function testBfdSessionPattern(string $subject, array $expected)
    {
        preg_match(BfdSessionParser::PATTERN_BFD_SESSION, $subject, $matches);

        $this->assertEquals($expected['ip_address'], $matches[1]);
        $this->assertEquals($expected['interface'], $matches[2]);
        $this->assertEquals($expected['state'], $matches[3]);
        $this->assertEquals($expected['since'], $matches[4]);
        $this->assertEquals($expected['interval'], $matches[5]);
        $this->assertEquals($expected['timeout'], $matches[6]);
    }


    public function bfdSessionProvider()
    {
        return [
            [
                '91.210.16.151             bond0.10   Up         2019-11-19 02:19:59    1.000    5.000',
                [
                    'ip_address' => '91.210.16.151',
                    'interface'  => 'bond0.10',
                    'state'      => 'Up',
                    'since'      => '2019-11-19 02:19:59',
                    'interval'   => '1.000',
                    'timeout'    => '5.000',
                ],
            ],
            [
                '91.210.16.41              ens19      Up         2019-11-25 07:05:20    1.000    5.000',
                [
                    'ip_address' => '91.210.16.41',
                    'interface'  => 'ens19',
                    'state'      => 'Up',
                    'since'      => '2019-11-25 07:05:20',
                    'interval'   => '1.000',
                    'timeout'    => '5.000',
                ],
            ],
            [
                '91.210.16.41              ens19      Up         2019-11-25 07:05:20.150    1.000    5.000',
                [
                    'ip_address' => '91.210.16.41',
                    'interface'  => 'ens19',
                    'state'      => 'Up',
                    'since'      => '2019-11-25 07:05:20',
                    'interval'   => '1.000',
                    'timeout'    => '5.000',
                ],
            ],
        ];
    }

}
