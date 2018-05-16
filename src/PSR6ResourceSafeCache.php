<?php

namespace GeeksAreForLife\ResourceSafeCache;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Psr\Cache\CacheItemPoolInterface as Cache;

final class PSR6ResourceSafeCache
{
    
    private $cache;

    private $client = "";

    private $expiration = 60;

    public function __construct(Cache $cache)
    {
        $this->cache = $cache;
    }

    public function getResource($url, $bypassCache = false)
    {
        if (!$bypassCache) {
            // try to get the resource from the timeout cache
            $key = $this->getTimeoutKey($url);

            $resourceItem = $this->cache->getItem($key);

            if ($resourceItem->isHit()) {
                return $resourceItem->get();
            }
        }

        // try to retrieve the live resource
        try {
            $response = $this->getClient()->request('GET', $url);
            $body = $response->getBody()->getContents();
            $this->storeResource($url, $body);
            return $body;
        } catch (TransferException $e) {
            // failed to get the live resource for some reason
            // check the backup cache
            $key = $this->getPermKey($url);
            $resourceItem = $this->cache->getItem($key);

            if ($resourceItem->isHit()) {
                return $resourceItem->get();
            } else {
                // throw an exception
                var_dump("exception");
            }
        }
    }

    public function load($urls)
    {
        foreach ($urls as $url) {
            try {
                $response = $this->getClient()->request('GET', $url);
                $body = $response->getBody()->getContents();
                $this->storeResource($url, $body);
            } catch (TransferException $e) {
                // swallow any exception here??
            }
        }
    }

    public function seed($resources)
    {
        foreach ($resources as $resource) {
            if (!isset($resource['url'], $resource['body'])) {
                // throw exception
            }
            $this->storeResource($resource['url'], $resource['body']);
        }
    }

    private function storeResource($url, $body)
    {
        $timeoutKey = $this->getTimeoutKey($url);
        $permKey = $this->getPermKey($url);

        $timeoutItem = $this->cache->getItem($timeoutKey);
        $permItem = $this->cache->getItem($permKey);

        $timeoutItem->set($body);
        $permItem->set($body);

        $timeoutItem->expiresAfter($this->expiration);

        $this->cache->save($timeoutItem);
        $this->cache->save($permItem);
    }

    private function getClient()
    {
        if ($this->client === "") {
            $this->buildClient();
        }

        return $this->client;
    }

    private function buildClient()
    {
        $this->client = new Client([
            "timeout"       => 10,
            "http_errors"   => true,
            "synchronous"   => true
        ]);
    }

    private function getTimeoutKey($url)
    {
        return hash('sha256', $url);
    }

    private function getPermKey($url)
    {
        return "perm." . $this->getTimeoutKey($url);
    }
}