<?php
declare(strict_types=1);

use chilimatic\lib\Cache\Engine\Adapter\APCU;
use chilimatic\lib\Cache\Engine\Adapter\Memcached;
use chilimatic\lib\Cache\Engine\Adapter\Memory;
use chilimatic\lib\Cache\Engine\Cache;
use chilimatic\lib\Interfaces\ISingelton;

class CacheTest extends \PHPUnit\Framework\TestCase
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
     * @before
     */
    public function before(){
        Cache::destroy();
    }

    /**
     * @test
     * @throws \chilimatic\lib\Cache\Exception\CacheException
     */
    public function implementsTheCorrectInterface(): void
    {
        $this->assertInstanceOf(ISingelton::class, Cache::getInstance([
            Cache::IDX_ADAPTER_NAME => Memory::class
        ]));
    }

    /**
     * @test
     * @throws \chilimatic\lib\Cache\Exception\CacheException
     */
    public function getMemcachedInstance(): void
    {
        $cache = Cache::getInstance([
            Cache::IDX_ADAPTER_NAME => Memcached::class,
            Cache::IDX_PARAMETERS => self::DEFAULT_MEMCACHED_SETUP
        ]);

        self::assertInstanceOf(Memcached::class, $cache->getEngine());
    }

    /**
     * @test
     * @throws \chilimatic\lib\Cache\Exception\CacheException
     */
    public function getAPCUInstance(): void
    {
        $cache = Cache::getInstance([
            Cache::IDX_ADAPTER_NAME => APCU::class,
        ]);

        self::assertInstanceOf(APCU::class, $cache->getEngine());
    }

    /**
     * @test
     * @throws \chilimatic\lib\Cache\Exception\CacheException
     */
    public function getMemoryInstance(): void
    {
        $cache = Cache::getInstance([
            Cache::IDX_ADAPTER_NAME => Memory::class,
        ]);

        self::assertInstanceOf(Memory::class, $cache->getEngine());
    }

    /**
     * @test
     * @throws \chilimatic\lib\Cache\Exception\CacheException
     */
    public function setValue(): void
    {
        Cache::getInstance([
            Cache::IDX_ADAPTER_NAME => Memory::class,
        ]);

        Cache::set('test', 12);

        self::assertEquals(12, Cache::get('test'));
    }

    /**
     * @test
     * @throws \chilimatic\lib\Cache\Exception\CacheException
     */
    public function setAndDeleteValue(): void
    {
        Cache::getInstance([
            Cache::IDX_ADAPTER_NAME => Memory::class,
        ]);

        Cache::set('test', 12);
        Cache::delete('test');

        self::assertEquals(null, Cache::get('test'));
    }

    /**
     * @test
     * @throws \chilimatic\lib\Cache\Exception\CacheException
     */
    public function hasValue(): void
    {
        Cache::getInstance([
            Cache::IDX_ADAPTER_NAME => Memory::class,
        ]);

        Cache::set('test', 12);

        self::assertTrue(Cache::has('test'));
    }

}