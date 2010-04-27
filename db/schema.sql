
CREATE TABLE `binaries` (
  `ID` int(11) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `fromname` varchar(255) NOT NULL default '',
  `date` datetime default NULL,
  `xref` varchar(255) NOT NULL default '',
  `totalParts` int(11) unsigned NOT NULL default '0',
  `groupID` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `fromname` (`fromname`),
  KEY `date` (`date`),
  KEY `groupID` (`groupID`),
  FULLTEXT KEY `name` (`name`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;


CREATE TABLE `groups` (
  `ID` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `last_record` int(11) unsigned NOT NULL default '0',
  `last_updated` datetime default NULL,
  `active` tinyint(1) NOT NULL default '0',
  `description` varchar(255) NOT NULL default '',
  `postcount` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `active` (`active`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;


CREATE TABLE `parts` (
  `ID` int(16) unsigned NOT NULL auto_increment,
  `binaryID` int(11) unsigned NOT NULL default '0',
  `messageID` varchar(255) NOT NULL default '',
  `number` int(11) unsigned NOT NULL default '0',
  `partnumber` int(11) unsigned NOT NULL default '0',
  `size` int(11) unsigned NOT NULL default '0',
  PRIMARY KEY  (`ID`),
  KEY `binaryID` (`binaryID`)
) TYPE=MyISAM AUTO_INCREMENT=1 ;


CREATE TABLE CONTENT
(
ID INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
TITLE VARCHAR(255) NOT NULL,
URL VARCHAR(2000) NULL,
BODY TEXT NULL,
METADESCRIPTION VARCHAR(1000) NOT NULL,
METAKEYWORDS VARCHAR(1000) NOT NULL,
SITE INT NOT NULL,
CONTENTTYPE INT NOT NULL,
SHOWINMENU INT NOT NULL,
STATUS INT NOT NULL,
ORDINAL INT NULL
);
