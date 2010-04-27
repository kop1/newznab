
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


CREATE TABLE content
(
id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
title VARCHAR(255) NOT NULL,
url VARCHAR(2000) NULL,
body TEXT NULL,
metadescription VARCHAR(1000) NOT NULL,
metakeywords VARCHAR(1000) NOT NULL,
contenttype INT NOT NULL,
showinmenu INT NOT NULL,
status INT NOT NULL,
ordinal INT NULL
);

insert into content (title, body, contenttype, status) 
values ('welcome to the homepage', '<p>this is the homepage text, its from the database</p>', 3, 1);

insert into content (title, url, body, contenttype, status, showinmenu) 
values ('example content', '/great/seo/content/page/', '<p>this is an example content page</p>', 2, 1, 1);

insert into content (title, url, body, contenttype, status, showinmenu) 
values ('next content', '/another/great/seo/content/page/', '<p>this is another example content page</p>', 2, 1, 1);


CREATE TABLE site (
id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
code VARCHAR(255) NOT NULL,
title VARCHAR(1000) NOT NULL,
strapline VARCHAR(1000) NOT NULL,
metatitle VARCHAR(1000) NOT NULL,
metadescription VARCHAR(1000) NOT NULL,
metakeywords VARCHAR(1000) NOT NULL,
footer VARCHAR(2000) NOT NULL,
email VARCHAR(1000) NOT NULL,
root VARCHAR(255) NOT NULL,
lastupdate DATETIME NOT NULL
);

alter table site add google_adsense_menu varchar(255) null;
alter table site add google_adsense_search varchar(255) null;
alter table site add google_adsense_sidepanel varchar(255) null;
alter table site add google_analytics_acc varchar(255) null;

insert into site values ('', 'newznab', 'Newznab', 'A great usenet indexer', 'meta title', 'metadesc', 'usenet,nzbs', 'intelligent footer text', 'info@newznab.com', '/', now(), null, null, null, null);