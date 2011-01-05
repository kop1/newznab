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
  `year` varchar(4) NOT NULL,
  `genre` int(10) unsigned NULL,
  `language` VARCHAR(64) NOT NULL,
  `cover` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT '0',
  `createddate` datetime NOT NULL,
  `updateddate` datetime NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=MYISAM DEFAULT CHARSET latin1 COLLATE latin1_general_ci AUTO_INCREMENT=1 ;


