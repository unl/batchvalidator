<?php
$stats = $context->assessment->getStats();
$error_class = 'margin-bottom: 20px; padding: 20px; background:#d89894; border-style: solid; border-color: #cf7976; border-width:3px; border-radius: 3px; font-size:14px; line-height:24px; font-family:Helvetica,Arial,sans-serif; display:block; text-align:center;';
$ok_class = 'margin-bottom: 20px; padding: 20px; background:#b7dd9b; border-style: solid; border-color: #a6d186; border-width:3px; border-radius: 3px; font-size:14px; line-height:24px; font-family:Helvetica,Arial,sans-serif; display:block; text-align:center;';
?>

<span class="emailbodytext" style="margin-bottom: 30px; font-size:22px; line-height:34px; font-family:Helvetica,Arial,sans-serif; display:block;">
    Hello! The UNL Web Developer Network has launched a new service, UNL Site Checker, to help you maintain your UNL 
    website. This email is being sent to you because you are listed in the WDN Registry as a 'member' of this 
    website.

    The Site Checker tool looks at a number of aspects of your site, including validity of the sites HTML markup, broken 
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
    <a href="<?php echo $context->assessment->baseUri; ?>"><?php echo ($stats['site_title'] == 'unknown')?$context->assessment->baseUri:$stats['site_title']; ?></a>
</span>
<span class="emailbodytext" style="margin-bottom: 30px; font-size:22px; line-height:34px; font-family:Helvetica,Arial,sans-serif; display:block;">
    Here are your results.
</span>

<table border="0" cellspacing="0" cellpadding="0" width="640" class="emailwrapto100pc">
    <tr>
        <td class="emailcolsplit" align="left" valign="top" width="310">
            <span class="emailbodytext" style="<?php echo $ok_class; ?>">
                <span style="display:block; font-size:28px; font-weight:bold;"><?php echo $stats['total_pages'] ?></span> Pages
            </span>
        </td>
        <td class="emailcolgutter" width="20">
            &nbsp;
        </td>
        <td class="emailcolsplit" align="left" valign="top" width="310">
            <?php 
                if ($stats['total_html_errors'] > 0) {
                    echo '<span class="emailbodytext" style="'.$error_class.'">';
                } else {
                    echo '<span class="emailbodytext" style="'.$ok_class.'">';
                }
            ?>
                <span style="display:block; font-size:28px; font-weight:bold;"><?php echo $stats['total_html_errors'] ?></span> HTML Errors
            </span>
        </td>
    </tr>

    <tr>
        <td class="emailcolsplit" align="left" valign="top" width="310">
            <?php 
                if ($stats['total_pages'] && round(($stats['total_current_template_html']/$stats['total_pages'])*100) < 100) {
                    echo '<span class="emailbodytext" style="'.$error_class.'">';
                } else {
                    echo '<span class="emailbodytext" style="'.$ok_class.'">';
                }
            ?>
                <span style="display:block; font-size:28px; font-weight:bold;"><?php echo ($stats['total_pages'])?round(($stats['total_current_template_html']/$stats['total_pages'])*100):'' ?>%</span> in current HTML (v<?php echo  $stats['current_template_html'] ?>)
            </span>
        </td>
        <td class="emailcolgutter" width="20">
            &nbsp;
        </td>
        <td class="emailcolsplit" align="left" valign="top" width="310">
            <?php 
                if ($stats['total_pages'] && round(($stats['total_current_template_dep']/$stats['total_pages'])*100) < 100) {
                    echo '<span class="emailbodytext" style="'.$error_class.'">';
                } else {
                    echo '<span class="emailbodytext" style="'.$ok_class.'">';
                }
            ?>
                <span style="display:block; font-size:28px; font-weight:bold;"><?php echo ($stats['total_pages'])?round(($stats['total_current_template_dep']/$stats['total_pages']*100)):'' ?>%</span> in current Dependents (v<?php echo  $stats['current_template_dep'] ?>)
            </span>
        </td>
    </tr>

    <?php
    $i = 0;
    foreach ($stats['total_bad_links'] as $code=>$total) {
        if ($i == 0) {
            echo "<tr>";
        }
        ?>
        <td class="emailcolsplit" align="left" valign="top" width="310">
            <?php
            if ($total > 0) {
                echo '<span class="emailbodytext" style="'.$error_class.'">';
            } else {
                echo '<span class="emailbodytext" style="'.$ok_class.'">';
            }
            ?>
            <span style="display:block; font-size:28px; font-weight:bold;"><?php echo $total ?></span> <?php echo $code ?> Links
            </span>
        </td>
        <?php
        $i++;
        
        if ($i == 1) {
            echo '<td class="emailcolgutter" width="20">&nbsp;</td>';
        }
        
        if ($i == 2) {
            echo "</tr>";
            $i = 0;
        }
    }
    
    if ($i == 1) {
        echo "</tr>";
    }
    ?>

</table>

<span class="emailbodytext" style="margin-bottom: 20px; font-size:14px; line-height:24px; font-family:Helvetica,Arial,sans-serif; display:block;">
    For more details on the scan, <a href='http://validator.unl.edu/site/?uri=<?php echo urlencode($context->assessment->baseUri)?>'>view the complete results</a>.
</span>

<span class="emailbodytext" style="margin-bottom: 20px; font-size:14px; line-height:24px; font-family:Helvetica,Arial,sans-serif; display:block;">
    You were sent this email because you are a member of the site in the <a href='http://www1.unl.edu/wdn/'>WDN Registry</a>.
</span>

<span class="emailbodytext" style="margin-bottom: 20px; font-size:14px; line-height:24px; font-family:Helvetica,Arial,sans-serif; display:block;">
    With most humble regards, <br />
    The Web Developer Network
</span>

