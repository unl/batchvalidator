<?php

require_once 'config.inc.php';

$uri = '';

if (isset($_GET['uri'])) {
    $uri = htmlentities($_GET['uri'], ENT_QUOTES);
    $assessment = new UNL_WDN_Assessment($uri, $db);
    if (isset($_GET['revalidate'])) {
        $assessment->reValidate();
    }
}

?>
<h1>Welcome to the batch validator</h1>
<form method="GET" action="">
<input type="text" name="uri" value="<?php echo $uri; ?>" />
Revalidate all? <input type="checkbox" name="revalidate" />
<input type="submit" name="submit" /></form>

<?php
if (isset($assessment)) {
    if ($subPages = $assessment->getSubPages()) {
        echo '<ul>';
        foreach ($subPages as $page) {
            echo '<li class="'.$page['valid'].'">'.$page['url']. ' valid = '.$page['valid'].'</li>';
        }
        echo '</ul>';
    }
}
?>