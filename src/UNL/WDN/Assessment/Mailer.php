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
        $savvy = new Savvy();
        $savvy->setTemplatePath(dirname(dirname(dirname(dirname(dirname(__FILE__))))).'/www/templates/');
        $body = $savvy->render($this, 'email/UNL/Assessment/Mailer.tpl.php');

        $mailer = new UNL_WDN_Emailer_Main();

        $to = $email;
        
        if (empty($email) && !$to = $this->getContactAddress()) {
            return false;
        }
        
        $mailer->html_body    = $body;
        $mailer->to_address   = $to;
        $mailer->from_address = "noreply@unl.edu";
        $mailer->subject      = "Site Check Complete - " . $this->assessment->baseUri;

        $mailer->send();
    }
}