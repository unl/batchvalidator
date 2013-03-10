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

if (!isset($template_path)) {
    $template_path = $_SERVER['DOCUMENT_ROOT'];
}

?>
<!DOCTYPE html>
<!--[if IEMobile 7 ]><html class="ie iem7"><![endif]-->
<!--[if lt IE 7 ]><html class="ie ie6" lang="en"><![endif]-->
<!--[if IE 7 ]><html class="ie ie7" lang="en"><![endif]-->
<!--[if IE 8 ]><html class="ie ie8" lang="en"><![endif]-->
<!--[if (gte IE 9)|(gt IEMobile 7) ]><html class="ie" lang="en"><![endif]-->
<!--[if !(IEMobile) | !(IE)]><!--><html lang="en"><!-- InstanceBegin template="/Templates/php.fixed.dwt.php" codeOutsideHTMLIsLocked="false" --><!--<![endif]-->
<head>
<?php include($template_path . "/wdn/templates_3.1/includes/metanfavico.html"); ?>
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

    $Id: php.fixed.dwt.php | ebd6eef8f48e609f4e2fe9c1d6432991649298e7 | Tue Mar 6 14:38:44 2012 -0600 | Brett Bieber  $
-->
<?php include($template_path . "/wdn/templates_3.1/includes/scriptsandstyles.html"); ?>
<!-- InstanceBeginEditable name="doctitle" -->
<title>Site Checker | Web Developer Network | University of Nebraska&ndash;Lincoln</title>
<!-- InstanceEndEditable -->
<!-- InstanceBeginEditable name="head" -->
<!-- Place optional header elements here -->
<link rel="stylesheet" type="text/css" href="http://wdn.unl.edu/resources/grid/grid-v3.css" />
<link rel="stylesheet" type="text/css" href="css/main.css" />
<script type="text/javascript" src="js/main.min.js"></script>
<script type="text/javascript">var baseURI = '<?php echo $uri; ?>';</script>

