<?php
require_once dirname(dirname(__FILE__)) . '/www/config.inc.php';

/**
 * This script will fix queue errors by expiring long-running jobs.
 * 
 * Jobs may become long-running if the processes is killed prematurly.
 */

//Only run a max of 5 checks at a time.
$sql = "UPDATE assessment_runs SET status = 'error' and date_completed = NOW() WHERE timestampdiff(SECOND, date_started, now()) > ? AND status = 'running';";
$sth = $db->prepare($sql);
$sth->execute(array(UNL_WDN_Assessment::$timeout));

echo $sth->rowCount() . " records updated." . PHP_EOL;