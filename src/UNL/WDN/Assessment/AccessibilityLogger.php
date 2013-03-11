<?php
class UNL_WDN_Assessment_AccessibilityLogger extends Spider_LoggerAbstract
{
    public static $validator_uri = "http://ucommfairchild.unl.edu/achecker/checkacc.php";
    public static $api_key = "";

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
        $url = self::$validator_uri . "?uri=" . urlencode($uri);
        $url .= "&id=" . self::$api_key;
        $url .= "&output=rest";
        $url .= "&output=rest";
        $url .= "&guide=WCAG2-AA";
        
        if (!$xml = @file_get_contents($url)) {
            return false;
        }

        if (!$xml = simplexml_load_string($xml)) {
            return false;
        }
        
        $errors = $xml->xpath('//NumOfErrors');
        
        if ($errors === null) {
            return false;
        }
        
        return $errors;
    }

    function setValidationResult($uri, $result)
    {
        $sth = $this->assessment->db->prepare('UPDATE assessment SET accessibility_errors = ?, timestamp = ? WHERE baseurl = ? AND url = ?;');

        $sth->execute(array($result, date('Y-m-d H:i:s'), $this->assessment->baseUri, $uri));
    }
}