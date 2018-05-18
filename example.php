<?php
/**
 * Run this from the command line:
 *
 * php example.php
 */

use GeeksAreForLife\SafeCache\SafeCacheItemPool;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

require('vendor/autoload.php');



// The PSR6 implementation needs a cache passed to it, using Symfony's cache here
// If you can, use dependancy injection on the
// GeeksAreForLife\SafeCache\SafeCache
// interface
$cache = new FilesystemAdapter();
$rsc = new SafeCacheItemPool($cache);

// for the purposes of this example, clear the cache
$cache->clear();

$item = $rsc->getItem("test1");
$item->set("Hello World");
$rsc->save($item);

for ($i = 0; $i < 4; $i++) {
    $test = $rsc->getItem("test1");
    $value = $test->get();
    if ($test->isHit()) {
        var_dump($value);
    } else {
        try {
            var_dump($test->getMostRecent());
        } catch (Psr\Cache\CacheException $e) {
            var_dump($e->getMessage());
        }
    }

    unset($test);
    sleep(30);
}

/*// the first time we ask for a resource it will need to go and get it
$start_time = microtime(true);
echo($rsc->getResource('https://www.geeksareforlife.com/test/cache-test.txt') . "\n");
$time = microtime(true) - $start_time;
echo("Took " . number_format($time, 10) . " seconds\n");

// the next time, we get it from the cache
$start_time = microtime(true);
echo($rsc->getResource('https://www.geeksareforlife.com/test/cache-test.txt') . "\n");
$time = microtime(true) - $start_time;
echo("Took " . number_format($time, 10) . " seconds\n");

// let's clear the cache again
$cache->clear();

// we can preload resources we will need later on
$urls = [
    'https://www.geeksareforlife.com/test/cache-test.txt',
];

$rsc->load($urls);

$start_time = microtime(true);
echo($rsc->getResource('https://www.geeksareforlife.com/test/cache-test.txt') . "\n");
$time = microtime(true) - $start_time;
echo("Took " . number_format($time, 10) . " seconds\n");


// we can even preseed resources if the app starts offline for some reason

$resources = [
    [
        'url'  => 'https://www.geeksareforlife.com/test/cache-404.txt',
        'body' => 'This would be a 404 error',
    ],
];

$rsc->seed($resources);

$start_time = microtime(true);
echo($rsc->getResource('https://www.geeksareforlife.com/test/cache-404.txt') . "\n");
$time = microtime(true) - $start_time;
echo("Took " . number_format($time, 10) . " seconds\n");

// now wait until the cache expires (by default, 60 seconds)
echo("WAITING until cache expires\n");
sleep(60);
echo("RESUMING\n");

// this will now be re-fetched from the live resource
$start_time = microtime(true);
echo($rsc->getResource('https://www.geeksareforlife.com/test/cache-test.txt') . "\n");
$time = microtime(true) - $start_time;
echo("Took " . number_format($time, 10) . " seconds\n");

// as this is offline, it is fetched from the perm cache
$start_time = microtime(true);
echo($rsc->getResource('https://www.geeksareforlife.com/test/cache-404.txt') . "\n");
$time = microtime(true) - $start_time;
echo("Took " . number_format($time, 10) . " seconds\n");*/