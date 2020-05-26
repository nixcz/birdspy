<?php

namespace App\Service;

use App\Data\BgpProtocolData;
use DateTime;


class BgpProtocolParser
{

    const PATTERN_BGP_PROTOCOL = "/^(\w+)\s+BGP\s+([\-\w]+)\s+(\w+)\s+([0-9\-]+\s[0-9:]+)(.\d+)?\s+(\w+).*$/";
    const PATTERN_DESCRIPTION = "/^\s+Description:\s+(.*)$/";
    const PATTERN_TABLE = "/^\s+Table:\s+(.*)$/";
    const PATTERN_IMPORT_LIMIT = "/^\s+Import limit:\s+([\d]+).*$/";
    const PATTERN_ROUTES = "/^\s+Routes:\s+(\d+)\s+imported,\s+(?:(\d+)\s+filtered,\s+)*(\d+)\s+exported,\s+(\d+)\s+preferred.*$/";
    const PATTERN_BGP_STATE = "/^\s+BGP state:\s+(\w+).*$/";
    const PATTERN_NEIGHBOR_ADDRESS = "/^\s+Neighbor address:\s+([^\s]+).*$/";
    const PATTERN_NEIGHBOR_AS = "/^\s+Neighbor AS:\s+([\d]+).*$/";
    const PATTERN_ROUTE_LIMIT = "/^\s+Route limit:\s+(\d+)\/(\d+).*$/";


    /**
     * @param string $data
     *
     * @return array
     */
    private function getProtocolBlobs(string $data)
    {
        return explode(PHP_EOL . PHP_EOL, $data);
    }


    /**
     * @param string $data
     *
     * @return array
     */
    private function getBgpProtocolBlobs(string $data)
    {
        $matches = [];

        $blobs = $this->getProtocolBlobs($data);

        foreach ($blobs as $key => $blob) {

            // Remove non BGP Protocol blobs
            if (! preg_match("/^(\w+)\s+BGP\s+.*/", $blob, $matches)) {
                unset($blobs[$key]);

                continue;
            }

        }

        return $blobs;
    }


    /**
     * @param string $data
     *
     * @return BgpProtocolData[]|array
     */
    public function getBgpProtocols(string $data)
    {
        $blobs = $this->getBgpProtocolBlobs($data);

        $protocols = [];

        foreach ($blobs as $blob) {

            if ($protocol = $this->parseBgpProtocolBlob($blob)) {
                $protocols[$protocol->getUuid()] = $protocol;
            }

        }

        return $protocols;
    }


    /**
     * @param string $data
     *
     * @return BgpProtocolData|bool
     */
    private function parseBgpProtocolBlob(string $data)
    {
        $matched = false;

        $protocolData = new BgpProtocolData();

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $data) as $line) {
            $matches = [];

            if (preg_match(self::PATTERN_BGP_PROTOCOL, $line, $matches)) {
                // pb_0109_as42    BGP      t_0109_as42       up     2016-09-30 14:18:49  Established
                // pb_0081_as30900 BGP      t_0081_as30900    start  2015-11-27 14:18:49  Active        Socket: No route to host
                // R244x1          BGP        ---             up     2019-01-23 14:18:49  Established

                $protocolData->setName($matches[1]);
                $protocolData->setTable($matches[2]);
                $protocolData->setState($matches[3]);
                $protocolData->setStateChanged(DateTime::createFromFormat('Y-m-d H:i:s', $matches[4]));

                $matched = true;
                continue;
            }

            if (preg_match(self::PATTERN_DESCRIPTION, $line, $matches)) {
                //   Description:    RIB for AS42 - Packet Clearing House DNS - VLAN Interface 109

                $protocolData->setDescription(trim($matches[1]));

                $matched = true;
                continue;
            }

            if (preg_match(self::PATTERN_TABLE, $line, $matches)) {
                //   Table:          t_R244x1

                $protocolData->setTable($matches[1]);

                $matched = true;
                continue;
            }

            if (preg_match(self::PATTERN_IMPORT_LIMIT, $line, $matches)) {
                //   Import limit:   1000
                //   Import limit:   1000 [HIT]

                $protocolData->setImportLimit(intval($matches[1]));

                $matched = true;
                continue;
            }

            if (preg_match(self::PATTERN_ROUTES, $line, $matches)) {
                //   Routes:         35 imported, 41127 exported, 2590 preferred
                //   Routes:         17 imported, 0 filtered, 27323 exported, 0 preferred

                $protocolData->setImportedRoutes(intval($matches[1]));
                $protocolData->setExportedRoutes(intval($matches[3]));

                $matched = true;
                continue;
            }

            if (preg_match(self::PATTERN_BGP_STATE, $line, $matches)) {
                //   BGP state:          Established

                $protocolData->setBgpState(trim($matches[1]));

                $matched = true;
                continue;
            }

            if (preg_match(self::PATTERN_NEIGHBOR_ADDRESS, $line, $matches)) {
                //     Neighbor address: 193.242.111.60

                $protocolData->setNeighborAddress($matches[1]);

                $matched = true;
                continue;
            }

            if (preg_match(self::PATTERN_NEIGHBOR_AS, $line, $matches)) {
                //     Neighbor AS:      42

                $protocolData->setAsn(intval($matches[1]));

                $matched = true;
                continue;
            }

            if (preg_match(self::PATTERN_ROUTE_LIMIT, $line, $matches)) {
                //     Route limit:      35/1000

                $protocolData->setRouteLimit(intval($matches[1]));

                $matched = true;
                continue;
            }

        }

        if ($matched) {
            $protocolData->setBlob($data);

            return $protocolData;
        }

        return false;
    }

}
