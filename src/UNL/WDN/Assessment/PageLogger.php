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
    
    function log($uri, $depth, DOMXPath $xpath)
    {
        echo PHP_EOL.'|'.str_repeat('-', $depth).$uri;
        $this->assessment->addUri($uri);
    }
}