<!-- InstanceEndEditable -->
<!-- InstanceParam name="class" type="text" value="fixed" -->
</head>
<body class="fixed" data-version="3.1">
    <nav class="skipnav" role="navigation">
        <a class="skipnav" href="#maincontent">Skip Navigation</a>
    </nav>
    <div id="wdn_wrapper">
        <header id="header" role="banner">
            <a id="logo" href="http://www.unl.edu/" title="UNL website">UNL</a>
            <span id="wdn_institution_title">University of Nebraska&ndash;Lincoln</span>
            <span id="wdn_site_title"><!-- InstanceBeginEditable name="titlegraphic" -->Site Checker<!-- InstanceEndEditable --></span>
            <?php include($template_path . "/wdn/templates_3.1/includes/idm.html"); ?>
            <?php include($template_path . "/wdn/templates_3.1/includes/wdnTools.html"); ?>
        </header>
        <div id="wdn_navigation_bar" role="navigation">
            <nav id="breadcrumbs">
                <!-- WDN: see glossary item 'breadcrumbs' -->
                <h3 class="wdn_list_descriptor hidden">Breadcrumbs</h3>
                <!-- InstanceBeginEditable name="breadcrumbs" -->
                <ul>
                    <li class="first"><a href="http://www.unl.edu/">UNL</a></li>
                    <li><a href="http://wdn.unl.edu/"><abbr title="Web Developer Network">WDN</abbr></a></li>
                    <li>Site Checker</li>
                </ul>
                <!-- InstanceEndEditable -->
            </nav>
            <div id="wdn_navigation_wrapper">
                <nav id="navigation" role="navigation">
                    <h3 class="wdn_list_descriptor hidden">Navigation</h3>
                    <!-- InstanceBeginEditable name="navlinks" -->
                    <?php echo file_get_contents('http://www1.unl.edu/wdn/templates_3.0/scripts/navigationSniffer.php?u=http://wdn.unl.edu/'); ?>
                    <!-- InstanceEndEditable -->
                </nav>
            </div>
        </div>
        <div id="wdn_content_wrapper" role="main">
            <div id="pagetitle" style="display:none;">
                <!-- InstanceBeginEditable name="pagetitle" -->
                <h1>Site Validator</h1>
                <!-- InstanceEndEditable -->
            </div>
            <div id="maincontent">
                <!--THIS IS THE MAIN CONTENT AREA; WDN: see glossary item 'main content area' -->
                <!-- InstanceBeginEditable name="maincontentarea" -->
                <form method="get" action="#" class="wdn-form single" id="validator-form">
                    <fieldset class="main-focus">
                        <legend class="intro-action">Scan your site for validation</legend>
                        <label for="uri" class="element">
                            Enter your site URL <span class="helper-text">Simply use your homepage</span>
                        </label>
                        <input type="url" name="uri" value="<?php echo $uri; ?>" placeholder="http://" required="required" id="uri" />
                        <input type="submit" id="submit" name="submit" value="Check" disabled />
                    </fieldset>
                </form>
                <div id="scan-wrapper">

                </div>
                <script id="temp-validator-results" type="text/x-handlebars-template">
                    <section id="validator-results-setup" class="report-view">
                        <h2 class="report-title">Summary of Check</h2>
                        <div class="wdn-grid-set">
                            <div class="bp2-wdn-col-three-fourths">
                            <h3>Site Information</h3>
                            <ul class="structure-list">
                                <li>
                                    <span class="item-label">Site title:</span> <span id="site-title">{{{site_title}}}</span>
                                </li>
                                <li>
                                    <span class="item-label">Date of last check:</span> <time id="last-scan-date">{{last_scan}}</time>
                                </li>
                            </p>
                            </div>
                            <div class="bp2-wdn-col-one-fourth">
                                <!--<a href="#" class="wdn-button large-button triad recheck-button">Recheck Site</a>-->
                            </div>
                        </div>
                        <div class="wdn-grid-set-halves bp1-wdn-grid-set-thirds bp2-wdn-grid-set-fifths dashboard-metrics">
                            <div class="wdn-col" id="valid-pages">
                                <div class="visual-island">
                                    <span class="dashboard-value">
                                        {{total_pages}}
                                    </span>
                                    <span class="dashboard-metric">
                                        pages
                                    </span>
                                </div>
                            </div>
                            <div class="wdn-col" id="valid-errors">
                                <div class="visual-island {{error_total total_html_errors}}">
                                    <span class="dashboard-value">
                                        {{total_html_errors}}
                                    </span>
                                    <span class="dashboard-metric">
                                        HTML errors
                                    </span>
                                </div>
                            </div>
                            <div class="wdn-col" id="valid-html">
                                <div class="visual-island {{error_percentage total_current_template_html total_pages}}">
                                    <span class="dashboard-value">
                                        {{percentage total_current_template_html total_pages}}
                                    </span>
                                    <span class="dashboard-metric">
                                        current HTML
                                    </span>
                                </div>
                            </div>
                            <div class="wdn-col" id="valid-dependents">
                                <div class="visual-island {{error_percentage total_current_template_dep total_pages}}">
                                    <span class="dashboard-value">
                                        {{percentage total_current_template_dep total_pages}}
                                    </span>
                                    <span class="dashboard-metric">
                                        current dependents
                                    </span>
                                </div>
                            </div>
                            <div class="wdn-col" id="valid-links">
                                <div class="visual-island {{error_total total_bad_links}}">
                                    <span class="dashboard-value">
                                        {{total_bad_links}}
                                    </span>
                                    <span class="dashboard-metric">
                                        Bad links
                                    </span>
                                </div>
                            </div>
                        </div>
                        <table class="wdn_responsive_table" id="validator-results">
                            <caption>Results for your viewing pleasure</caption>
                            <thead>
                                <tr>
                                    <th id="validator-page">Page</th>
                                    <th id="validator-html">HTML Errors</th>
                                    <th id="validator-current-html">Current HTML</th>
                                    <th id="validator-current-dependents">Current Dependents</th>
                                    <th id="validator-404">Bad Links</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{#each pages}}
                                <tr data-page="{{page}}">
                                    <th id="page-01" class="side-col">
                                        {{strip_site page}}
                                    </th>
                                    <td headers="page-01 validator-html" data-header="HTML Validity" class="{{error_total html_errors}}">
                                        {{html_errors}}
                                    </td>
                                    <td headers="page-01 validator-current-html" data-header="Current HTML" class="{{error_boolean template_html.current}}">
                                        {{{format_boolean template_html.current}}}
                                    </td>
                                    <td headers="page-01 validator-current-dependents" data-header="Current Dependents" class="{{error_boolean template_dep.current}}">
                                        {{{format_boolean template_dep.current}}}
                                    </td>
                                    {{#if bad_links}}
                                        <td headers="page-01 validator-404" data-header="Bad Links" class="error">
                                            {{links bad_links}}
                                        </td>
                                    {{else}}
                                        <td headers="page-01 validator-404" data-header="Bad Links">
                                            0
                                        </td>
                                    {{/if}}
                                </tr>
                                {{/each}}
                            </tbody>
                        </table>
                        <div class="footer">
                            <div class="wdn-grid-set">
                                <div class="bp2-wdn-col-three-fourths">
                                <ul class="structure-list">
                                    <li>
                                        <span class="item-label">Date of last check:</span> <time id="last-scan-date">{{last_scan}}</time>
                                    </li>
                                </p>
                                </div>
                                <div class="bp2-wdn-col-one-fourth">
                                    <a href="#" class="wdn-button large-button triad recheck-button">Recheck Site</a>
                                </div>
                            </div>
                        </div>
                    </section>
                </script>
                <div class="loader hidden">
                    <p class="action-title">Site check! 1. 2. 3.</p>
                    <p>Your site is being checked; our hamsters are running as quickly as possible. <br /> We'll present the results as soon as they're ready.</p>
                    <div class="wdn-spinner">
                        <div class="circle"></div>
                        <div class="circle1"></div>
                    </div>
                </div>
                <!-- InstanceEndEditable -->
                <div class="clear"></div>
                <?php include($template_path . "/wdn/templates_3.1/includes/noscript.html"); ?>
                <!--THIS IS THE END OF THE MAIN CONTENT AREA.-->
            </div>
        </div>
        <footer id="footer" role="contentinfo">
            <div id="footer_floater"></div>
            <div class="footer_col" id="wdn_footer_feedback">
                <?php include($template_path . "/wdn/templates_3.1/includes/feedback.html"); ?>
            </div>
            <div class="footer_col" id="wdn_footer_related">
                <!-- InstanceBeginEditable name="leftcollinks" -->
                <?php echo file_get_contents('http://wdn.unl.edu/sharedcode/relatedLinks.html'); ?>
                <!-- InstanceEndEditable --></div>
            <div class="footer_col" id="wdn_footer_contact">
                <!-- InstanceBeginEditable name="contactinfo" -->
                <h3>Contacting Us</h3>
                <p>
                The WDN is coordinated by:<br />
                <strong>University Communications</strong><br />
                Internet and Interactive Media<br />
                WICK 17<br />
                Lincoln, NE 68583-0218</p>
                <!-- InstanceEndEditable --></div>
            <div class="footer_col" id="wdn_footer_share">
                <?php include($template_path . "/wdn/templates_3.1/includes/socialmediashare.html"); ?>
            </div>
            <!-- InstanceBeginEditable name="optionalfooter" -->
            <!-- InstanceEndEditable -->
            <div id="wdn_copyright">
                <div>
                    <!-- InstanceBeginEditable name="footercontent" -->
                    <?php file_get_contents('http://wdn.unl.edu/sharedcode/footer.html'); ?>
                    <!-- InstanceEndEditable -->
                    <?php include($template_path . "/wdn/templates_3.1/includes/wdn.html"); ?>
                </div>
                <?php include($template_path . "/wdn/templates_3.1/includes/logos.html"); ?>
            </div>
        </footer>
    </div>
</body>
<!-- InstanceEnd --></html>
