<?php
declare(strict_types=1);

use chilimatic\lib\Cache\Engine\Adapter\Memory;
use chilimatic\lib\Cache\Engine\ICache;

class MemoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @test
     */
    public function implementsTheCorrectInterface(): void
    {
        $this->assertInstanceOf(ICache::class, new Memory());
    }

    /**
     * @test
     */
    public function setValue(): void
    {
        $cache = new Memory();

        $value = ['12'];
        $cache->set('test', ['12']);

        self::assertEquals($cache->get('test'), $value);
    }

    /**
     * @test
     */
    public function setValueAndUpdateIt(): void
    {
        $cache = new Memory();

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
    public function setValueAndUpdateItWriteCount(): void
    {
        $cache = new Memory();

        $value = ['12'];
        $value2 = ['14'];
        $cache->set('test', $value);
        $cache->set('test', $value2);

        self::assertEquals(2, $cache->getWriteCount());
    }

    /**
     * @test
     */
    public function setValueAndRemoveIt(): void
    {
        $cache = new Memory();

        $value = ['12'];
        $cache->set('test', $value);
        $cache->delete('test');

        self::assertEquals(null, $cache->get('test'));
    }

    /**
     * @test
     */
    public function setValueAndRemoveItRemoveCount(): void
    {
        $cache = new Memory();

        $value = ['12'];
        $cache->set('test', $value);
        $cache->delete('test');

        self::assertEquals(1, $cache->getRemoveCount());
    }

    /**
     * @test
     */
    public function setValueAndGetItReadCount(): void
    {
        $cache = new Memory();

        $value = ['12'];
        $cache->set('test', $value);
        $cache->get('test');
        $cache->get('test');
        $cache->get('test');

        self::assertEquals(3, $cache->getReadCount());
    }
}