<?php
class UNL_WDN_Assessment_HTMLValidationLogger extends Spider_LoggerAbstract
{
    public static $validator_uri = "http://validator.unl.edu/check";
    
    public static $last_check_time = false;
    
    /**
     * 
     * @var UNL_WDN_Assessment
     */
    public $assessment;
    
    function __construct(UNL_WDN_Assessment $assessment)
    {
        $this->assessment = $assessment;
    }
    
    function log($uri, $depth, DOMXPath $xpath)
    {
        $errors = $this->getNumberOfErrors($uri);
        
        if ($errors === false) {
            $errors = 'unknown';
        }
        
        $this->setValidationResult($uri, $errors);
    }
    
    function getNumberOfErrors($uri)
    {
        //Wait until at least 1 second has passed between checks.
        if (self::$last_check_time && (time() - self::$last_check_time) < 1) {
            sleep(1);
        }
        
        //Set to head to speed things up.
        stream_context_set_default(
            array(
                'http' => array(
                    'method' => 'HEAD'
                )
            )
        );

        if (!$headers = @get_headers(self::$validator_uri . "?uri=" . urlencode($uri), 1)) {
            return false;
        }

        //Update the last check time.
        self::$last_check_time = time();
        
        if (!isset($headers['X-W3C-Validator-Errors'])) {
            return false;
        }

        //return to normal
        stream_context_set_default(
            array(
                'http' => array(
                    'method' => 'GET'
                )
            )
        );

        return $headers['X-W3C-Validator-Errors'];
    }

    function setValidationResult($uri, $result)
    {
        $sth = $this->assessment->db->prepare('UPDATE assessment SET html_errors = ?, timestamp = ? WHERE baseurl = ? AND url = ?;');

        $sth->execute(array($result, date('Y-m-d H:i:s'), $this->assessment->baseUri, $uri));
    }
}