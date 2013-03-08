<?php

require_once 'config.inc.php';

header("Content-Type: application/json");

if (!isset($_GET['uri'])) {
    throw new Exception("You must pass the uri", 400);
}

$assessment = new UNL_WDN_Assessment($_GET['uri'], $db);

//Allow rechecking
if (isset($_POST['action']) && $_POST['action'] == 'check') {
    $assessment->check();
}

//Always display results
echo $assessment->getJSONstats();