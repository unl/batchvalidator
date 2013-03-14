<?php
require_once dirname(dirname(__FILE__)) . '/www/config.inc.php';

//Only run a max of 5 checks at a time.
$sth = $db->prepare("SELECT count(*) as total FROM assessment_runs WHERE status = 'running' AND run_type = 'user'");
$sth->execute();
$result = $sth->fetch();

if (!isset($result['total'])) {
    exit();
}

if ($result['total'] >= UNL_WDN_Assessment::$maxConcurrentUserJobs) {
    exit();
}

//Select a site to queue.
$sth = $db->prepare("SELECT baseurl FROM assessment_runs WHERE status = 'queued' AND run_type = 'user' ORDER BY date_started ASC LIMIT 1");
$sth->execute();
$result = $sth->fetch();

if (!isset($result['baseurl'])) {
    //Nothing queued...
    exit();
}

$assessment = new UNL_WDN_Assessment($result['baseurl'], $db);

//run the check
$assessment->check();