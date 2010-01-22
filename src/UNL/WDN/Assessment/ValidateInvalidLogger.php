<?php
class UNL_WDN_Assessment_ValidateInvalidLogger extends UNL_WDN_Assessment_ValidationLogger
{
    function log($uri, $depth, DOMXPath $xpath)
    {
        if (!$this->assessment->pageWasValid($uri)) {
            parent::log($uri, $depth, $xpath);
        }
    }
}