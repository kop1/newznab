
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
CONTENTTYPE INT NOT NULL,
SHOWINMENU INT NOT NULL,
STATUS INT NOT NULL,
ORDINAL INT NULL
);

insert into content (title, body, contenttype, status) 
values ('welcome to the homepage', '<p>this is the homepage text, its from the database</p>', 3, 1);

insert into content (title, url, body, contenttype, status, showinmenu) 
values ('example content', '/great/seo/content/page/', '<p>this is an example content page</p>', 2, 1, 1);

insert into content (title, url, body, contenttype, status, showinmenu) 
values ('next content', '/another/great/seo/content/page/', '<p>this is another example content page</p>', 2, 1, 1);


CREATE TABLE SITE (
ID INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
CODE VARCHAR(255) NOT NULL,
TITLE VARCHAR(1000) NOT NULL,
STRAPLINE VARCHAR(1000) NOT NULL,
METATITLE VARCHAR(1000) NOT NULL,
METADESCRIPTION VARCHAR(1000) NOT NULL,
METAKEYWORDS VARCHAR(1000) NOT NULL,
FOOTER VARCHAR(2000) NOT NULL,
EMAIL VARCHAR(1000) NOT NULL,
ROOT VARCHAR(255) NOT NULL,
LASTUPDATE DATETIME NOT NULL
);

alter table site add GOOGLE_ADSENSE_MENU varchar(255) null;
alter table site add GOOGLE_ADSENSE_SEARCH varchar(255) null;
alter table site add GOOGLE_ADSENSE_SIDEPANEL varchar(255) null;
alter table site add GOOGLE_ANALYTICS_ACC varchar(255) null;

insert into site values ('', 'newznab', 'Newznab', 'A great usenet indexer', 'meta title', 'metadesc', 'usenet,nzbs', 'intelligent footer text', 'info@newznab.com', '/', now(), null, null, null, null);