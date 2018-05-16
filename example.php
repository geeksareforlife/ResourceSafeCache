<?php
/**
 * Run this from the command line:
 *
 * php example.php
 */

use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use GeeksAreForLife\ResourceSafeCache\PSR6ResourceSafeCache;

require('vendor/autoload.php');

// The PSR6 implementation needs a cache passed to it
// If you can, use dependancy injection on the
// GeeksAreForLife\ResourceSafeCache\ResourceSafeCache
// interface
$cache = new FilesystemAdapter();
$rsc = new PSR6ResourceSafeCache($cache);

// for the purposes of this example, clear the cache
$cache->clear();

// the first time we ask for a resource it will need to go and get it
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
echo("Took " . number_format($time, 10) . " seconds\n");