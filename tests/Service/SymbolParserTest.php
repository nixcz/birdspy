<?php

namespace App\Tests\Service;

use App\Service\SymbolParser;
use PHPUnit\Framework\TestCase;


class SymbolParserTest extends TestCase
{

    /**
     * @dataProvider symbolProvider
     *
     * @param string $subject
     * @param string $expected
     */
    public function testSymbolPattern(string $subject, string $expected)
    {
        preg_match(SymbolParser::PATTERN_SYMBOL, $subject, $matches);

        $this->assertEquals($expected, $matches[1]);
    }


    public function symbolProvider()
    {
        return [
            ['R4_59970x3	protocol', 'R4_59970x3', 'protocol'],
            ['T4_57463x2	routing table', 'T4_57463x2', 'routing table'],
            ['bgp_out 	function', 'bgp_out', 'function'],
            ['info    	undefined', 'info', 'undefined'],
            ['bgp6_in_AS59970x1	filter', 'bgp6_in_AS59970x1', 'filter'],
            ['ips_site	constant', 'ips_site', 'constant'],
        ];
    }

}
