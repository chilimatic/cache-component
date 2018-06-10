<?php
declare(strict_types=1);

use chilimatic\lib\Cache\Engine\Adapter\APC;
use chilimatic\lib\Cache\Engine\ICache;

class APCTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     * @throws \chilimatic\lib\Cache\Exception\CacheException
     */
    public function implementsTheCorrectInterface(): void
    {
        $this->assertInstanceOf(ICache::class, new APC());
    }

    /**
     * @test
     * @throws \chilimatic\lib\Cache\Exception\CacheException
     */
    public function isConnected(): void
    {
        $cache = new APC();
        self::assertTrue($cache->isConnected());
    }
    
    /**
     * @test
     * @throws \chilimatic\lib\Cache\Exception\CacheException
     */
    public function setValue(): void
    {
        $cache = new APC();

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
        $cache = new APC();

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
        $cache = new APC();

        $value = ['12'];
        $cache->set('test', $value);
        $cache->delete('test');

        self::assertEquals(null, $cache->get('test'));
    }
}