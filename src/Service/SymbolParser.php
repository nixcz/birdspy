<?php

namespace App\Service;

use App\Data\SymbolData;


class SymbolParser
{

    const PROTOCOL = 'protocol';
    const TABLE = 'routing table';

    const PATTERN_SYMBOL = "/^([^\s]+)\s+(.+)$/";


    /**
     * @param string $data
     *
     * @return array
     */
    public function getSymbols(string $data)
    {
        $symbols = [
            self::PROTOCOL => [],
            self::TABLE    => [],
        ];

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $data) as $line) {
            $matches = [];

            if (preg_match(self::PATTERN_SYMBOL, $line, $matches)) {
                // R4_59970x3	protocol
                // T4_57463x2	routing table
                // bgp_out 	function
                // info    	undefined
                // bgp6_in_AS59970x1	filter
                // ips_site	constant

                $type = $matches[2];

                $symbols[$type][] = new SymbolData($matches[1], $type);
            }

        }

        return [
            SymbolData::TYPE_PROTOCOL => $symbols[self::PROTOCOL],
            SymbolData::TYPE_TABLE    => $symbols[self::TABLE],
        ];
    }

}
