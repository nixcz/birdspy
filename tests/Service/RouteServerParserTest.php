<?php

namespace App\Tests\Service;

use App\Service\RouteServerParser;
use PHPUnit\Framework\TestCase;


class RouteServerParserTest extends TestCase
{

    /**
     * @dataProvider birdVersionProvider
     *
     * @param string $subject
     * @param array  $expected
     */
    public function testBirdVersionPattern(string $subject, array $expected)
    {
        preg_match(RouteServerParser::PATTERN_BIRD, $subject, $matches);

        $this->assertEquals($expected['name'], $matches[1]);
        $this->assertEquals($expected['version'], $matches[2]);
    }


    public function birdVersionProvider()
    {
        return [
            [
                'BIRD 2.0.4 ready.',
                [
                    'name'    => null,
                    'version' => '2.0.4',
                ],
            ],
            [
                'BIRD debian/2.0.7-2-x',
                [
                    'name'    => 'debian',
                    'version' => '2.0.7-2-x',
                ],
            ],
            [
                'BIRD debian/2.0.7-2-x ready.',
                [
                    'name'    => 'debian',
                    'version' => '2.0.7-2-x',
                ],
            ],

        ];
    }


    /**
     * @dataProvider birdRouterIdProvider
     *
     * @param string $subject
     * @param string $expected
     */
    public function testBirdRouterIdPattern(string $subject, string $expected)
    {
        preg_match(RouteServerParser::PATTERN_ROUTER, $subject, $matches);

        $this->assertEquals($expected, $matches[1]);
    }


    public function birdRouterIdProvider()
    {
        return [
            ['Router ID is 91.210.16.1', '91.210.16.1'],
        ];
    }


    /**
     * @dataProvider birdServerTimeProvider
     *
     * @param string $subject
     * @param string $expected
     */
    public function testBirdServerTimePattern(string $subject, string $expected)
    {
        preg_match(RouteServerParser::PATTERN_SERVER_TIME, $subject, $matches);

        $this->assertEquals($expected, $matches[1]);
    }


    public function birdServerTimeProvider()
    {
        return [
            ['Current server time is 2019-12-16 16:40:17', '2019-12-16 16:40:17'],
            ['Current server time is 2020-05-22 14:17:15.150', '2020-05-22 14:17:15'],
        ];
    }


    /**
     * @dataProvider birdLastRebootProvider
     *
     * @param string $subject
     * @param string $expected
     */
    public function testBirdLastRebootPattern(string $subject, string $expected)
    {
        preg_match(RouteServerParser::PATTERN_LAST_REBOOT, $subject, $matches);

        $this->assertEquals($expected, $matches[1]);
    }


    public function birdLastRebootProvider()
    {
        return [
            ['Last reboot on 2019-11-12 11:32:10', '2019-11-12 11:32:10'],
            ['Last reboot on 2020-05-19 18:09:36.987', '2020-05-19 18:09:36'],
        ];
    }


    /**
     * @dataProvider birdLastReconfigurationProvider
     *
     * @param string $subject
     * @param string $expected
     */
    public function testBirdLastReconfigurationPattern(string $subject, string $expected)
    {
        preg_match(RouteServerParser::PATTERN_LAST_RECONFIGURATION, $subject, $matches);

        $this->assertEquals($expected, $matches[1]);
    }


    public function birdLastReconfigurationProvider()
    {
        return [
            ['Last reconfiguration on 2019-12-16 16:02:16', '2019-12-16 16:02:16'],
            ['Last reconfiguration on 2020-05-19 18:09:36.987', '2020-05-19 18:09:36'],
        ];
    }

}
