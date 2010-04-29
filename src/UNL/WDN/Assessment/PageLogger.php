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
        $this->assessment->addUri($uri);
        echo PHP_EOL.'<div id="uri_'.md5($uri).'" class="depth_'.$depth.' '.$this->assessment->getValidityStatus($uri).'">
        <span class="uri">'.$uri.'</span>
        </div>'.PHP_EOL;
    }
}
