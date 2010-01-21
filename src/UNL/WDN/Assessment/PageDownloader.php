<?php
class UNL_WDN_Assessment_PageDownloader extends Spider_Downloader
{
    protected $assessment;

    function __construct(UNL_WDN_Assessment $assessment)
    {
        $this->assessment = $assessment;
    }

    public function download($uri)
    {
        try {
            return parent::download($uri);
        } catch(Exception $e) {
            echo 'Link to missing page. '.$uri;
        }
    }
}