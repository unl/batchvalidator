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

        $this->setHTMLVersion($uri, $version);
    }
    
    public function getHTMLVersion(DOMXPath $xpath)
    {
        $version = "";
        
        $nodes = $xpath->query(
            "//xhtml:body/@data-version"
        );

        foreach ($nodes as $node) {
            $version = $node->nodeValue;
        }
        
        return $version;
    }

    function setHTMLVersion($uri, $result)
    {
        $sth = $this->assessment->db->prepare('UPDATE assessment SET template_html = ?, timestamp = ? WHERE baseurl = ? AND url = ?;');

        $sth->execute(array($result, date('Y-m-d H:i:s'), $this->assessment->baseUri, $uri));
    }
}
