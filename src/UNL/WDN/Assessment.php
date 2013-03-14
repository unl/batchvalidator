<?php
class UNL_WDN_Assessment
{
    public $baseUri;
    
    public static $htmlValidatorURI = "http://validator.unl.edu/check";
    
    public static $spiderUserAgent = "UNL_WDN_Validator/2";
    
    public static $spiderPageLimit = 500;
    
    public static $maxConcurrentUserJobs = 5;

    public static $maxConcurrentAutoJobs = 3;

    public $db;
    
    function __construct($baseUri, $db)
    {
        $this->baseUri = $baseUri;
        $this->db      = $db;
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
        $spider->addUriFilter('UNL_WDN_Assessment_FileExtensionFilter');
        
        return $spider;
    }
    
    function checkInvalid()
    {
        $vlogger = new UNL_WDN_Assessment_ValidateInvalidLogger($this);
        
        $spider  = $this->getSpider(array($vlogger));
        
        $spider->spider($this->baseUri);
    }

    /**
     * Will recheck all metrics for every page
     * (save results to DB)
     * 
     * @param null $url - if not null, will run a scan on only the given url.
     */
    function check($url = null)
    {
        $limit = 1;
        
        //Scan the entire site.
        $updateCompletionDate = false;
        if ($url == null) {
            $url = $this->baseUri;
            $limit = self::$spiderPageLimit;
            $updateCompletionDate = true;
            $this->setRunning();
        }
        
        $uriLogger = new UNL_WDN_Assessment_URILogger($this);
        $validationLogger = new UNL_WDN_Assessment_HTMLValidationLogger($this);
        $templateHTMLLogger = new UNL_WDN_Assessment_TemplateHTMLLogger($this);
        $templateDEPLogger = new UNL_WDN_Assessment_TemplateDEPLogger($this);
        $linkChecker = new UNL_WDN_Assessment_LinkChecker($this);

        $spider  = $this->getSpider(array($uriLogger, $validationLogger, $templateHTMLLogger, $templateDEPLogger, $linkChecker), 
                                    array(),
                                    array('page_limit'=>$limit));

        $spider->spider($url);

        //Update the completion date if this is a full scan.
        if ($updateCompletionDate) {
            $this->setCompleted();
        }
    }
    
    function addRun($runType = 'user')
    {
        //Remove old entries
        $this->removeEntries();
        
        //Add a new run
        $sth = $this->db->prepare('INSERT INTO assessment_runs (baseurl, run_type, date_started) VALUES (?, ?, ?);');
        $sth->execute(array($this->baseUri, $runType, date('Y-m-d H:i:s')));
    }
    
    function setCompleted()
    {
        $sth = $this->db->prepare("UPDATE assessment_runs SET date_completed = ?, status='complete' WHERE baseurl = ?");

        $sth->execute(array(date('Y-m-d H:i:s'), $this->baseUri));
        
        $info = $this->getRunInformation();
        
        if (isset($info['run_type']) && $info['run_type'] == 'auto') {
            $this->emailStats();
        }
    }
    
    function setRunning()
    {
        $sth = $this->db->prepare("UPDATE assessment_runs SET status='running' WHERE baseurl = ?");

        $sth->execute(array($this->baseUri));
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
    
    function getStats($url = null)
    {
        $versions = self::getCurrentTemplateVersions();
        $run = $this->getRunInformation();
        
        $stats = array();
        $stats['site_title'] = $this->getTitle();
        $stats['last_scan'] = $this->getLastScanDate();
        $stats['total_pages'] = 0;
        $stats['total_html_errors'] = 0;
        $stats['total_accessibility_errors'] = 0;
        $stats['total_bad_links'] = 0;
        $stats['total_current_template_html'] = 0;
        $stats['total_current_template_dep'] = 0;
        $stats['current_template_html'] = $versions['html'];
        $stats['current_template_dep'] = $versions['dep'];
        $stats['error_scanning'] = false;
        $stats['status'] = false;
        
        if ($run) {
            $stats['status'] = $run['status'];
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
            
            $htmlCurrent = false;
            if ($page['template_html'] != 'unknown' && $page['template_html'] == $versions['html']) {
                $stats['total_current_template_html']++;
                $htmlCurrent = true;
            }
            
            $depCurrent = false;
            if ($page['template_dep'] != 'unknown' && $page['template_dep'] == $versions['dep']) {
                $stats['total_current_template_dep']++;
                $depCurrent = true;
            }

            $badLinks = array();
            foreach ($this->getBadLinksForPage($page['url']) as $link) {
                $badLinks[$link['code']][] = $link['link_url'];

                $stats['total_bad_links']++;
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
            
            $i++;
        }

        return $stats;
    }
    
    function getJSONstats($url = null)
    {
        return json_encode($this->getStats($url));
    }
}