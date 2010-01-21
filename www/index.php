<?php

require_once 'config.inc.php';

$uri = '';

if (isset($_GET['uri'])) {
    $uri = htmlentities($_GET['uri'], ENT_QUOTES);
    $assessment = new UNL_WDN_Assessment($uri, $db);
    $assessment->removeEntries();
    $assessment->runValidation();
}

?>
<h1>Welcome to the batch validator</h1>
<form method="GET" action="">
<input type="text" name="uri" value="<?php echo $uri; ?>" />
<input type="submit" name="submit" /></form>
