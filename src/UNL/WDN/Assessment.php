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
    
    function reValidate()
    {
        $this->removeEntries();
        
        $plogger          = new UNL_WDN_Assessment_PageLogger($this);
        $vlogger          = new UNL_WDN_Assessment_ValidationLogger($this);
        $downloader       = new Spider_Downloader();
        $parser           = new Spider_Parser();
        $spider           = new Spider($downloader, $parser);
        
        $spider->addLogger($plogger);
        $spider->addLogger($vlogger);
        $spider->addUriFilter('Spider_AnchorFilter');
        $spider->addUriFilter('Spider_MailtoFilter');
        $spider->addUriFilter('UNL_WDN_Assessment_FileExtensionFilter');

        $spider->spider($this->baseUri);
    }
    
    function removeEntries()
    {
        $sth = $this->db->prepare('DELETE FROM assessment WHERE baseurl = ?');
        $sth->execute(array($this->baseUri));
    }
    
    function addUri($uri)
    {
        $sth = $this->db->prepare('INSERT INTO assessment (baseurl, url, timestamp) VALUES (?, ?, ?);');
        $sth->execute(array($this->baseUri, $uri, date('Y-m-d H:i:s')));
        
    }
    
    function setValidationResult($uri, $result)
    {
        $sth = $this->db->prepare('UPDATE assessment SET valid = ? WHERE baseurl = ? AND url = ?;');
        if ($result) {
            $result = 'true';
        } else {
            $result = 'false';
        }
        $sth->execute(array($result, $this->baseUri, $uri));
    }
    
    function getSubPages()
    {
        $sth = $this->db->prepare('SELECT * FROM assessment WHERE baseurl = ?;');
        $sth->execute(array($this->baseUri));
        return $sth->fetchAll();
    }
}