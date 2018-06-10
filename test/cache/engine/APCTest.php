<?php
declare(strict_types=1);

use chilimatic\lib\Cache\Engine\Adapter\APCU;
use chilimatic\lib\Cache\Engine\ICache;

class APCTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     * @throws \chilimatic\lib\Cache\Exception\CacheException
     */
    public function implementsTheCorrectInterface(): void
    {
        $this->assertInstanceOf(ICache::class, new APCU());
    }

    /**
     * @test
     * @throws \chilimatic\lib\Cache\Exception\CacheException
     */
    public function isConnected(): void
    {
        $cache = new APCU();
        self::assertTrue($cache->isConnected());
    }
    
    /**
     * @test
     * @throws \chilimatic\lib\Cache\Exception\CacheException
     */
    public function setValue(): void
    {
        $cache = new APCU();

        $value = ['12'];
        $cache->set('test', ['12']);

        self::assertEquals($cache->get('test'), $value);
    }

    /**
     * @test
     * @throws \chilimatic\lib\Cache\Exception\CacheException
     */
    public function setValueAndUpdateIt(): void
    {
        $cache = new APCU();

        $value = ['12'];
        $value2 = ['14'];
        $cache->set('test', $value);

        self::assertEquals($cache->get('test'), $value);

        $cache->set('test', $value2);

        self::assertEquals($cache->get('test'), $value2);
    }


    /**
     * @test
     * @throws \chilimatic\lib\Cache\Exception\CacheException
     */
    public function setValueAndRemoveIt(): void
    {
        $cache = new APCU();

        $value = ['12'];
        $cache->set('test', $value);
        $cache->delete('test');

        self::assertEquals(null, $cache->get('test'));
    }
}