<?php

require_once 'config.inc.php';

header("Content-Type: application/json");

if (!isset($_GET['uri'])) {
    throw new Exception("You must pass the base uri &uri=", 400);
}

$assessment = new UNL_WDN_Assessment($_GET['uri'], $db);

//Allow rechecking
if (isset($_POST['action']) && $_POST['action'] == 'check') {
    $assessment->check();
}

$action = "stats";

if (isset($_GET['action'])) {
    $action = $_GET['action'];
}

$json = "";

switch ($action)
{
    case "html_errors":
        if (!isset($_GET['page'])) {
            throw new Exception("You must pass the page uri &page=", 400);
        }

        $v = new Services_W3C_HTMLValidator();
        $v->validator_uri = UNL_WDN_Assessment::$htmlValidatorURI;
        
        $result = $v->validate($_GET['page']);
        
        $logger = new UNL_WDN_Assessment_ValidationLogger($assessment);
        $logger->setValidationResult($_GET['page'], count($result->errors));
        
        $json = json_encode($result);
        
        break;
    case "stats":
    default:
        $json = $assessment->getJSONstats();
}

//Always display results
echo $json;