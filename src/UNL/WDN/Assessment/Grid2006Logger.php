<?php
class UNL_WDN_Assessment_Grid2006Logger extends Spider_LoggerAbstract
{
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
        $contains = (int)$this->contains2006Grid($xpath);

        $this->setGrid2006($uri, $contains);
    }

    public function contains2006Grid(DOMXPath $xpath)
    {
        $nodes = $xpath->query(
            '//xhtml:div[@id="maincontent"]//*[contains(concat(" ", normalize-space(@class), " "), " col ") 
                                               or contains(concat(" ", normalize-space(@class), " "), " two_col ")
                                               or contains(concat(" ", normalize-space(@class), " "), " three_col ")
                                               or contains(concat(" ", normalize-space(@class), " "), " four_col ")
                                               ]/@class'
        );
        
        return (bool)$nodes->length;
    }

    function setGrid2006($uri, $result)
    {
        $sth = $this->assessment->db->prepare('UPDATE assessment SET grid_2006 = ?, timestamp = ? WHERE baseurl = ? AND url = ?;');

        $sth->execute(array($result, date('Y-m-d H:i:s'), $this->assessment->baseUri, $uri));
    }
}
