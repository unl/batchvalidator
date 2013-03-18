<?php

require_once 'config.inc.php';

header("Content-Type: application/json");

if (!isset($_GET['uri'])) {
    throw new Exception("You must pass the base uri &uri=", 400);
}

$_GET['uri'] = trim($_GET['uri']);

$page = null;
if (isset($_GET['page'])) {
    $page = trim($_GET['page']);
}

$assessment = new UNL_WDN_Assessment($_GET['uri'], $db);

//Allow rechecking
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'check':
            if ($page == null) {
                //Add a run
                $assessment->addRun();
            } else {
                $assessment->check($page);
            }
            break;
        case 'contact_email':
            if (!isset($_POST['email'])) {
                throw new Exception('No Email Provided', 400);
            }
            
            $assessment->setRunContactEmail($_POST['email']);
            break;
    }
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
        
        //Make sure it was actually checked:
        if ($result->uri !== null) {
            $logger = new UNL_WDN_Assessment_HTMLValidationLogger($assessment);
            $logger->setValidationResult($_GET['page'], count($result->errors));
        }
        
        $json = json_encode($result);
        
        break;
    case "stats":
    default:
        $json = $assessment->getJSONstats($page);
}

//Always display results
echo $json;