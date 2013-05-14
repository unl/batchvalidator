<?php
$stats = $context->assessment->getStats();
$error_class = 'margin-bottom: 20px; padding: 20px; background:#d89894; border-style: solid; border-color: #cf7976; border-width:3px; border-radius: 3px; font-size:14px; line-height:24px; font-family:Helvetica,Arial,sans-serif; display:block; text-align:center;';
$ok_class = 'margin-bottom: 20px; padding: 20px; background:#b7dd9b; border-style: solid; border-color: #a6d186; border-width:3px; border-radius: 3px; font-size:14px; line-height:24px; font-family:Helvetica,Arial,sans-serif; display:block; text-align:center;';
?>

<span class="emailbodytext" style="margin-bottom: 30px; font-size:22px; line-height:34px; font-family:Helvetica,Arial,sans-serif; display:block;">
    Hello! The UNL Web Developer Network has launched a new service, UNL Site Checker, to help you maintain your UNL 
    website. This email is being sent to you because you are listed in the WDN Registry as a 'member' of this 
    website.

    The Site Checker tool looks at a number of aspects of your site, including validity of the site's HTML markup, broken 
    links, and whether or not the site is running the latest UNLedu Web Framework files.
    <?php
    if ($stats['page_limit'] == 1) {
        echo "For the results shown below, only the homepage was checked. Future scans will check the entire site.";
    }
    ?>
    You can get more information on any errors 
    noted in this email, or run your own "full site scan" by entering the 
    <a href='http://validator.unl.edu/site/?uri=<?php echo urlencode($context->assessment->baseUri)?>'>WDN Site Checker tool</a> now.
</span>

<span class="emailbodytext" style="margin-bottom: 30px; margin-left:30px; font-size:22px; line-height:34px; font-family:Helvetica,Arial,sans-serif; display:block;">
    Site Checked: <a href="<?php echo $context->assessment->baseUri; ?>"><?php echo ($stats['site_title'] == 'unknown')?$context->assessment->baseUri:$stats['site_title']; ?></a>
</span>

<?php
if ($stats['total_grid_2006_pages']) {
    ?>
    <span class="emailbodytext" style="margin-bottom: 30px; font-size:22px; line-height:34px; font-family:Helvetica,Arial,sans-serif; display:block; <?php echo $error_class?>">
        <span class="h3" style="font-size:26px; font-weight:bold; font-family:Helvetica,Arial,sans-serif; display:block; color:#535353;">Deprecated 2006 grid system was found on this site.</span>
        <p>
            Grid columns with classes such as .one_col, .two_col are deprecated and will not be
            supported in the 4.0 release of the UNLedu Framework. Please upgrade to the latest
            <a href='http://wdn.unl.edu/resources/grid/'>grid system</a>.
        </p>
        <p>
        <a href="http://validator.unl.edu/site/?uri=<?php echo urlencode($context->assessment->baseUri)?>">View the complete results</a> to see a list of pages using the 2006 grid system.
        </p>
    </span>
    <?php
}
?>

<span class="emailbodytext" style="margin-bottom: 30px; font-size:22px; line-height:34px; font-family:Helvetica,Arial,sans-serif; display:block;">
    Here is a summary of your results.
    <a href="http://validator.unl.edu/site/?uri=<?php echo urlencode($context->assessment->baseUri)?>">View the complete results</a> now.
</span>

<span class="emailbodytext" style="margin-bottom: 30px; font-size:22px; line-height:34px; font-family:Helvetica,Arial,sans-serif; display:block; <?php echo $ok_class?>">
    <span class="h3" style="font-size:26px; font-weight:bold; font-family:Helvetica,Arial,sans-serif; display:block; color:#535353;"><?php echo $stats['total_pages'] ?> Pages Checked</span>
    <p>
        Grid columns with classes such as .one_col, .two_col are deprecated and will not be
        supported in the 4.0 release of the UNLedu Framework. Please upgrade to the latest
        <a href='http://wdn.unl.edu/resources/grid/'>grid system</a>.
    </p>
</span>

