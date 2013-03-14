<div>
<table cellspacing="0" cellpadding="0" border="0" width="98%" style="margin-top:10px; margin-bottom:10px;">
<?php if (isset($context->web_view_uri)): ?>
<tr>
    <td align="center">
        <a href="<?php echo $context->web_view_uri; ?>">Problem viewing? Click here to read online.</a>
    </td>
</tr>
<?php endif; ?>
<tr>
    <td align="center" valign="top">
        <!-- [ header starts here] -->
        <table cellspacing="0" cellpadding="0" border="0" width="600">
            <tr>
                <td valign="top"><img src="http://www.unl.edu/wdn/templates_3.0/images/email/header.jpg" alt="The University of Nebraska-Lincoln" width="600" height="126" border="0"/></td>
            </tr>
        </table>
        <table cellspacing="0" cellpadding="0" border="0" width="600" valign="top">
            <tr>
                <td width="12" bgcolor="#E0E0E0">&nbsp;</td>
                <td width="10">&nbsp;</td>
                <td align="left" valign="top" width="556">
                    <?php echo $context->html_body; ?>
                </td>
                <td width="10"></td>
                <td width="12" bgcolor="#E0E0E0"></td>
            </tr>
        </table>
        <table cellspacing="0" cellpadding="0" border="0" width="600" height="22">
            <tr>
                <td valign="top"><img src="http://www.unl.edu/wdn/templates_3.0/images/email/footer.jpg" alt="The University of Nebraska-Lincoln" width="600" height="22" /></td>
            </tr>
        </table>
    </td>
</tr>
</table>
</div>