<?php
require_once dirname(__FILE__).'/../config.sample.php';

if (!empty($_POST)) {
    $mailer               = new UNL_WDN_Emailer_Main();
    $mailer->html_body    = $_POST['body'];
    $mailer->to_address   = $_POST['to'];
    $mailer->from_address = $_POST['from'];
    $mailer->subject      = $_POST['subject'];
    if (!empty($_POST['send'])) {
        $mailer->send();
    }
}
?>

<script type="text/javascript">
//<![CDATA[
    WDN.jQuery(document).ready(function(){
         WDN.initializePlugin('zenform');
    });
//]]>
</script>
<h3 class="zenform">Important Survey</h3>
<form class="zenform" action="?" method="post">
    <fieldset>
            <legend>Sample Form Content</legend>
            <ol>
                <li>
                    <label for="from">
                        <span class="required">*</span>
                        From:
                        <span class="helper">Enter the 'from' email address</span>
                    </label>
                    <input type="text" id="from" name="from" />
                </li>
                <li>
                    <label for="to">To:
                        <span class="helper">Enter the 'to' email address</span>
                    </label>
                    <input type="text" id="to" name="to" />
                </li>
                <li>
                    <label for="subject">Subject:
                        <span class="helper">Enter the email address</span>
                    </label>
                    <input type="text" id="subject" name="subject" />
                    
                </li>
                <li>
                    <label for="body">Email Body:</label>
                    <textarea id="body" name="body" rows="30" cols="60"></textarea>
                </li>
            </ol>
    </fieldset> 
    <input type="submit" name="submit" value="Submit" />
</form>