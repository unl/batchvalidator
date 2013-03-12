CREATE TABLE IF NOT EXISTS `assessment` (
  `baseurl` varchar(255) NOT NULL,
  `url` varchar(1000) NOT NULL,
  `html_errors` varchar(10) DEFAULT 'unknown' NOT NULL,
  `accessibility_errors` varchar(10) DEFAULT 'unknown' NOT NULL,
  `template_dep` varchar(10) DEFAULT 'unknown' NOT NULL,
  `template_html` varchar(10) DEFAULT 'unknown' NOT NULL,
  `timestamp` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`baseurl`,`url`(500)),
  CONSTRAINT FOREIGN KEY (`baseurl`) REFERENCES `assessment_runs`(`baseurl`) ON DELETE CASCADE,
  KEY `html_errors` (`html_errors`),
  KEY `accessibility_errors` (`accessibility_errors`),
  KEY `template_dep` (`template_dep`),
  KEY `template_html` (`template_html`),
  KEY `url` (`url`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1; 
