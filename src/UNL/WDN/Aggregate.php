<?php
class UNL_WDN_Aggregate
{
    protected $db;
    
    function __construct($db)
    {
        $this->db = $db;
    }
    
    function getAverageRunTime()
    {
        $sth = $this->db->prepare("select avg(timestampdiff(MINUTE, date_started, date_completed)) as average_time from assessment_runs;");
        $sth->execute();
        $result = $sth->fetch();

        if (!isset($result['average_time'])) {
            return false;
        }

        return $result['average_time'];
    }
    
    function getTotalPages()
    {
        static $total;
        
        if ($total != null) {
            return $total;
        }
        
        $sth = $this->db->prepare("select count(*) as total from assessment;");
        $sth->execute();
        $result = $sth->fetch();

        if (!isset($result['total'])) {
            return false;
        }
        
        $total = $result['total'];

        return $total;
    }

    function getTotalSites()
    {
        $sth = $this->db->prepare("select count(*) as total from assessment_runs WHERE status != 'queued';");
        $sth->execute();
        $result = $sth->fetch();

        if (!isset($result['total'])) {
            return false;
        }

        return $result['total'];
    }
    
    function getHTMLErrors()
    {
        $stats = array();
        $stats['total'] = 0;
        $stats['max']['count'] = 0;
        $stats['average']['count'] = 0;
        $stats['max']['site'] = "";

        $sth = $this->db->prepare("select sum(html_errors) as total, assessment_runs.baseurl from assessment_runs LEFT JOIN assessment ON assessment_runs.baseurl = assessment.baseurl GROUP BY assessment_runs.baseurl;");
        $sth->execute();
        
        $totalSites = 0;
        while ($row = $sth->fetch()) {
            $stats['total'] += $row['total'];
            
            if ($row['total'] > $stats['max']['count']) {
                $stats['max']['count'] = $row['total'];
                $stats['max']['site'] = $row['baseurl'];
            }

            $totalSites++;
        }

        $stats['average']['count'] = round($stats['total']/$totalSites, 2);
        
        return $stats;
    }

