<?php
declare(strict_types=1);

use chilimatic\lib\Cache\Engine\Adapter\APCU;
use chilimatic\lib\Cache\Engine\Adapter\Memcached;
use chilimatic\lib\Cache\Engine\Adapter\Memory;
use chilimatic\lib\Cache\Engine\Cache;
use chilimatic\lib\Cache\Engine\CacheFactory;
use chilimatic\lib\Interfaces\ISingelton;

class CacheFactoryTest extends \PHPUnit\Framework\TestCase
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
     * @throws \chilimatic\lib\Cache\Exception\CacheException
     */
    public function getMemcachedInstance(): void
    {
        $engine = CacheFactory::make(
             Memcached::class,
        self::DEFAULT_MEMCACHED_SETUP
        );

        self::assertInstanceOf(Memcached::class, $engine);
    }

    /**
     * @test
     * @throws \chilimatic\lib\Cache\Exception\CacheException
     */
    public function getAPCUInstance(): void
    {
        $engine = CacheFactory::make(
            APCU::class,
            self::DEFAULT_MEMCACHED_SETUP
        );

        self::assertInstanceOf(APCU::class, $engine);
    }

    /**
     * @test
     * @throws \chilimatic\lib\Cache\Exception\CacheException
     */
    public function getMemoryInstance(): void
    {
        $engine = CacheFactory::make(
            Memory::class,
            self::DEFAULT_MEMCACHED_SETUP
        );

        self::assertInstanceOf(Memory::class, $engine);
    }


    /**
     * @test
     * @throws \chilimatic\lib\Cache\Exception\CacheException
     * @expectedException \TypeError
     * @expectedExceptionMessageRegExp /must be of the type string, null given/
     */
    public function getNonExistingInstance(): void
    {
        $engine = CacheFactory::make(
            null,
            []
        );

        self::assertNull($engine);
    }


    /**
     * @test
     * @expectedException \chilimatic\lib\Cache\Exception\CacheException
     * @expectedExceptionMessageRegExp /name is empty/
     */
    public function getNonExistingInstanceWithEmptyString(): void
    {
        CacheFactory::make(
            '',
            []
        );
    }

    /**
     * @test
     * @expectedException \chilimatic\lib\Cache\Exception\CacheException
     * @expectedExceptionMessageRegExp /The Cache is not implemented or not installed: asdasasdasd/
     */
    public function getNonExistingInstanceWithString(): void
    {
         CacheFactory::make(
            'asdasasdasd',
            []
        );
    }

    /**
     * @test
     * @expectedException \chilimatic\lib\Cache\Exception\CacheException
     * @expectedExceptionMessageRegExp /The Cache could not establish connection/
     */
    public function getExistingClassThatDoesNotConnectToCache(): void
    {
        $engine = CacheFactory::make(
            Memcached::class,
            []
        );
    }
}