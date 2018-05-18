<?php

namespace GeeksAreForLife\SafeCache;

use Psr\Cache\CacheException;

class ItemNotCachedException extends \Exception implements CacheException
{
}
