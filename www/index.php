<?php

require_once 'config.inc.php';

$uri = '';
if (isset($_GET['uri'])
    && preg_match('/https?:\/\//', $_GET['uri'])) {
    $uri = htmlentities($_GET['uri'], ENT_QUOTES);
}
?>
<h1>Welcome to the new batch validator</h1>
<form method="get" action="">
        <fieldset>
            <legend>Sample Form Content</legend>
            <ol>
                <li><label for="name" class="element"><span class="required">*</span>URL</label><div class="element"><input type="text" name="uri" value="<?php echo $uri; ?>" size="80" /></div></li>
                <li><label class="element">Revalidate all?</label><div class="element"><input type="checkbox" name="revalidate" /></div></li>
                <li><label class="element">Revalidate invalid?</label><div class="element"><input type="checkbox" name="invalid" /></div></li>
                <li><label class="element">Rescan links?</label><div class="element"><input type="checkbox" name="rescan" /></div></li>
            </ol>
        </fieldset>
    <input type="submit" name="submit" />
</form>
<pre>
<?php 
if (!empty($uri)) {
    $assessment = new UNL_WDN_Assessment($uri, $db);
    if (isset($_GET['revalidate'])) {
        $assessment->reValidate();
    } elseif (isset($_GET['invalid'])) {
        $assessment->checkInvalid();
    } else {
        if (isset($_GET['rescan'])) {
            //$assessment->removeEntries();
        }
        $assessment->logPages();
    }
}

//if (isset($assessment)) {
//    if ($subPages = $assessment->getSubPages()) {
//        echo '<ul>';
//        foreach ($subPages as $page) {
//            echo '<li class="'.$page['valid'].'">'.$page['url']. ' valid = '.$page['valid'].'</li>';
//        }
//        echo '</ul>';
//    }
//}
?>