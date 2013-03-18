<?php

require dirname(dirname(__FILE__)) . '/www/config.inc.php';

if (!isset($argv[1]) || !isset($argv[2])) {
    echo "usage: php sendTestEmail.php http://reportsite.unl.edu/ youremail@unl.edu" . PHP_EOL;
    exit();
}

$assessment = new UNL_WDN_Assessment($argv[1], $db);

$assessment->emailStats($argv[2]);