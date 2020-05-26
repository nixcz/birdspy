<?php

namespace App\Service;

use App\Data\RouteServerData;
use DateTime;


class RouteServerParser
{

    const PATTERN_BIRD = "/^BIRD\s(?:(\w+)\/)?(?:([0-9a-zA-Z\/\-.]+).).*$/";
    const PATTERN_ROUTER = "/^Router\sID\sis\s([0-9.]+).*$/";
    const PATTERN_SERVER_TIME = "/^Current\sserver\stime\sis\s([0-9\-]+\s[0-9:]+).*$/";
    const PATTERN_LAST_REBOOT = "/^Last\sreboot\son\s([0-9\-]+\s[0-9:]+).*$/";
    const PATTERN_LAST_RECONFIGURATION = "/^Last\sreconfiguration\son\s([0-9\-]+\s[0-9:]+).*$/";


    public function updateServerStatus(RouteServerData $server, string $data)
    {
        foreach (preg_split("/((\r?\n)|(\r\n?))/", $data) as $line) {

            $matches = [];

            if (preg_match(self::PATTERN_BIRD, $line, $matches)) {
                // BIRD 2.0.4 ready.
                // BIRD debian/2.0.7-2-x ready.
                // BIRD debian/2.0.7-2-x

                $server->setVersion($matches[2]);

                continue;
            }

            if (preg_match(self::PATTERN_ROUTER, $line, $matches)) {
                // Router ID is 91.210.16.1

                $server->setRouterId($matches[1]);

                continue;
            }

            if (preg_match(self::PATTERN_SERVER_TIME, $line, $matches)) {
                // Current server time is 2019-12-16 16:40:17
                // Current server time is 2019-12-16 16:40:17.150

                $serverTime = DateTime::createFromFormat(
                    'Y-m-d H:i:s',
                    $matches[1]
                );

                $server->setServerTime($serverTime);

                continue;
            }

            if (preg_match(self::PATTERN_LAST_REBOOT, $line, $matches)) {
                // Last reboot on 2019-11-12 11:32:10
                // Last reboot on 2019-11-12 11:32:10.150

                $lastReboot = DateTime::createFromFormat(
                    'Y-m-d H:i:s',
                    $matches[1]
                );

                $server->setLastReboot($lastReboot);

                continue;
            }

            if (preg_match(self::PATTERN_LAST_RECONFIGURATION, $line, $matches)) {
                // Last reconfiguration on 2019-12-16 16:02:16
                // Last reconfiguration on 2019-12-16 16:02:16.150

                $lastReconfig = DateTime::createFromFormat(
                    'Y-m-d H:i:s',
                    $matches[1]
                );

                $server->setLastReconfiguration($lastReconfig);

                continue;
            }

            if (! preg_match("/^\s*$/", $line)) {
                // Daemon is up and running

                $server->setMessage($line);

                continue;
            }

        }
    }

}
