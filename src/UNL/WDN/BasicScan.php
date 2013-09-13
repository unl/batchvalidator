<?php
class UNL_WDN_BasicScan
{
    public $starttime = 0;
    public $endtime = 0;
    public $pages = array();
    public $baseurl;


    function __construct($baseurl)
    {
        $this->baseurl = $baseurl;
    }

    /**
     *
     * @param array $loggers
     * @param array $filters
     * @param array $options
     *
     * @return Spider
     */
    protected function getSpider($loggers = array(), $filters = array(), $options = array())
    {
        $downloader       = new UNL_WDN_Assessment_Downloader();
        $parser           = new Spider_Parser();
        $spider           = new Spider($downloader, $parser, $options);

        foreach ($loggers as $logger) {
            $spider->addLogger($logger);
        }

        foreach ($filters as $filter) {
            $spider->addUriFilter($filter);
        }

        //Add default filters
        $spider->addUriFilter('UNL_WDN_Assessment_Filter_FileExtension');

        return $spider;
    }

    /**
     * Will run a basic scan on a url
     *
     * @return bool
     */
    function scan()
    {
        $this->starttime = time();
        $this->pages = array();

        $uriLogger = new UNL_WDN_BasicScan_URILogger($this);

        $spider  = $this->getSpider(array($uriLogger),
            array(),
            array('page_limit'=>0,
                  'respect_robots_txt'=>false,
                  'crawl_404_pages'=>false));

        $spider->spider($this->baseurl);
        
        $this->endtime = time();

        return true;
    }
    
    function getStats()
    {
        $stats = array();
        $stats['baseurl']     = $this->baseurl;
        $stats['total_pages'] = count($this->pages);
        $stats['start_time']  = date('c', $this->starttime);
        $stats['end_time']    = date('c', $this->endtime);
        $stats['duration']    = $this->endtime - $this->starttime;
        $stats['pages']       = $this->pages;
        
        return $stats;
    }
    
    function logStats()
    {
        $tmpDir = UNL_WDN_Assessment::getTempDir();
        
        $stats = json_encode($this->getStats());
        
        file_put_contents($tmpDir . 'basic_scan_' . preg_replace("/[^a-z0-9.]+/i", "-", $this->baseurl) . '.json', $stats);
    }
    
    

}
