<?php

require_once 'config.inc.php';

$uri = '';
if (isset($_GET['uri'])
    && preg_match('/https?:\/\//', $_GET['uri'])
    && filter_var($_GET['uri'], FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
    $uri = htmlentities($_GET['uri'], ENT_QUOTES);
    $ch = curl_init($uri);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_LOW_SPEED_LIMIT, 10);
    curl_setopt($ch, CURLOPT_LOW_SPEED_TIME, 5);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_exec($ch);
    $new_uri = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    if ($new_uri !== $uri) {
        header('Location: ?uri='.urlencode($new_uri));
        exit();
    }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en"><!-- InstanceBegin template="/Templates/php.fixed.dwt.php" codeOutsideHTMLIsLocked="false" -->
<head>
<!--
    Membership and regular participation in the UNL Web Developer Network
    is required to use the UNL templates. Visit the WDN site at 
    http://wdn.unl.edu/. Click the WDN Registry link to log in and
    register your unl.edu site.
    All UNL template code is the property of the UNL Web Developer Network.
    The code seen in a source code view is not, and may not be used as, a 
    template. You may not use this code, a reverse-engineered version of 
    this code, or its associated visual presentation in whole or in part to
    create a derivative work.
    This message may not be removed from any pages based on the UNL site template.
    
    $Id: php.fixed.dwt.php 536 2009-07-23 15:47:30Z bbieber2 $
-->
<link rel="stylesheet" type="text/css" media="screen" href="/wdn/templates_3.0/css/all.css" />
<link rel="stylesheet" type="text/css" media="print" href="/wdn/templates_3.0/css/print.css" />
<script type="text/javascript" src="/wdn/templates_3.0/scripts/all.js"></script>
<?php virtual('/wdn/templates_3.0/includes/browserspecifics.html'); ?>
<?php virtual('/wdn/templates_3.0/includes/metanfavico.html'); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>UNL | WDN | Batch Validator</title>
<!-- InstanceEndEditable --><!-- InstanceBeginEditable name="head" -->
<link rel="stylesheet" type="text/css" href="/wdn/templates_3.0/css/content/zenform.css" />
<link rel="stylesheet" type="text/css" href="css/batchval.css" />
<script type="text/javascript" src="js/batchval.js"></script>
<script type="text/javascript">var baseURI = '<?php echo $uri; ?>';</script>
<!-- InstanceEndEditable -->
</head>
<body class="fixed">
<p class="skipnav"> <a class="skipnav" href="#maincontent">Skip Navigation</a> </p>
<div id="wdn_wrapper">
    <div id="header"> <a href="http://www.unl.edu/" title="UNL website"><img src="/wdn/templates_3.0/images/logo.png" alt="UNL graphic identifier" id="logo" /></a>
        <h1>University of Nebraska&ndash;Lincoln</h1>
        <?php virtual('/wdn/templates_3.0/includes/wdnTools.html'); ?>
    </div>
    <div id="wdn_navigation_bar">
        <div id="breadcrumbs">
            <!-- WDN: see glossary item 'breadcrumbs' -->
            <!-- InstanceBeginEditable name="breadcrumbs" -->
            <ul>
                <li class="first"><a href="http://www.unl.edu/">UNL</a></li>
                <li><a href="http://wdn.unl.edu/"><acronym title="Web Developer Network">WDN</acronym></a></li>
                <li>Batch Validator</li>
            </ul>
            <!-- InstanceEndEditable --></div>
        <div id="wdn_navigation_wrapper">
            <div id="navigation"><!-- InstanceBeginEditable name="navlinks" -->
                <?php echo file_get_contents('http://www1.unl.edu/wdn/templates_3.0/scripts/navigationSniffer.php?u=http://wdn.unl.edu/'); ?>
                <!-- InstanceEndEditable --></div>
        </div>
    </div>
    <div id="wdn_content_wrapper">
        <div id="titlegraphic"><!-- InstanceBeginEditable name="titlegraphic" -->
            <h1>Batch Validator</h1>
            <!-- InstanceEndEditable --></div>
        <div id="pagetitle"><!-- InstanceBeginEditable name="pagetitle" --> <!-- InstanceEndEditable --></div>
        <div id="maincontent">
            <!--THIS IS THE MAIN CONTENT AREA; WDN: see glossary item 'main content area' -->
            <!-- InstanceBeginEditable name="maincontentarea" -->
            <form method="get" action="" class="zenform primary" style="width:930px;margin-top:10px;">
                    <fieldset>
                        <legend>Submit your site for validation</legend>
                        <ol>
                            <li>
                            	<label for="name" class="element">
                            		<span class="required">*</span>
                            		URL
                            	</label>
                            	<input type="text" name="uri" value="<?php echo $uri; ?>" size="80" />
                            </li>
                            <!-- 
                            <li>
	                        	<fieldset>
	                       		<legend>What should be validated?</legend>
		                        	<ol>
		                        	 <li>
		                        		<input id="action_all" type="radio" name="action" value="revalidate" />
		                        		<label for="action_all">All pages</label>
		                        	 </li>
		                        	 <li>
                            		<input id="action_invalid" type="radio" name="action" value="invalid" />
                            		<label for="action_invalid">Only pages previously identified as invalid <span class="helper">Based on a prior batch validation scan</span></label>
                            		 </li>
                            		<li>
                            		<input id="action_links" type="radio" name="action" value="rescan" />
                            		<label for="action_links">Rescan links <span class="helper">What does this do?</span></label>
                            		 </li>
                            		<li>
                            		<input id="action_external" type="radio" name="action" value="linkcheck" />
                            		<label for="action_external">Check external links <span class="helper">What does this do?</span></label>
                            		 </li>
	                        	  </ol>
	                        	</fieldset>
	                        </li>
	                        -->
                        </ol>
                    </fieldset>
                    <a href="#" id="submitTest">Test</a>
                    <input type="submit" id="submit" name="submit" value="Submit" />
            </form>
            <h3 id="summaryTitle" class="sec_header">Summary of Scan <span>Revalidate: <a href="#" id="validateInvalid" onclick="validateInvalid(); return false">Invalid Pages</a> | <a href="#" id="validateAll" onclick="validateAll(); return false">All Pages</a></span></h3>
            <div class="clear" id="summaryResults">
                <?php
                
                if (!empty($uri)) {
                    $parts = parse_url($uri);
                    if (!isset($parts['path'])) {
                        echo '<h2>tsk tsk. A trailing slash is always required. Didn\'t saltybeagle ever teach you what a web address is?</h2>';
                        unset($uri);
                    }
                }
                
                if (!empty($uri)) {
                    $assessment = new UNL_WDN_Assessment($uri, $db);
                    $action = 'rescan';
                    
                    if (isset($_GET['action'])) {
                        $action = $_GET['action'];
                    }
                    
                    switch ($action) {
                        case 'revalidate':
                            $assessment->reValidate();
                            break;
                        case 'invalid':
                            $assessment->checkInvalid();
                            break;
                        case 'linkcheck':
                            $assessment->checkLinks();
                            break;
                        case 'remove':
                            $assessment->removeEntries();
                        case 'rescan':
                        default:
                            $assessment->logPages();
                    }
                }
                ?>
            </div>
            <div id="progressReport">
	           <h3>We're churning through your site now</h3>
	           <p>Here is what we've found so far:</p>
	           <ul>
	               <li id="validatedPages"><span class="number">0</span>Pages</li>
                   <li id="validatedErrors"><span class="number">0</span>Errors</li>
                   <li id="validatedWarnings"><span class="number">0</span>Warnings</li>
	           </ul>
            
            </div>
            <!-- InstanceEndEditable -->
            <div class="clear"></div>
            <?php virtual('/wdn/templates_3.0/includes/noscript.html'); ?>
            <!--THIS IS THE END OF THE MAIN CONTENT AREA.-->
        </div>
        <div id="footer">
            <div id="footer_floater"></div>
            <div class="footer_col">
                <?php virtual('/wdn/templates_3.0/includes/feedback.html'); ?>
            </div>
            <div class="footer_col"><!-- InstanceBeginEditable name="leftcollinks" -->
                <?php echo file_get_contents('http://wdn.unl.edu/sharedcode/relatedLinks.html'); ?>
                <!-- InstanceEndEditable --></div>
            <div class="footer_col"><!-- InstanceBeginEditable name="contactinfo" -->
                <h3>Contacting Us</h3>
                <p>
                The WDN is coordinated by:<br />
                <strong>University Communications</strong><br />
                Internet and Interactive Media<br />
                WICK 17<br />
                Lincoln NE 68583-0218</p>
                <!-- InstanceEndEditable --></div>
            <div class="footer_col">
                <?php virtual('/wdn/templates_3.0/includes/socialmediashare.html'); ?>
            </div>
            <!-- InstanceBeginEditable name="optionalfooter" --> <!-- InstanceEndEditable -->
            <div id="wdn_copyright"><!-- InstanceBeginEditable name="footercontent" -->
                <?php file_get_contents('http://wdn.unl.edu/sharedcode/footer.html'); ?>
                <!-- InstanceEndEditable -->
                <?php virtual('/wdn/templates_3.0/includes/wdn.html'); ?>
                | <a href="http://validator.unl.edu/check/referer">W3C</a> | <a href="http://jigsaw.w3.org/css-validator/check/referer?profile=css3">CSS</a> <a href="http://www.unl.edu/" title="UNL Home" id="wdn_unl_wordmark"><img src="/wdn/templates_3.0/css/footer/images/wordmark.png" alt="UNL's wordmark" /></a> </div>
        </div>
    </div>
    <div id="wdn_wrapper_footer"> </div>
</div>
</body>
<!-- InstanceEnd --></html>
