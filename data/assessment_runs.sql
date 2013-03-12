CREATE TABLE IF NOT EXISTS `assessment_runs` (
  `baseurl` varchar(255) NOT NULL,
  `date_started` datetime not null,
  `date_completed` datetime,
  PRIMARY KEY (`baseurl`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1; 
