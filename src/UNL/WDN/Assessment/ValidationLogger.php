<?php
class UNL_WDN_Assessment_ValidationLogger extends Spider_LoggerAbstract
{
    /**
     * 
     * @var Services_W3C_HTMLValidator
     */
    public $validator;
    
    /**
     * 
     * @var UNL_WDN_Assessment
     */
    public $assessment;
    
    function __construct(Services_W3C_HTMLValidator $validator, UNL_WDN_Assessment $assessment)
    {
        $this->validator  = $validator;
        $this->assessment = $assessment;
    }
    
    function log($uri, DOMXPath $xpath)
    {
        $this->assessment->addUri($uri);
        $r = $this->validator->validate($uri);
        $this->assessment->setValidationResult($uri, $r->isValid());
    }
}