<?php
class UNL_WDN_Assessment
{
    public $baseUri;
    
    public $starttime = 0;
    
    public static $htmlValidatorURI = "http://validator.unl.edu/check";
    
    public static $spiderUserAgent = "UNL_WDN_Validator/2";
    
    public static $spiderPageLimit = 500;
    
    public static $maxConcurrentUserJobs = 1;

    public static $maxConcurrentAutoJobs = 1;
    
    public static $maxQueuedAutoJobs = 2;
    
    public static $timeout = 3600; //60min (3600 seconds)
    
    public static $restrictedURIs = array('http://events.unl.edu/');
    
    public static $tempDir = "";

    public $db;
    
    function __construct($baseUri, $db)
    {
        $this->baseUri = $baseUri;
        $this->db      = $db;
        
        if (empty(self::$tempDir)) {
            self::$tempDir = dirname(dirname(dirname(dirname(__FILE__)))) . "/tmp/";
        }
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
        $downloader       = new Spider_Downloader();    
        $parser           = new Spider_Parser();
        $spider           = new Spider($downloader, $parser, $options);
        
        foreach ($loggers as $logger) {
            $spider->addLogger($logger);
        }

        foreach ($filters as $filter) {
            $spider->addUriFilter($filter);
        }
        
        //Add default filters
        $spider->addUriFilter('Spider_AnchorFilter');
        $spider->addUriFilter('Spider_MailtoFilter');
        $spider->addUriFilter('UNL_WDN_Assessment_Filter_FileExtension');
        $spider->addUriFilter('UNL_WDN_Assessment_Filter_Protocol');
        
        return $spider;
    }
    
    function checkInvalid()
    {
        $vlogger = new UNL_WDN_Assessment_ValidateInvalidLogger($this);
        
        $spider  = $this->getSpider(array($vlogger));
        
        $spider->spider($this->baseUri);
    }
    
