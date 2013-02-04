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
        $status = $this->assessment->getValidityStatus($uri);
        
        if (empty($status)) {
            $status = "unknown";
        }
        
        echo PHP_EOL.'<div id="uri_'.md5($uri).'" class="depth_'.$depth.' '.$status.'">
        <span class="uri">'.$uri.'</span>
        </div>'.PHP_EOL;
    }
}
