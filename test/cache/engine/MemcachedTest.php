<?php
declare(strict_types=1);

use chilimatic\lib\Cache\Engine\Adapter\Memcached;
use chilimatic\lib\Cache\Engine\ICache;

class MemcachedTest extends \PHPUnit\Framework\TestCase
{
    private const DEFAULT_MEMCACHED_SETUP = [
        'server_list' => [
            [
                'host' => '127.0.0.1',
                'port' => 11211,
                'weight' => 1,
            ]
        ]
    ];

    /**
     * @test
     */
    public function implementsTheCorrectInterface(): void
    {
        $this->assertInstanceOf(ICache::class, new Memcached());
    }

    /**
     * @test
     */
    public function setUpWithStdClass(): void
    {
        $memcachedParams = new stdClass();
        $server = new stdClass();
        $server->host = '127.0.0.1';
        $server->port = 11211;
        $server->weight = 1;
        $memcachedParams->server_list = [$server];

        $cache = new Memcached($memcachedParams);

        self::assertTrue($cache->isConnected());
    }

    /**
     * @test
     */
    public function setUpWithArray(): void
    {
        $cache = new Memcached(self::DEFAULT_MEMCACHED_SETUP);
        self::assertTrue($cache->isConnected());
    }

    /**
     * @test
     */
    public function setValue(): void
    {
        $cache = new Memcached(self::DEFAULT_MEMCACHED_SETUP);

        $value = ['12'];
        $cache->set('test', ['12']);

        self::assertEquals($cache->get('test'), $value);
    }

    /**
     * @test
     */
    public function setValueAndUpdateIt(): void
    {
        $cache = new Memcached(self::DEFAULT_MEMCACHED_SETUP);

        $value = ['12'];
        $value2 = ['14'];
        $cache->set('test', $value);

        self::assertEquals($cache->get('test'), $value);

        $cache->set('test', $value2);

        self::assertEquals($cache->get('test'), $value2);
    }


    /**
     * @test
     */
    public function setValueAndRemoveIt(): void
    {
        $cache = new Memcached(self::DEFAULT_MEMCACHED_SETUP);

        $value = ['12'];
        $cache->set('test', $value);
        $cache->delete('test');

        self::assertEquals(null, $cache->get('test'));
    }
}