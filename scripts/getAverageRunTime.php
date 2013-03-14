<?php
require_once dirname(dirname(__FILE__)) . '/www/config.inc.php';

//Only run a max of 5 checks at a time.
$sth = $db->prepare("select avg(timestampdiff(MINUTE, date_started, date_completed)) as average_time from assessment_runs;");
$sth->execute();
$result = $sth->fetch();

if (!isset($result['average_time'])) {
    echo "unknown" . PHP_EOL;
    exit();
}

echo $result['average_time'] . " min" . PHP_EOL;