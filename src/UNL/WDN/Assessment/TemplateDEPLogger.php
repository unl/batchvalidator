<?php
class UNL_WDN_Assessment_TemplateDEPLogger extends Spider_LoggerAbstract
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
        $version = $this->getDEPVersion($xpath);
        
        if (!$version) {
            $version = 'unknown';
        }
        
        $this->setDepVersion($uri, $version);
    }

    public function getDEPVersion(DOMXPath $xpath)
    {
        $version = "";

        $nodes = $xpath->query(
            "//xhtml:script[@id='wdn_dependents']/@src"
        );

        foreach ($nodes as $node) {
            $version = $node->nodeValue;
        }

        $matches = array();

        if (!preg_match('/all.js\?dep=([0-9.]*)/', $version, $matches)) {
            return false;
        }
        
        if (!isset($matches[1])) {
            return false;
        }

        return $matches[1];
    }

    function setDepVersion($uri, $result)
    {
        $sth = $this->assessment->db->prepare('UPDATE assessment SET template_dep = ?, timestamp = ? WHERE baseurl = ? AND url = ?;');

        $sth->execute(array($result, date('Y-m-d H:i:s'), $this->assessment->baseUri, $uri));
    }
}
