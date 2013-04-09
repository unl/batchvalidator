<?php
require_once dirname(dirname(__FILE__)) . '/www/config.inc.php';

//Update the aggregate cache
$aggregate = new UNL_WDN_Aggregate($db);
$aggregate->updateCache();