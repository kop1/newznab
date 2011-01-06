alter table releases add `musicinfoID` INT NULL;

DROP TABLE IF EXISTS `musicgenre`;
CREATE TABLE `musicgenre` 
(
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL
) ENGINE=MYISAM DEFAULT CHARSET latin1 COLLATE latin1_general_ci AUTO_INCREMENT=1 ;

DROP TABLE IF EXISTS `musicinfo`;
CREATE TABLE `musicinfo` 
(
 	`ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(128) NOT NULL,
  `asin` varchar(128) NULL,
  `url` varchar(1000) NULL,
  `salesrank` int(10) unsigned NULL,
  `artist` varchar(255) NULL,
  `publisher` varchar(255) NULL,
  `releasedate` varchar(255) NULL,
  `review` varchar(2000) NULL,
  `year` varchar(4) NOT NULL,
  `musicgenreID` int(10) unsigned NULL,
  `tracks` varchar(2000) NULL,
  `cover` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
  `createddate` datetime NOT NULL,
  `updateddate` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MYISAM DEFAULT CHARSET latin1 COLLATE latin1_general_ci AUTO_INCREMENT=1 ;


