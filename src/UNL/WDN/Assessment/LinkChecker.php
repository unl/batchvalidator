<?php
class UNL_WDN_Assessment_LinkChecker extends Spider_LoggerAbstract
{
    protected static $checked = array();
    
    //this can be altered, but should remain low, as to not overload servers.
    protected static $maxActiveRequests = 10;

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
        $links = $this->getLinks($xpath);
        
        $this->checkLinks($uri, $links, $depth);
        
    }
    
    function checkLinks($uri, $links, $depth)
    {
        $mcurl = curl_multi_init();
        $curl = array();
        $activeRequests = 0;
        while (count($links) + $activeRequests > 0) {
        
            //Limit the number of concurrent checks
            while ($activeRequests <= self::$maxActiveRequests && count($links) > 0) {
                $link = Spider::absolutePath(array_shift($links), $uri);
                
                //Don't check it if it is not a valid url
                if (!filter_var($link, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)) {
                    continue;
                }
                
                //Don't recheck it if we just checked it
                if (isset(self::$checked[$link])) {
                    //But DO add it to the list for this page.
                    $this->addLink($link, self::$checked[$link], $uri);
                    continue;
                }
                
                $curl[$link] = curl_init($link);
                curl_setopt($curl[$link], CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl[$link], CURLOPT_NOBODY, true);
                curl_setopt($curl[$link], CURLOPT_CONNECTTIMEOUT, 5);
                curl_setopt($curl[$link], CURLOPT_LOW_SPEED_LIMIT, 10);
                curl_setopt($curl[$link], CURLOPT_LOW_SPEED_TIME, 5);
                curl_setopt($curl[$link], CURLOPT_FOLLOWLOCATION, false);
                curl_setopt($curl[$link], CURLOPT_USERAGENT, UNL_WDN_Assessment::$spiderUserAgent);
                curl_multi_add_handle($mcurl, $curl[$link]);
                $activeRequests++;
            }
        
            sleep(1);
            curl_multi_exec($mcurl, $running);
                
            while ($msg = curl_multi_info_read($mcurl, $msgCount)) {
                
                $finishedCurl = $msg['handle'];
                $info = curl_getinfo($finishedCurl);
                $activeRequests--;
                
                //Mark the url as checked.
                self::$checked[$info['url']] = $info['http_code'];
                
                if ($info['http_code'] != 200) {
                    $this->addLink($info['url'], $info['http_code'], $uri);
                    continue;
                }

                curl_multi_remove_handle($mcurl, $finishedCurl);
                curl_close($finishedCurl);
            }
        
        }
    }

    protected function getLinks(DOMXPath $xpath)
    {
        $links = array();

        $nodes = $xpath->query(
            "//xhtml:a[@href]/@href | //a[@href]/@href"
        );

        foreach ($nodes as $node) {
            $link = trim((string)$node->nodeValue);
            if (substr($link, 0, 7) != 'mailto:'
                && substr($link, 0, 11) != 'javascript:'
                && substr($link, 0, 1) != '#') {
                $links[] = $link;
            } 
        }

        sort($links);
        return $links;
    }

    function addLink($link, $code, $uri)
    {
        if (!in_array($code, array(404, 301))) {
            return;
        }
        
        $sth = $this->assessment->db->prepare('INSERT INTO url_has_badlinks (baseurl, url, link_url, code, timestamp) VALUES (?, ?, ?, ?, ?);');
        $sth->execute(array($this->assessment->baseUri, $uri, $link, $code, date('Y-m-d H:i:s')));
    }
}