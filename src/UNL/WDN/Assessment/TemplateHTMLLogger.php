<?php
class UNL_WDN_Assessment_TemplateHTMLLogger extends Spider_LoggerAbstract
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
        $version = $this->getHTMLVersion($xpath);

        if (!$version) {
            $version = 'unknown';
        }

        $this->setHTMLVersion($uri, $version);
    }

    public function getHTMLVersion(DOMXPath $xpath)
    {
        $version = "";
        
        //look for >= 3.1 templates
        $nodes = $xpath->query(
            "//xhtml:body/@data-version"
        );

        foreach ($nodes as $node) {
            $version = $node->nodeValue;
        }
        
        if (!empty($version)) {
            //found >= 3.1 templates
            return $version;
        }

        //Look for 3.0
        $nodes = $xpath->query(
            "//xhtml:script/@src"
        );
        
        foreach ($nodes as $node) {
            if (stripos($node->nodeValue, 'templates_3.0') !== false) {
                //Found 3.0
                return "3.0";
            }
        }

        //Couldn't find anything.
        return false;
    }

    function setHTMLVersion($uri, $result)
    {
        $sth = $this->assessment->db->prepare('UPDATE assessment SET template_html = ?, timestamp = ? WHERE baseurl = ? AND url = ?;');

        $sth->execute(array($result, date('Y-m-d H:i:s'), $this->assessment->baseUri, $uri));
    }
}
