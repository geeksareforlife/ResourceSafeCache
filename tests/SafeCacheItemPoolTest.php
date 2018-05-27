<?php

use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use GeeksAreForLife\SafeCache\SafeCacheItemPool;

class SafeCacheItemPoolTest extends TestCase
{
    protected $safeCache;

    protected $backupPrefix = 'SafeCacheNoExpire.';

    public function getEmptyCacheMock($itemCallback)
    {
        $cache = $this->createMock(CacheItemPoolInterface::class);

        $cache->method('getItem')
              ->will($this->returnCallback($itemCallback));

        return $cache;
    }

    public function getCacheItem($key, $existing = false)
    {
        $item = $this->createMock(CacheItemInterface::class);

        $item->method('getKey')
             ->will($this->returnValue($key));

        $item->method('isHit')
             ->will($this->returnValue($existing));

        return $item;
    }

    public function testGetNewSafeCacheItem()
    {
        // set up our mock cache pool and items for this test
        $itemMock = function($key) {
            $item = $this->getCacheItem($key, false);

            // check we are doing what we expect
            // pretty hacky though!
            if (strpos($key, $this->backupPrefix) === false) {
                $item->expects($this->once())
                     ->method('expiresAfter')
                     ->with($this->greaterThan(0));
            }

            return $item;
        };

        $cache = $this->getEmptyCacheMock($itemMock);

        $cache->expects($this->exactly(2))
              ->method('getItem')
              ->withConsecutive(
                ['test'],
                [$this->backupPrefix . 'test']
              );

        $safeCache = new SafeCacheItemPool($cache);

        $item = $safeCache->getItem("test");
        $this->assertTrue(is_a($item, 'GeeksAreForLife\SafeCache\SafeCacheItem'));
        $this->assertSame($item->getPrimary()->getKey(), 'test');
        $this->assertSame($item->getBackup()->getKey(), $this->backupPrefix . 'test');
    }
}