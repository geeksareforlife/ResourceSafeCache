<?php

namespace GeeksAreForLife\SafeCache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface as Cache;

final class SafeCacheItemPool implements Cache
{
    
    private $cache;

    private $defaultExpiration;

    public function __construct(Cache $cache, int $defaultExpiration = 60)
    {
        $this->cache = $cache;

        $this->defaultExpiration = $defaultExpiration;
    }

    /**
     * Returns a Cache Item representing the specified key.
     *
     * This method must always return a CacheItemInterface object, even in case of
     * a cache miss. It MUST NOT return null.
     *
     * @param string $key
     *   The key for which to return the corresponding Cache Item.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return SafeCacheItem
     *   The corresponding Cache Item.
     */
    public function getItem($key)
    {
        // get the item from the underlying cache
        $item = $this->cache->getItem($key);

        // if this is a new item, we want to pre-set the expiration
        if (!$item->isHit()) {
            $item->expiresAfter($this->defaultExpiration);
        }

        // now get the backup item
        $backup = $this->cache->getItem($this->getBackupKey($key));

        // return a SafeCacheItem
        return new SafeCacheItem($item, $backup);
    }

    /**
     * Returns a traversable set of cache items.
     *
     * @param string[] $keys
     *   An indexed array of keys of items to retrieve.
     *
     * @throws InvalidArgumentException
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return array|\Traversable
     *   A traversable collection of Cache Items keyed by the cache keys of
     *   each item. A Cache item will be returned for each key, even if that
     *   key is not found. However, if no keys are specified then an empty
     *   traversable MUST be returned instead.
     */
    public function getItems(array $keys = array())
    {

    }

    /**
     * Confirms if the cache contains specified cache item.
     *
     * @param string $key
     *   The key for which to check existence.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if item exists in the cache, false otherwise.
     */
    public function hasItem($key)
    {
        return $this->cache->hasItem($key);
    }

    /**
     * Deletes all items in the pool
     *
     * @return bool
     *   True if the pool was successfully cleared. False if there was an error.
     */
    public function clear()
    {
        return $this->cache->clear();
    }

    /**
     * Removes the item from the pool
     *
     * @param string $key
     *   The key to delete.
     *
     * @throws InvalidArgumentException
     *   If the $key string is not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if the item was successfully removed. False if there was an error.
     */
    public function deleteItem($key)
    {
        $itemReturn = $this->cache->deleteItem($key);
        $backupReturn = $this->cache->deleteItem($this->getBackupKey($key));

        if ($itemReturn && $backupReturn) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Removes multiple items from the pool.
     *
     * @param string[] $keys
     *   An array of keys that should be removed from the pool.

     * @throws InvalidArgumentException
     *   If any of the keys in $keys are not a legal value a \Psr\Cache\InvalidArgumentException
     *   MUST be thrown.
     *
     * @return bool
     *   True if the items were successfully removed. False if there was an error.
     */
    public function deleteItems(array $keys)
    {
        $itemReturn = $this->cache->deleteItems($keys);
        $backupReturn = $this->cache->deleteItems($this->getBackupKeys($keys));

        if ($itemReturn && $backupReturn) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Persists a cache item immediately.
     *
     * @param SafeCacheItemInterface $item
     *   The cache item to save.
     *
     * @return bool
     *   True if the item was successfully persisted. False if there was an error.
     */
    public function save(CacheItemInterface $item)
    {

    }

    /**
     * Sets a cache item to be persisted later.
     *
     * @param SafeCacheItemInterface $item
     *   The cache item to save.
     *
     * @return bool
     *   False if the item could not be queued or if a commit was attempted and failed. True otherwise.
     */
    public function saveDeferred(CacheItemInterface $item)
    {

    }

    /**
     * Persists any deferred cache items.
     *
     * @return bool
     *   True if all not-yet-saved items were successfully saved or there were none. False otherwise.
     */
    public function commit()
    {

    }

    private function getBackupKey($key)
    {
        // there is a chance of collisions here...
        return "SafeCache." . $key;
    }

    private function getBackupKeys($keys)
    {
        $backupKeys = [];
        foreach ($keys as $key) {
            $backupKeys[] = $this->getBackupKey($key);
        }

        return $backupKeys;
    }
}