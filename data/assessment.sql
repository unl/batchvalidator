CREATE TABLE IF NOT EXISTS `assessment` (
  `site_id` int(11) NOT NULL,
  `baseurl` varchar(255) NOT NULL,
  `url` varchar(1000) NOT NULL,
  `html_errors` varchar(10) DEFAULT 'unknown' NOT NULL,
  `accessibility_errors` varchar(10) DEFAULT 'unknown' NOT NULL,
  `template_dep` varchar(10) DEFAULT 'unknown' NOT NULL,
  `template_html` varchar(10) DEFAULT 'unknown' NOT NULL,
  `code` int(4) DEFAULT '0',
  `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`baseurl`,`site_id`,`url`(500)),
  KEY `valid` (`valid`),
  KEY `baseurl` (`baseurl`),
  KEY `url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1; 