    function getLinks($code)
    {
        $stats = array();
        $stats['total'] = 0;
        $stats['max']['count'] = 0;
        $stats['average']['count'] = 0;
        $stats['max']['site'] = "";

        $sth = $this->db->prepare("select count(url_has_badlinks.code) as total, assessment_runs.baseurl 
                                   from assessment_runs 
                                   LEFT JOIN url_has_badlinks ON assessment_runs.baseurl = url_has_badlinks.baseurl
                                   WHERE url_has_badlinks.code = ?
                                   GROUP BY assessment_runs.baseurl;");
        $sth->execute(array($code));

        $totalSites = 0;
        while ($row = $sth->fetch()) {
            $stats['total'] += $row['total'];

            if ($row['total'] > $stats['max']['count']) {
                $stats['max']['count'] = $row['total'];
                $stats['max']['site'] = $row['baseurl'];
            }

            $totalSites++;
        }

        $stats['average']['count'] = round($stats['total']/$totalSites, 2);

        return $stats;
    }

    function getTotalInCurrentTemplateHTML()
    {
        $versions = UNL_WDN_Assessment::getCurrentTemplateVersions();
        
        $sth = $this->db->prepare("select count(*) as total from assessment WHERE template_html = ?");
        $sth->execute(array($versions['html']));

        $row = $sth->fetch();
        
        return $row['total'];
    }

    function getTotalInCurrentTemplateDEP()
    {
        $versions = UNL_WDN_Assessment::getCurrentTemplateVersions();

        $sth = $this->db->prepare("select count(*) as total from assessment WHERE template_dep = ?");
        $sth->execute(array($versions['dep']));

        $row = $sth->fetch();

        return $row['total'];
    }

    function getPrimaryNav()
    {
        $stats = array();
        $stats['max']['count'] = 0;
        $stats['max']['site'] = "";
        $stats['average']['count'] = 0;

        $sth = $this->db->prepare("select avg(primary_nav_count) as total, assessment_runs.baseurl from assessment_runs LEFT JOIN assessment ON assessment_runs.baseurl = assessment.baseurl GROUP BY assessment_runs.baseurl;");
        $sth->execute();

        $totalSites = 0;
        $total = 0;
        while ($row = $sth->fetch()) {
            $total += $row['total'];

            if ($row['total'] > $stats['max']['count']) {
                $stats['max']['count'] = $row['total'];
                $stats['max']['site'] = $row['baseurl'];
            }

            $totalSites++;
        }

        $stats['average']['count'] = round($total/$totalSites, 2);

        return $stats;
    }

    function getGrid2006()
    {
        $stats = array();
        $stats['percent_pages_in_2006'] = 0;
        $stats['sites'] = array();

        $sth = $this->db->prepare("select count(*) as total from assessment WHERE grid_2006 = 1");
        $sth->execute();

        $row = $sth->fetch();

        $stats['percent_pages_in_2006'] = round(($row['total']/$this->getTotalPages())*100, 2);
        
        $sth = $this->db->prepare("select assessment_runs.baseurl from assessment_runs LEFT JOIN assessment ON assessment_runs.baseurl = assessment.baseurl WHERE grid_2006 = 1 GROUP BY assessment_runs.baseurl;");
        $sth->execute();

        while ($row = $sth->fetch()) {
            $stats['sites'][] = $row['baseurl'];
        }

        return $stats;
    }
    
    function getGANonAsync()
    {
        $stats = array();
        $stats['percent_pages_with_non-async'] = 0;
        $stats['sites'] = array();

        $sth = $this->db->prepare("select count(*) as total from assessment WHERE ga_non_async = 1");
        $sth->execute();

        $row = $sth->fetch();

        $stats['percent_pages_with_non'] = round(($row['total']/$this->getTotalPages())*100, 2);

        $sth = $this->db->prepare("select assessment_runs.baseurl from assessment_runs LEFT JOIN assessment ON assessment_runs.baseurl = assessment.baseurl WHERE ga_non_async = 1 GROUP BY assessment_runs.baseurl;");
        $sth->execute();

        while ($row = $sth->fetch()) {
            $stats['sites'][] = $row['baseurl'];
        }

        return $stats;
    }

    function getGASetallowHash()
    {
        $stats = array();
        $stats['percent_pages_with_setallowhash'] = 0;
        $stats['sites'] = array();

        $sth = $this->db->prepare("select count(*) as total from assessment WHERE ga_setallowhash = 1");
        $sth->execute();

        $row = $sth->fetch();

        $stats['percent_pages_with_setallowhash'] = round(($row['total']/$this->getTotalPages())*100, 2);

        $sth = $this->db->prepare("select assessment_runs.baseurl from assessment_runs LEFT JOIN assessment ON assessment_runs.baseurl = assessment.baseurl WHERE ga_setallowhash = 1 GROUP BY assessment_runs.baseurl;");
        $sth->execute();

        while ($row = $sth->fetch()) {
            $stats['sites'][] = $row['baseurl'];
        }

        return $stats;
    }
    
    function getStats()
    {
        $stats = array();
        $stats['total_pages'] = $this->getTotalPages();
        $stats['total_sites'] = $this->getTotalSites();
        
        $stats['average_run_time'] = $this->getAverageRunTime();

        $stats['html_errors'] = $this->getHTMLErrors();
        
        $stats['percent_current_template_html'] = round(($this->getTotalInCurrentTemplateHTML() / $stats['total_pages'])*100, 2);
        $stats['percent_current_template_dep'] = round(($this->getTotalInCurrentTemplateDEP() / $stats['total_pages'])*100, 2);
        
        $stats['primary_nav'] = $this->getPrimaryNav();
        
        $stats['grid']['2006'] = $this->getGrid2006();
        
        $stats['ga'] = array();
        $stats['ga']['non-async'] = $this->getGANonAsync();
        $stats['ga']['setallowhash'] = $this->getGASetallowHash();
        
        $stats['links'] = array();
        $stats['links']['404'] = $this->getLinks(404);
        $stats['links']['301'] = $this->getLinks(301);
        
        return $stats;
    }

}