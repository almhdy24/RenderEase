<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Almhdy\RenderEase\RenderEase;

// Initialize the renderer
$renderer = new RenderEase();

// Test 1: Try to enable caching without setting directory (should throw exception)
echo "=== Testing Cache Validation ===\n";

// Test the cache manager directly to avoid render() recursion
try {
    $cacheManager = $renderer->getCacheManager();
    $cacheManager->enable(true);
    $renderer->setCacheDirectory('../cache');
    $cacheManager->validateCacheSetup(); // This should throw exception
    echo "ERROR: Should have thrown exception!\n";
} catch (Exception $e) {
    echo "✓ Correctly caught exception: " . $e->getMessage() . "\n";
}

// Test 2: Proper usage with cache directory
echo "\n=== Testing Proper Caching ===\n";
$renderer->setCacheDirectory(__DIR__ . '/../cache/');
$renderer->enableCaching(true);

// Set variables
$renderer->set("title", "Welcome Page");
$renderer->setMultiple([
    "username" => "John",
    "message" => "This is your dashboard.",
    "items" => ["Item 1", "Item 2", "Item 3"],
    "current_date" => date('Y-m-d H:i:s')
]);

$start = microtime(true);
$output = $renderer->render("welcome");
$time1 = microtime(true) - $start;
echo $output . "\n";
echo "First render time: " . round($time1 * 1000, 2) . "ms\n";

// Test cached render
echo "\n=== Testing Cached Render ===\n";
$start = microtime(true);
$output2 = $renderer->render("welcome");
$time2 = microtime(true) - $start;
echo "Cached render time: " . round($time2 * 1000, 2) . "ms\n";
echo "Performance improvement: " . round($time1 / $time2, 1) . "x faster\n";