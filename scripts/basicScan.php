<?php

require dirname(dirname(__FILE__)) . '/www/config.inc.php';

if (!isset($argv[1])) {
    echo "This script will scan an entire site and only gather basic stats about the site as a whole, such as page count" . PHP_EOL;
    echo "usage: php basicScan.php url" . PHP_EOL;
    exit();
}

echo "Scanning '" . $argv[1] . "'..." . PHP_EOL;

$scanner = new UNL_WDN_BasicScan($argv[1]);
$scanner->scan();
$scanner->logStats();

echo "Finished Scanning '" . $argv[1] . "'..." . PHP_EOL;

echo "--- STATS FOR '" . $argv[1] . "' ---" . PHP_EOL;

echo "Total Pages: " . count($scanner->pages) . PHP_EOL;