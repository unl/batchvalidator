CREATE TABLE IF NOT EXISTS `logged_links` (
  `baseurl` varchar(255) NOT NULL,
  `url` varchar(1000) NOT NULL,
  `link_url` varchar(1000) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`baseurl`,`url`(500),`link_url`(500)),
  CONSTRAINT FOREIGN KEY (`baseurl`) REFERENCES `assessment_runs`(`baseurl`) ON DELETE CASCADE,
  KEY `url` (`url`),
  KEY `reason` (`reason`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE INDEX `logged_links_baseurl` ON `logged_links`(`baseurl`);