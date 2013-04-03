<?php
require_once dirname(dirname(__FILE__)) . '/www/config.inc.php';

$sth = $db->prepare("select count(*) as total from assessment_runs WHERE run_type='auto' AND status='queued'");
$sth->execute();

$result = $sth->fetch();

if (isset($result['total']) && $result['total'] >= UNL_WDN_Assessment::$maxQueuedAutoJobs) {
    exit();
}

$limit = 0;

if (isset($result['total'])) {
    $limit = UNL_WDN_Assessment::$maxQueuedAutoJobs - $result['total'];
}

//Only run a max of 10 checks at a time.
$sth = $db->prepare("select site.baseurl, date_completed from site LEFT JOIN assessment_runs on assessment_runs.baseurl = site.baseurl WHERE assessment_runs.status NOT IN ('running', 'queued', 'restricted') ORDER BY date_completed ASC LIMIT " . (int)$limit);
$sth->execute();

while ($result = $sth->fetch()) {
    $assessment = new UNL_WDN_Assessment($result['baseurl'], $db);
    $assessment->addRun('auto');
}