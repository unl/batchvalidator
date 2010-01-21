<?php
class UNL_WDN_Assessment_PageLogger extends Spider_LoggerAbstract
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
    
    function log($uri, DOMXPath $xpath)
    {
        $this->assessment->addUri($uri);
    }
}