<?php
$class = $ok_class;
if ($stats['total_html_errors']) {
    $class = $error_class;
}
?>
<span class="emailbodytext" style="margin-bottom: 30px; font-size:22px; line-height:34px; font-family:Helvetica,Arial,sans-serif; display:block; <?php echo $class?>">
    <span class="h3" style="font-size:26px; font-weight:bold; font-family:Helvetica,Arial,sans-serif; display:block; color:#535353;"><?php echo $stats['total_html_errors'] ?> HTML Errors</span>
    <p>
        HTML errors are due to invalid HTML markup in your pages.  They may cause inconsistent rendering and behavior between browsers.
    </p>
</span>

<?php
$class = $ok_class;
if ($stats['total_pages'] && round(($stats['total_current_template_html']/$stats['total_pages'])*100) < 100) {
    $class = $error_class;
}
?>
<span class="emailbodytext" style="margin-bottom: 30px; font-size:22px; line-height:34px; font-family:Helvetica,Arial,sans-serif; display:block; <?php echo $class?>">
    <span class="h3" style="font-size:26px; font-weight:bold; font-family:Helvetica,Arial,sans-serif; display:block; color:#535353;"><?php echo ($stats['total_pages'])?round(($stats['total_current_template_html']/$stats['total_pages'])*100):'' ?>% in current HTML (v<?php echo  $stats['current_template_html'] ?>)</span>
    <p>
        The latest version of the supporting UNLedu framework HTML markup.  If you are using UNLcms, this will be updated automatically.
    </p>
</span>

<?php
$class = $ok_class;
if ($stats['total_pages'] && round(($stats['total_current_template_dep']/$stats['total_pages'])*100) < 100) {
    $class = $error_class;
}
?>
<span class="emailbodytext" style="margin-bottom: 30px; font-size:22px; line-height:34px; font-family:Helvetica,Arial,sans-serif; display:block; <?php echo $class?>">
    <span class="h3" style="font-size:26px; font-weight:bold; font-family:Helvetica,Arial,sans-serif; display:block; color:#535353;"><?php echo ($stats['total_pages'])?round(($stats['total_current_template_dep']/$stats['total_pages']*100)):'' ?>% in current Dependents (v<?php echo  $stats['current_template_dep'] ?>)</span>
    <p>
        The latest version of the supporting UNLedu framework resources (CSS, JS and other includes).  If you are using UNLcms, this will be updated automatically.
    </p>
</span>

<?php
foreach ($stats['total_bad_links'] as $code=>$total) {
    $class = $ok_class;
    if ($total) {
        $class = $error_class;
    }
    
    $helper_text = "";
    $title = "";
    switch ($code) {
        case "404": 
            $helper_text = "These link to a resource that no longer exist.  Please remove these links.";
            $title = "Broken Links";
            break;
        case "301":
            $helper_text = "These link to a resource that has been permanently redirected. Update your link to the redirected resource.";
            $title = "Redirected Links";
            break;
    }
    
    ?>
    <span class="emailbodytext" style="margin-bottom: 30px; font-size:22px; line-height:34px; font-family:Helvetica,Arial,sans-serif; display:block; <?php echo $class?>">
        <span class="h3" style="font-size:26px; font-weight:bold; font-family:Helvetica,Arial,sans-serif; display:block; color:#535353;"><?php echo $total ?> <?php echo $title ?> (<?php echo $code ?>)</span>
        <p>
            <?php echo $helper_text ?>
        </p>
    </span>
    <?php
}
?>

<span class="emailbodytext" style="margin-bottom: 30px; font-size:22px; line-height:34px; font-family:Helvetica,Arial,sans-serif; display:block;">
    For more details on the scan, <a href='http://validator.unl.edu/site/?uri=<?php echo urlencode($context->assessment->baseUri)?>' class="emailmobbutton" style="font-size:14px; font-family:Helvetica,Arial,sans-serif;">View the complete results</a>.
</span>

<span class="emailbodytext" style="margin-bottom: 30px; font-size:22px; line-height:34px; font-family:Helvetica,Arial,sans-serif; display:block;">
    You were sent this email because you are a member of the site in the <a href='http://www1.unl.edu/wdn/'>WDN Registry</a>.
</span>

<span class="emailbodytext" style="margin-bottom: 30px; font-size:22px; line-height:34px; font-family:Helvetica,Arial,sans-serif; display:block;">
    Thank you, <br />
    The Web Developer Network
</span>

