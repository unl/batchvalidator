<?php
class UNL_WDN_Assessment_TemplateDEPLogger extends Spider_LoggerAbstract
{
    public function log($uri, $depth, DOMXPath $xpath)
    {
        $version = $this->getDEPVersion($xpath);

        echo "<div>DEP Version: " . $version . "</div>";
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
}
