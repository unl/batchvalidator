<?php
class UNL_WDN_BasicScan_URILogger extends Spider_LoggerAbstract
{
    /**
     *
     * @var UNL_WDN_BasicScan
     */
    public $scanner;

    function __construct(UNL_WDN_BasicScan $scanner)
    {
        $this->scanner = $scanner;
    }

    public function log($uri, $depth, DOMXPath $xpath)
    {
        $this->addUri($uri, $this->isScannable($xpath));
        
        //Sleep for one second as to not overwhelm servers
        sleep(1);
    }

    function isScannable(DOMXPath $xpath)
    {
        return (bool)$xpath->query('//xhtml:html')->length;
    }

    function addUri($uri, $scannable)
    {
        echo "\t scanned: " . $uri . PHP_EOL;
        $this->scanner->pages[$uri] = array();
    }
}
