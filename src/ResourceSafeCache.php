<?php

namespace GeeksAreForLife\ResourceSafeCache;

interface ResourceSafeCache
{
    public function getResource($url, $bypassCache = false);

    public function load($urls);

    public function seed($resources);
}