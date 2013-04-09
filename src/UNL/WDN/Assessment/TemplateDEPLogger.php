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

        //look for >= 3.1 templates
        $nodes = $xpath->query(
            "//xhtml:script[@id='wdn_dependents']/@src"
        );

        foreach ($nodes as $node) {
            $version = $node->nodeValue;
        }

        $matches = array();

        if (preg_match('/all.js\?dep=([0-9.]*)/', $version, $matches) && isset($matches[1])) {
            //found look for >= 3.1 templates
            return $matches[1];
        }

        //look for 3.0
        $nodes = $xpath->query(
            "//xhtml:script/@src"
        );

        foreach ($nodes as $node) {
            if (stripos($node->nodeValue, 'templates_3.0') !== false) {
                //found 3.0
                return "3.0";
            }
        }
        
        //Couldn't find anything.
        return false;
    }

    function setDepVersion($uri, $result)
    {
        $sth = $this->assessment->db->prepare('UPDATE assessment SET template_dep = ?, timestamp = ? WHERE baseurl = ? AND url = ?;');

        $sth->execute(array($result, date('Y-m-d H:i:s'), $this->assessment->baseUri, $uri));
    }
}
