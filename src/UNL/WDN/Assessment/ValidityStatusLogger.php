<?php
class UNL_WDN_Assessment_ValidityStatusLogger extends Spider_LoggerAbstract
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
    
    function log($uri, $depth, DOMXPath $xpath)
    {
        $status = $this->assessment->getValidityStatus($uri);
        echo '<span id="validity_'.md5($uri).'" class=" validity '.$status.'">'.$status.'</span>'.PHP_EOL;
    }
}