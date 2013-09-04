<?php
class UNL_WDN_Assessment_LoggedLinkLogger extends Spider_LoggerAbstract
{
    protected static $checked = array();

    //Logged Extensions
    public static $loggedExtensions = array('pdf');
    
    public static $loggedReasons = array('pdf');

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
        $links = $this->getLinks($uri, $xpath);

        $this->checkLinks($uri, $links, $depth);
    }

    function checkLinks($uri, $links, $depth)
    {
        foreach ($links as $link) {
            if (!$urlInfo = parse_url($link)) {
                continue;
            }
            
            if (isset($urlInfo['path']) && $pathInfo = pathInfo($urlInfo['path'])) {
                if (isset($pathInfo['extension'])) {
                    foreach (self::$loggedExtensions as $extension) {
                        if ($pathInfo['extension'] == $extension) {
                            $this->addLink($link, 'extension_' . $extension, $uri);
                        }
                    }
                }
            }
        }
    }

    protected function getLinks($uri, DOMXPath $xpath)
    {
        $links = Spider::getUris(Spider::getUriBase($uri), $uri, $xpath);

        return $links;
    }

    function addLink($link, $reason, $uri)
    {
        $sth = $this->assessment->db->prepare('INSERT INTO logged_links (baseurl, url, link_url, reason, timestamp) VALUES (?, ?, ?, ?, ?);');
        $sth->execute(array($this->assessment->baseUri, $uri, $link, $reason, date('Y-m-d H:i:s')));
    }
}