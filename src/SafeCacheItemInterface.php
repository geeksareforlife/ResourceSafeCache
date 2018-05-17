<?php

namespace GeeksAreForLife\SafeCache;

use Psr\Cache\CacheItemInterface;

interface SafeCacheItemInterface extends CacheItemInterface
{
    /**
     * Retrieves the value of the item from the backup cache
     *
     * Use this if isHit() returns false to retrieve the most recently, but now expired, value.
     * This will throw an ItemNotCachedException if there is no value available
     *
     * @throws ItemNotCachedException
     *   If the backup value is not available (i.e. the value was never cached),
     *   this exception will be thrown
     *
     * @return mixed
     *   The value corresponding to this cache item's key, or null if not found.
     */
    public function getMostRecent();
}