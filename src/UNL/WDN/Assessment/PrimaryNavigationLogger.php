<?php
class UNL_WDN_Assessment_PrimaryNavigationLogger extends Spider_LoggerAbstract
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
        $count = $this->getPrimaryNavigationCount($xpath);

        $this->setPrimaryNavigationCount($uri, $count);
    }

    public function getPrimaryNavigationCount(DOMXPath $xpath)
    {
        $nodes = $xpath->query(
            "//xhtml:div[@id='wdn_navigation_wrapper']/xhtml:ul/xhtml:li"
        );
        
        return $nodes->length;
    }

    function setPrimaryNavigationCount($uri, $result)
    {
        $sth = $this->assessment->db->prepare('UPDATE assessment SET primary_nav_count = ?, timestamp = ? WHERE baseurl = ? AND url = ?;');

        $sth->execute(array($result, date('Y-m-d H:i:s'), $this->assessment->baseUri, $uri));
    }
}
