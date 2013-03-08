<?php
class UNL_WDN_Assessment_LinkChecker extends Spider_LoggerAbstract
{
    protected static $checked = array();
    
    public static $time_limit = 60;

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
        set_time_limit(self::$time_limit);
        
        $mcurl = curl_multi_init();
        $curl = array();
        $activeRequests = 0;
        while (count($links) + $activeRequests > 0) {
        
            while ($activeRequests < 50 && count($links) > 0) {
                $link = Spider::absolutePath(array_shift($links), $uri);
                
                if (filter_var($link, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED)
                    && !array_key_exists($link, self::$checked)) {
                    $curl[$link] = curl_init($link);
                    curl_setopt($curl[$link], CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl[$link], CURLOPT_CONNECTTIMEOUT, 5);
                    curl_setopt($curl[$link], CURLOPT_LOW_SPEED_LIMIT, 10);
                    curl_setopt($curl[$link], CURLOPT_LOW_SPEED_TIME, 5);
                    curl_setopt($curl[$link], CURLOPT_FOLLOWLOCATION, false);
                    curl_setopt($curl[$link], CURLOPT_USERAGENT, 'Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2) Gecko/20100115 Firefox/3.6');
                    curl_multi_add_handle($mcurl, $curl[$link]);
                    $activeRequests++;
                }
            }
        
            usleep(500);
            curl_multi_exec($mcurl, $running);
                
            while ($msg = curl_multi_info_read($mcurl, $msgCount)) {
            
                $finishedCurl = $msg['handle'];
                $info = curl_getinfo($finishedCurl);
                $activeRequests--;
                if ($info['http_code'] == 200) {
                    self::$checked[$info['url']] = true;
                    curl_multi_remove_handle($mcurl, $finishedCurl);
                    curl_close($finishedCurl);
                } else {
                    self::$checked[$info['url']] = false;
                    $this->addLink($info['url'], $info['http_code'], $uri);
                    continue;
                }

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
                && substr($link, 0, 11) != 'javascript:') {
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