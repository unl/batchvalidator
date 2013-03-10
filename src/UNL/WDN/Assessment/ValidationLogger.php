<?php
class UNL_WDN_Assessment_ValidationLogger extends Spider_LoggerAbstract
{
    public static $validator_uri = "http://validator.unl.edu/check";
    
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
        $sth = $this->assessment->db->prepare('UPDATE assessment SET valid = ?, timestamp = ? WHERE baseurl = ? AND url = ?;');

        $sth->execute(array($result, date('Y-m-d H:i:s'), $this->assessment->baseUri, $uri));
    }
}