<?php

namespace unit;

use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;
use rollun\StreetPricer\Client;
use rollun\StreetPricer\Repricer;

class RepricerTest extends TestCase
{
    public function testAuthWithCache()
    {
        $username = 'aaa';
        $password = 'bbb';
        $expected = md5('auth/token' . json_encode([
            'username' => $username,
            'password' => $password
        ]));

        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('post')->willReturnCallback(function (string $path, array $params) {
            return md5($path . json_encode($params));
        });

        $cache = $this->createMock(CacheInterface::class);
        $cache->expects($this->any())->method('has')->with(Repricer::TOKEN_CACHE_KEY)->willReturnCallback(function () {
            static $cached = false;
            if (!$cached) {
                $cached = true;
                return false;
            }
            return $cached;
        });
        $cache->expects($this->atLeast(1))->method('get')->willReturn($expected);

        $repricer = new Repricer($username, $password, $client, $cache);
        $token = $repricer->auth();

        $this->assertEquals($expected, $token);

        $token = $repricer->auth();

        $this->assertEquals($expected, $token);
    }
}