    function isRestricted()
    {
        foreach (self::$restrictedURIs as $uri) {
            if (stripos($this->baseUri, $uri) === 0) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Will recheck all metrics for every page
     * (save results to DB)
     *
     * @param null $url - if not null, will run a scan on only the given url.
     * @param int  $pageLimit
     * @return bool
     */
    function check($url = null, $pageLimit = 0)
    {
        //Don't check restricted URIs
        if ($this->isRestricted()) {
            $this->setRunStatus('restricted', date('Y-m-d H:i:s'));
            return false;
        }

        //Don't check if the home page is not found.
        if ($this->getHTTPStatusCode($this->baseUri) == '404') {
            $this->setCompleted();
            return false;
        }
        
        $this->starttime = time();
        
        //Scan the entire site.
        $updateCompletionDate = false;
        if ($url == null) {
            $url = $this->baseUri;
            $updateCompletionDate = true;

            //Don't allow for negative page limits
            if ($pageLimit <= 0) {
                $pageLimit = self::$spiderPageLimit;
            }
            
            $this->setRunning();
        } else {
            $pageLimit = 1;
        }
        
        $uriLogger = new UNL_WDN_Assessment_URILogger($this);
        $validationLogger = new UNL_WDN_Assessment_HTMLValidationLogger($this);
        $templateHTMLLogger = new UNL_WDN_Assessment_TemplateHTMLLogger($this);
        $templateDEPLogger = new UNL_WDN_Assessment_TemplateDEPLogger($this);
        $linkChecker = new UNL_WDN_Assessment_LinkChecker($this);
        $navigationLogger = new UNL_WDN_Assessment_PrimaryNavigationLogger($this);
        $grid2006 = new UNL_WDN_Assessment_Grid2006Logger($this);
        $gaAsync = new UNL_WDN_Assessment_GANonAsyncLogger($this);
        $gaAllowHash = new UNL_WDN_Assessment_GASetAllowHashLogger($this);

        $spider  = $this->getSpider(array($uriLogger, $validationLogger, $templateHTMLLogger, $templateDEPLogger, $linkChecker, $navigationLogger, $grid2006, $gaAllowHash, $gaAsync), 
                                    array(),
                                    array('page_limit'=>$pageLimit));

        $spider->spider($url);

        //Update the completion date if this is a full scan.
        if ($updateCompletionDate) {
            $this->setCompleted();
        }
        
        return true;
    }
    
    function getHTTPStatusCode($url)
    {
        $curl = curl_init($url);
        
        curl_setopt($curl, CURLOPT_NOBODY, true);
        
        curl_exec($curl);
        
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        
        curl_close($curl);
        
        return $http_status;
    }
    
    function addRun($runType = 'user', $pageLimit = 0)
    {
        //Remove old entries
        $this->removeEntries();
        
        //Add a new run
        $sth = $this->db->prepare('INSERT INTO assessment_runs (baseurl, run_type, date_started, page_limit) VALUES (?, ?, ?, ?);');
        $sth->execute(array($this->baseUri, $runType, date('Y-m-d H:i:s'), $pageLimit));
    }
    
    function setCompleted()
    {
        $this->setRunStatus('complete', date('Y-m-d H:i:s'));

        $info = $this->getRunInformation();
        
        if (isset($info['run_type']) && $info['run_type'] == 'auto') {
            $this->emailStats();
        }
        
        if (isset($info['contact_email'])) {
            $this->emailStats($info['contact_email']);
        }
    }
    
    function setRunning()
    {
        $sth = $this->db->prepare("UPDATE assessment_runs SET date_started = ?, status='running' WHERE baseurl = ?");

        $sth->execute(array(date('Y-m-d H:i:s'), $this->baseUri));
    }

    function setRunStatus($status, $dateCompleted = null)
    {
        $sth = $this->db->prepare("UPDATE assessment_runs SET status=?, date_completed=? WHERE baseurl = ?");

        $sth->execute(array($status, $dateCompleted, $this->baseUri));
    }

    function setRunContactEmail($email)
    {
        $sth = $this->db->prepare("UPDATE assessment_runs SET contact_email=? WHERE baseurl = ? and run_type='user' LIMIT 1");

        $sth->execute(array($email, $this->baseUri));
    }
    
    function removeEntries()
    {
        //Remove assessment_runs entries
        $sth = $this->db->prepare('DELETE FROM assessment_runs WHERE baseurl = ?');
        $sth->execute(array($this->baseUri));
        
        //Remove assessment entries
        $sth = $this->db->prepare('DELETE FROM assessment WHERE baseurl = ?');
        $sth->execute(array($this->baseUri));
        
        //remove url_has_badlinks entries
        $sth = $this->db->prepare('DELETE FROM url_has_badlinks WHERE baseurl = ?');
        $sth->execute(array($this->baseUri));
        
        //remove old cache file
        if (file_exists($this->getCacheFileName()) {
            unlink($this->getCacheFileName());
        }
    }
    
    function getSubPages()
    {
        $sth = $this->db->prepare('SELECT * FROM assessment WHERE baseurl = ?;');
        $sth->execute(array($this->baseUri));
        return $sth->fetchAll();
    }
    
    function getBadLinksForPage($url)
    {
        $sth = $this->db->prepare('SELECT * FROM url_has_badlinks WHERE url = ?;');
        $sth->execute(array($url));
        return $sth->fetchAll();
    }
    
    function pageWasValid($uri)
    {
        if ($this->getValidityStatus($uri) == '0') {
            return true;
        }
        return false;
    }
    
    function getValidityStatus($uri)
    {
        $sth = $this->db->prepare('SELECT html_errors FROM assessment WHERE baseurl = ? AND url = ?;');
        $sth->execute(array($this->baseUri, $uri));
        $result = $sth->fetch();
        return $result['html_errors'];
    }

    function getRunInformation()
    {
        $sth = $this->db->prepare('SELECT * FROM assessment_runs WHERE baseurl = ?');
        $sth->execute(array($this->baseUri));
        $result = $sth->fetch();
        
        return $result;
    }

    function getTitle()
    {
        $page = @file_get_contents($this->baseUri);
        
        if (strlen($page)) {
            $results = array();
            
            preg_match("/\<title\>(.*)\<\/title\>/", $page, $results);
            
            if (isset($results[1])) {
                return $results[1];
            }
        }
        
        return "unknown";
    }
    
    function getLastScanDate()
    {
        $sth = $this->db->prepare('SELECT date_completed as scan_date FROM assessment_runs WHERE baseurl = ?');
        $sth->execute(array($this->baseUri));
        $result = $sth->fetch();
        
        if (isset($result['scan_date'])) {
            return $result['scan_date'];
        }
        
        return false;
    }
    
    public static function getCurrentTemplateVersions()
    {
        if (!$json = file_get_contents(dirname(__FILE__) . "/../../../tmp/templateversions.json")) {
            throw new Exception("tmp/templateversions.json does not exist.  Please run scripts/getLatestTemplateVersions.php");
        }
        
        return json_decode($json, true);
    }

    function emailStats($email = null)
    {
        $mailer = new UNL_WDN_Assessment_Mailer($this);
        $mailer->mail($email);
    }
    
    function getQueuePosition($runInformation = array())
    {
        if (empty($runInformation)) {
            $runInformation = $this->getRunInformation();
        }
        
        if (!$runInformation) {
            return "unknown";
        }
        
        $sql = "SELECT count(*) as queue_position FROM assessment_runs WHERE status = 'queued' AND run_type = ? AND date_started <= ?";

        $sth = $this->db->prepare($sql);
        $sth->execute(array($runInformation['run_type'], $runInformation['date_started']));
        $result = $sth->fetch();
        
        return $result['queue_position'];
    }
    
    function getStats($url = null)
    {
        $tmpFileName = $this->getCacheFileName();
        
        if (file_exists($tmpFileName)) {
            return unserialize(file_get_contents($tmpFileName));
        }
        
        $versions = self::getCurrentTemplateVersions();
        $run = $this->getRunInformation();
        
        $stats = array();
        $stats['site_title'] = $this->getTitle();
        $stats['last_scan'] = $this->getLastScanDate();
        $stats['total_pages'] = 0;
        $stats['total_html_errors'] = 0;
        $stats['total_accessibility_errors'] = 0;
        $stats['total_current_template_html'] = 0;
        $stats['total_current_template_dep'] = 0;
        $stats['current_template_html'] = $versions['html'];
        $stats['current_template_dep'] = $versions['dep'];
        $stats['error_scanning'] = false;
        $stats['status'] = false;
        $stats['contact_email'] = false;
        $stats['page_limit'] = 0;
        $stats['queue_position'] = 0;
        $stats['max_primary_nav_count'] = 0;
        $stats['total_grid_2006_pages'] = 0;
        $stats['total_ga_non_async_pages'] = 0;
        $stats['total_ga_setallowhash_pages'] = 0;

        $stats['total_bad_links'] = array();
        foreach (UNL_WDN_Assessment_LinkChecker::$loggedStatusCodes as $code) {
            $stats['total_bad_links'][$code] = 0;
        }
        
        if ($run) {
            $stats['status'] = $run['status'];
        }

        if ($run && $stats['status'] == 'queued') {
            $stats['queue_position'] = $this->getQueuePosition($run);
        }
        
        if ($run && isset($run['contact_email'])) {
            $stats['contact_email'] = true;
        }

        if ($run && isset($run['page_limit'])) {
            $stats['page_limit'] = (int)$run['page_limit'];
        }
        
        $stats['pages'] = array();
        
        $i = 0;
        foreach ($this->getSubPages() as $page) {
            if ($page['html_errors'] != 'unknown') {
                $stats['total_html_errors'] += $page['html_errors'];
            }
            
            if (!$page['scannable']) {
                $stats['error_scanning'] = true;
            }

            if ($page['accessibility_errors'] != 'unknown') {
                $stats['total_accessibility_errors'] += $page['accessibility_errors'];
            }

            if ($page['primary_nav_count'] > $stats['max_primary_nav_count']) {
                $stats['max_primary_nav_count'] = (int)$page['primary_nav_count'];
            }
            
            if ($page['grid_2006']) {
                $stats['total_grid_2006_pages']++;
            }

            if ($page['ga_non_async']) {
                $stats['total_ga_non_async_pages']++;
            }

            if ($page['ga_setallowhash']) {
                $stats['total_ga_setallowhash_pages']++;
            }
            
            $htmlCurrent = false;
            if ($page['template_html'] != 'unknown' && $this->isCurrentVersion($page['template_html'], $versions['html'])) {
                $stats['total_current_template_html']++;
                $htmlCurrent = true;
            }
            
            $depCurrent = false;
            if ($page['template_dep'] != 'unknown' && $this->isCurrentVersion($page['template_dep'], $versions['dep'])) {
                $stats['total_current_template_dep']++;
                $depCurrent = true;
            }

            $badLinks = array();
            foreach (UNL_WDN_Assessment_LinkChecker::$loggedStatusCodes as $code) {
                $badLinks[$code]['total'] = 0;
                $badLinks[$code]['links'] = array();
            }
            foreach ($this->getBadLinksForPage($page['url']) as $link) {
                $badLinks[$link['code']]['links'][] = $link['link_url'];
                $badLinks[$link['code']]['total']++;

                $stats['total_bad_links'][$link['code']]++;
            }

            $stats['total_pages']++;
            
            if ($url != null && $page['url'] != $url) {
                continue;
            }
            
            $stats['pages'][$i]['page'] = $page['url'];
            $stats['pages'][$i]['html_errors'] = $page['html_errors'];
            $stats['pages'][$i]['accessibility_errors'] = $page['accessibility_errors'];
            $stats['pages'][$i]['template_dep']['version'] = $page['template_dep'];
            $stats['pages'][$i]['template_dep']['current'] = $depCurrent;
            $stats['pages'][$i]['template_html']['version'] = $page['template_html'];
            $stats['pages'][$i]['template_html']['current'] = $htmlCurrent;
            $stats['pages'][$i]['bad_links'] = $badLinks;
            $stats['pages'][$i]['scannable'] = (bool)$page['scannable'];
            $stats['pages'][$i]['primary_nav_count'] = $page['primary_nav_count'];
            $stats['pages'][$i]['grid_2006'] = (bool)$page['grid_2006'];
            $stats['pages'][$i]['ga_non_async'] = (bool)$page['ga_setallowhash'];
            $stats['pages'][$i]['ga_setallowhash'] = (bool)$page['ga_setallowhash'];
            
            $i++;
        }
        
        //save cache if we are done.
        if (!in_array($stats['status'], array('queued', 'running'))) {
            file_put_contents($tmpFileName, serialize($stats));
        }

        return $stats;
    }
    
    function getCacheFileName()
    {
        return self::$tempDir . "site_" . md5($this->baseUri);
    }
    
    function isCurrentVersion($currentVersion, $version)
    {
        if ($currentVersion == $version) {
            return true;
        }
        
        return false;
    }
    
    function getJSONstats($url = null)
    {
        return json_encode($this->getStats($url));
    }
}
