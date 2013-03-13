<?php
class UNL_WDN_Assessment_URILogger extends Spider_LoggerAbstract
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
    
    public function log($uri, $depth, DOMXPath $xpath)
    {
        $this->addUri($uri, $this->isScannable($xpath));
    }
    
    function isScannable(DOMXPath $xpath)
    {
        return (bool)$xpath->query('//xhtml:html')->length;
    }

    function addUri($uri, $scannable)
    {
        $sth = $this->assessment->db->prepare('INSERT INTO assessment (baseurl, url, scannable, timestamp) VALUES (?, ?, ?, ?);');
        $sth->execute(array($this->assessment->baseUri, $uri, (int)$scannable, date('Y-m-d H:i:s')));

    }
}
