<?php
require_once dirname(dirname(__FILE__)) . '/www/config.inc.php';

//Only run a max of 5 checks at a time.
$sth = $db->prepare("select site.baseurl, date_completed from site LEFT JOIN assessment_runs on assessment_runs.baseurl = site.baseurl ORDER BY date_completed ASC LIMIT 5");
$sth->execute();

while ($result = $sth->fetch()) {
    $assessment = new UNL_WDN_Assessment($result['baseurl'], $db);
    $assessment->addRun('auto');
}