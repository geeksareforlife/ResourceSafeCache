<?php

namespace GeeksAreForLife\SafeCache;

use Psr\Cache\CacheItemInterface;

final class SafeCacheItem implements SafeCacheItemInterface
{
    private $primary;

    private $backup;

    function __construct(CacheItemInterface $primary, CacheItemInterface $backup)
    {
        $this->primary = $primary;

        $this->backup = $backup;
    }

    /**
     * Returns the key for the current cache item.
     *
     * The key is loaded by the Implementing Library, but should be available to
     * the higher level callers when needed.
     *
     * @return string
     *   The key string for this cache item.
     */
    public function getKey()
    {
        return $this->primary->getKey();
    }

    /**
     * Retrieves the value of the item from the cache associated with this object's key.
     *
     * The value returned must be identical to the value originally stored by set().
     *
     * If isHit() returns false, this method MUST return null. Note that null
     * is a legitimate cached value, so the isHit() method SHOULD be used to
     * differentiate between "null value was found" and "no value was found."
     *
     * @return mixed
     *   The value corresponding to this cache item's key, or null if not found.
     */
    public function get()
    {
        return $this->primary->get();
    }

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
    public function getMostRecent()
    {
        $value = $this->backup->get();
        if ($this->backup->isHit()) {
            return $value;
        } else {
            throw new ItemNotCachedException(sprintf('Item "%s" has no backup in the cache', $this->getKey()));
        }
    }

    /**
     * Confirms if the cache item lookup resulted in a cache hit.
     *
     * Note: This method MUST NOT have a race condition between calling isHit()
     * and calling get().
     *
     * @return bool
     *   True if the request resulted in a cache hit. False otherwise.
     */
    public function isHit()
    {
        return $this->primary->isHit();
    }

    /**
     * Sets the value represented by this cache item.
     *
     * The $value argument may be any item that can be serialized by PHP,
     * although the method of serialization is left up to the Implementing
     * Library.
     *
     * @param mixed $value
     *   The serializable value to be stored.
     *
     * @return static
     *   The invoked object.
     */
    public function set($value)
    {
        $this->primary->set($value);
        $this->backup->set($value);

        return $this;
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param \DateTimeInterface|null $expiration
     *   The point in time after which the item MUST be considered expired.
     *   If null is passed explicitly, a default value MAY be used. If none is set,
     *   the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     */
    public function expiresAt($expiration)
    {
        $this->primary->expiresAt($expiration);

        return $this;
    }

    /**
     * Sets the expiration time for this cache item.
     *
     * @param int|\DateInterval|null $time
     *   The period of time from the present after which the item MUST be considered
     *   expired. An integer parameter is understood to be the time in seconds until
     *   expiration. If null is passed explicitly, a default value MAY be used.
     *   If none is set, the value should be stored permanently or for as long as the
     *   implementation allows.
     *
     * @return static
     *   The called object.
     */
    public function expiresAfter($time)
    {
        $this->primary->expiresAfter($time);

        return $this;
    }

    public function getPrimary()
    {
        return $this->primary;
    }

    public function getBackup()
    {
        return $this->backup;
    }
}