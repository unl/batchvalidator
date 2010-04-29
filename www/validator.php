<?php
require_once 'config.inc.php';
if (!isset($_GET['u'])) {
    throw new Exception('You must pass a uri to validate.');
}

$v = new Services_W3C_HTMLValidator();
$result = $v->validate($_GET['u']);

$assessment = new UNL_WDN_Assessment($_GET['base'], $db);
$assessment->setValidationResult($_GET['u'], $result->isValid());

echo json_encode($result);
