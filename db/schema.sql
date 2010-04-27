-- Host: localhost
-- Generation Time: Jun 03, 2005 at 09:07 PM
-- Server version: 4.0.23
-- PHP Version: 4.3.9
-- 
-- Database: `nntp`
-- 

-- --------------------------------------------------------

-- 
-- Table structure for table `binaries`
-- 

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

-- --------------------------------------------------------

-- 
-- Table structure for table `groups`
-- 

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

-- --------------------------------------------------------

-- 
-- Table structure for table `parts`
-- 

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
