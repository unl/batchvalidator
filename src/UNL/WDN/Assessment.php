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
    
    function runValidation()
    {
        $validator        = new Services_W3C_HTMLValidator();
        $validator->validator_uri = 'http://validator.unl.edu/check';
        $logger           = new UNL_WDN_Assessment_ValidationLogger($validator, $this);
        $downloader       = new Spider_Downloader();
        $parser           = new Spider_Parser();
        $spider           = new Spider($downloader, $parser);
        
        $spider->addLogger($logger);
        $spider->addUriFilter('Spider_AnchorFilter');
        $spider->addUriFilter('Spider_MailtoFilter');
        $spider->addUriFilter('UNL_WDN_Assessment_FileExtensionFilter');
        
        $spider->spider('http://www.unl.edu/fwc/');
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
}