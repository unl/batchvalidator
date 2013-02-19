<?php
class UNL_WDN_Assessment
{
    public $baseUri;
    
    public $db;
    
    function __construct($baseUri, $db)
    {
        $this->baseUri = $baseUri;
        $this->db      = $db;
    }
    
    /**
     * 
     * @return Spider
     */
    protected function getSpider($loggers = array(), $filters = array())
    {
        $plogger          = new UNL_WDN_Assessment_PageLogger($this);
        $downloader       = new Spider_Downloader();
        $parser           = new Spider_Parser();
        $spider           = new Spider($downloader, $parser);
        
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
        
        //We will always want to display the page logger after the other loggers have executed.
        $spider->addLogger($plogger);
        
        return $spider;
    }
    
    function checkInvalid()
    {
        $vlogger = new UNL_WDN_Assessment_ValidateInvalidLogger($this);
        $slogger = new UNL_WDN_Assessment_ValidityStatusLogger($this);
        
        $spider  = $this->getSpider(array($vlogger, $slogger));
        
        $spider->spider($this->baseUri);
    }
    
    function reValidate()
    {
        $this->removeEntries();
        
        $vlogger = new UNL_WDN_Assessment_ValidationLogger($this);
        
        $spider  = $this->getSpider(array($vlogger));
        
        $spider->spider($this->baseUri);
    }
    
    function logPages()
    {
        $spider = $this->getSpider();
        
        $spider->spider($this->baseUri);
    }
    
    function checkLinks()
    {
        $checker = new UNL_WDN_Assessment_LinkChecker($this);
        
        $spider = $this->getSpider(array($checker));
        
        $spider->spider($this->baseUri);
    }
    
    function removeEntries()
    {
        $sth = $this->db->prepare('DELETE FROM assessment WHERE baseurl = ?');
        $sth->execute(array($this->baseUri));
    }
    
    function addUri($uri)
    {
        $sth = $this->db->prepare('INSERT INTO assessment (baseurl, url, valid, timestamp) VALUES (?, ?, ?, ?);');
        $sth->execute(array($this->baseUri, $uri, 'unknown', date('Y-m-d H:i:s')));
        
    }
    
    function setValidationResult($uri, $result)
    {
        //Add the uri if it doesn't already exist.
        $currentResult = $this->getValidityStatus($_GET['u']);
        if (empty($currentResult)) {
            $this->addUri($uri);
        }
        
        $sth = $this->db->prepare('UPDATE assessment SET valid = ?, timestamp = ? WHERE baseurl = ? AND url = ?;');
        if ($result) {
            $result = 'true';
        } else {
            $result = 'false';
        }
        $sth->execute(array($result, date('Y-m-d H:i:s'), $this->baseUri, $uri));
    }
    
    function getSubPages()
    {
        $sth = $this->db->prepare('SELECT * FROM assessment WHERE baseurl = ?;');
        $sth->execute(array($this->baseUri));
        return $sth->fetchAll();
    }
    
    function pageWasValid($uri)
    {
        if ($this->getValidityStatus($uri) == 'true') {
            return true;
        }
        return false;
    }
    
    function getValidityStatus($uri)
    {
        $sth = $this->db->prepare('SELECT valid FROM assessment WHERE baseurl = ? AND url = ?;');
        $sth->execute(array($this->baseUri, $uri));
        $result = $sth->fetch();
        return $result['valid'];
    }
}