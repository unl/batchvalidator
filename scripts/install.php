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
            DROP TABLE IF EXISTS assessment;
            DROP TABLE IF EXISTS url_has_badlinks;
            SET FOREIGN_KEY_CHECKS=1;";
    
    exec_sql($db, $sql, 'Deleting old install');
}

$sql = file_get_contents(dirname(dirname(__FILE__)) . "/data/assessment.sql");
exec_sql($db, $sql, 'adding assessment table');

$sql = file_get_contents(dirname(dirname(__FILE__)) . "/data/url_has_badlinks.sql");
exec_sql($db, $sql, 'adding url_has_badlinks table');