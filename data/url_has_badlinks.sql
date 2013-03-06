CREATE TABLE IF NOT EXISTS `url_has_badlinks` (
  `site_id` int(11) NOT NULL,
  `baseurl` varchar(255) NOT NULL,
  `url` varchar(1000) NOT NULL,
  `link_url` varchar(1000) NOT NULL,
  `code` int NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`baseurl`,`site_id`,`url`(500),`link_url`(500)),
  KEY `baseurl` (`baseurl`),
  KEY `url` (`url`),
  KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1; 
