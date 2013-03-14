<?php
class UNL_WDN_Assessment_Mailer
{
    /**
     *
     * @var UNL_WDN_Assessment
     */
    public $assessment;

    function __construct(UNL_WDN_Assessment $assessment)
    {
        $this->assessment = $assessment;
    }
    
    function getContactAddress()
    {
        if (!$data = file_get_contents("http://www1.unl.edu/wdn/registry/?output=json&u=" . urlencode($this->assessment->baseUri))) {
            return false;
        }
        
        if (!$data = json_decode($data, true)) {
            return false;
        }
        
        $site = reset($data);
        
        if (!isset($site['members'])) {
            return false;
        }
        
        $address = array();
        
        foreach($site['members'] as $uid=>$info)
        {
            foreach ($info['roles'] as $role) {
                if (in_array($role, array('developer', 'sysadmin', 'content'))) {
                    if (!$data = file_get_contents("http://directory.unl.edu/?uid=" . $uid . "&format=json")) {
                        break;
                    }
                    
                    if (!$json = json_decode($data, true)) {
                        break;
                    }
                    
                    if (!isset($json['mail'][0])) {
                        break;
                    }

                    $address[] = $json['mail'][0];
                    
                    break;
                }
            }
        }
        
        return implode(",", $address);
    }

    
    function mail($email = null)
    {
        $mailer = new UNL_WDN_Emailer_Main();

        $stats = $this->assessment->getStats();
        
        $body = "We have completed a site check on " . $this->assessment->baseUri . "<br />";

        $body .= "<ul>
                    <li>" . $stats['total_pages'] . " Pages</li>
                    <li>" . $stats['total_html_errors'] . " HTML Errors</li>
                    <li>" . round(($stats['total_current_template_html']/$stats['total_pages'])*100) . "% in current HTML (v" . $stats['current_template_html']  . ")</li>
                    <li>" . round(($stats['total_current_template_dep']/$stats['total_pages']*100)) . "% in current Dependents (v" . $stats['current_template_dep']  . ")</li>
                    <li>" . $stats['total_bad_links'] . " Bad Links</li>
                  </ul>";

        $body .= "<a href='http://validator.unl.edu/site/?uri=" . urlencode($this->assessment->baseUri) . "'>View the complete results</a>";

        if (!$to = $this->getContactAddress()) {
            return false;
        }
        
        if (isset($email)) {
            $to = $email;
        }
        
        $mailer->html_body    = $body;
        $mailer->to_address   = $to;
        $mailer->from_address = "noreply@unl.edu";
        $mailer->subject      = "Site Check Complete - " . $this->assessment->baseUri;

        $mailer->send();
    }
}