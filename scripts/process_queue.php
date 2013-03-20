<?php
require_once dirname(dirname(__FILE__)) . '/www/config.inc.php';

if (!isset($argv[1]) || !in_array($argv[1], array('user', 'auto'))) {
    echo "usage: php process_queue.php user|auto" . PHP_EOL;
    exit();
}

$run_type = $argv[1];

//Only run a max of 5 checks at a time.
$sth = $db->prepare("SELECT count(*) as total FROM assessment_runs WHERE status = 'running' AND run_type = ?");
$sth->execute(array($run_type));
$result = $sth->fetch();

if (!isset($result['total'])) {
    exit();
}

$max = UNL_WDN_Assessment::$maxConcurrentUserJobs;

if ($run_type == 'auto') {
    $max = UNL_WDN_Assessment::$maxConcurrentAutoJobs;
    UNL_WDN_Assessment_LinkChecker::$maxActiveRequests = 5;
}

if ($result['total'] >= $max) {
    exit();
}

//Select a site to queue.
$sth = $db->prepare("SELECT baseurl, page_limit FROM assessment_runs WHERE status = 'queued' AND run_type = ? ORDER BY date_started ASC LIMIT 1");
$sth->execute(array($run_type));
$result = $sth->fetch();

if (!isset($result['baseurl'])) {
    //Nothing queued...
    exit();
}

$assessment = new UNL_WDN_Assessment($result['baseurl'], $db);

//Handle errors
function shutdown($assessment) {
    $error = error_get_last();
    if ($error['type'] === E_ERROR) {
        $assessment->setRunStatus('error');
    }
}

register_shutdown_function('shutdown', $assessment);

//run the check
$assessment->check(null, $result['page_limit']);