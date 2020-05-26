<?php

namespace App\Service;

use App\Data\BfdSessionData;
use DateTime;


class BfdSessionParser
{

    const PATTERN_BFD_SESSION = "/^([0-9a-f.:\/]+)\s+([0-9a-z.]+)\s+(\w+)\s+([0-9\-]+\s[0-9:]+)(?:.\d+)?\s+(\d+.\d+)\s+(\d+.\d+).*$/";


    /**
     * @param string $data
     *
     * @return BfdSessionData[]|array
     */
    public function getBfdSessions(string $data)
    {
        $sessions = [];

        foreach (preg_split("/((\r?\n)|(\r\n?))/", $data) as $line) {
            $matches = [];

            if (preg_match(self::PATTERN_BFD_SESSION, $line, $matches)) {
                // IP address                Interface  State      Since         Interval  Timeout
                // 91.210.16.151             bond0.10   Up         2019-11-19 02:19:59    1.000    5.000
                // 91.210.16.41              ens19      Up         2019-11-25 07:05:20    1.000    5.000

                $sessionData = new BfdSessionData();

                $sessionData->setIpAddress($matches[1]);
                $sessionData->setInterface($matches[2]);
                $sessionData->setState($matches[3]);
                $sessionData->setSince(DateTime::createFromFormat('Y-m-d H:i:s', $matches[4]));
                $sessionData->setInterval(floatval($matches[5]));
                $sessionData->setTimeout(floatval($matches[6]));

                $sessions[] = $sessionData;
            }

        }

        return $sessions;
    }

}
