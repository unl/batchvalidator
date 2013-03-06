<?php
class UNL_WDN_Assessment_TemplateHTMLLogger extends Spider_LoggerAbstract
{
    public function log($uri, $depth, DOMXPath $xpath)
    {
        $version = $this->getHTMLVersion($xpath);

        echo "<div>HTML Version: " . $version . "</div>";
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
}
