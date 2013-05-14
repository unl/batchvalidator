<?php

require dirname(dirname(__FILE__)) . '/www/config.inc.php';

echo '------------------------------------------'.PHP_EOL;
echo 'INSTALLING...' . PHP_EOL;
echo '------------------------------------------'.PHP_EOL;

function exec_sql($db, $sql, $message, $fail_ok = false)
{
    echo $message.'...'.PHP_EOL;
    
    try {
        $result = true;
        if (!$db->query($sql)) {
            echo "Query Failed: " . implode("; ", $db->errorInfo()) . PHP_EOL;
        }
    } catch (Exception $e) {
        $result = false;
        if (!$fail_ok) {
            echo 'The query failed:' . implode("; ", $result->errorInfo());
            exit();
        }
    }
    echo 'finished.'.PHP_EOL;
    echo '------------------------------------------'.PHP_EOL;
    return $result;
}

$sql = "";

//Force install (delete old data)
if (isset($argv[1]) && $argv[1] == '-f') {
    $sql = "SET FOREIGN_KEY_CHECKS=0;
            DROP TABLE IF EXISTS assessment_runs;
            DROP TABLE IF EXISTS assessment;
            DROP TABLE IF EXISTS url_has_badlinks;
            SET FOREIGN_KEY_CHECKS=1;";
    
    exec_sql($db, $sql, 'Deleting old install');
}

$sql = file_get_contents(dirname(dirname(__FILE__)) . "/data/assessment_runs.sql");
exec_sql($db, $sql, 'adding assessment_runs table');

$sql = file_get_contents(dirname(dirname(__FILE__)) . "/data/assessment.sql");
exec_sql($db, $sql, 'adding assessment table');

$sql = file_get_contents(dirname(dirname(__FILE__)) . "/data/url_has_badlinks.sql");
exec_sql($db, $sql, 'adding url_has_badlinks table');

$sql = file_get_contents(dirname(dirname(__FILE__)) . "/data/scannable.sql");
exec_sql($db, $sql, 'adding scannable to the assessment table');

$sql = file_get_contents(dirname(dirname(__FILE__)) . "/data/queue.sql");
exec_sql($db, $sql, 'adding queue columns');

$sql = file_get_contents(dirname(dirname(__FILE__)) . "/data/queue_statuses.sql");
exec_sql($db, $sql, 'adding queue statuses');

$sql = file_get_contents(dirname(dirname(__FILE__)) . "/data/runs_page_limit.sql");
exec_sql($db, $sql, 'adding page_limit to the assessment_runs table');

$sql = file_get_contents(dirname(dirname(__FILE__)) . "/data/runs_contact_email.sql");
exec_sql($db, $sql, 'adding contact_email to the assessment_runs table');

$sql = file_get_contents(dirname(dirname(__FILE__)) . "/data/primary_nav_count.sql");
exec_sql($db, $sql, 'adding primary_nav_count to the assessment table');

$sql = file_get_contents(dirname(dirname(__FILE__)) . "/data/grid_2006.sql");
exec_sql($db, $sql, 'adding grid_2006 to the assessment table');

$sql = file_get_contents(dirname(dirname(__FILE__)) . "/data/ga_non_async.sql");
exec_sql($db, $sql, 'adding ga_non_asyc to assessment table');

$sql = file_get_contents(dirname(dirname(__FILE__)) . "/data/ga_setallowhash.sql");
exec_sql($db, $sql, 'adding ga_setallowhash to assessment table');

$sql = file_get_contents(dirname(dirname(__FILE__)) . "/data/assessment_site_history.sql");
exec_sql($db, $sql, 'adding assessment_site_history to db');