CREATE TABLE IF NOT EXISTS `assessment` (
  `site_id` int(11) NOT NULL,
  `baseurl` varchar(255) NOT NULL,
  `url` varchar(1000) NOT NULL,
  `valid` enum('true','false','unknown') DEFAULT NULL,
  `code` int(4) DEFAULT '0',
  `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`baseurl`,`site_id`,`url`(500)),
  KEY `valid` (`valid`),
  KEY `baseurl` (`baseurl`),
  KEY `url` (`url`)
);
