<?php
class UNL_WDN_Assessment_GANonAsyncLogger extends Spider_LoggerAbstract
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
        $result = $this->containsOldCode($xpath);

        $this->setGAOldAsync($uri, $result);
    }

    public function containsOldCode(DOMXPath $xpath)
    {
        $nodes = $xpath->query(
            "//xhtml:script"
        );

        foreach ($nodes as $node) {
            
            if (strpos($node->nodeValue, "var pageTracker = _gat._getTracker") !== false) {
                return true;
            }
        }
        
        return false;
    }

    function setGAOldAsync($uri, $result)
    {
        $sth = $this->assessment->db->prepare('UPDATE assessment SET ga_non_async = ?, timestamp = ? WHERE baseurl = ? AND url = ?;');

        $sth->execute(array($result, date('Y-m-d H:i:s'), $this->assessment->baseUri, $uri));
    }
}
