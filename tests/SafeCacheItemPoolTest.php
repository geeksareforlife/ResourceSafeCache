<?php

use PHPUnit\Framework\TestCase;
use GeeksAreForLife\SafeCache\SafeCacheItemPool;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

class SafeCacheItemPoolTest extends TestCase
{
    protected $safeCache;

    public function setUp()
    {
        // use symfony's in-memory cache
        $cache = new ArrayAdapter();
        $cache->clear();
        $this->safeCache = new SafeCacheItemPool($cache);
    }

    public function testGetSafeCacheItem()
    {
        $item = $this->safeCache->getItem("test");
        $this->assertTrue(is_a($item, 'GeeksAreForLife\SafeCache\SafeCacheItem'));
    }
}