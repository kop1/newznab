
drop table if exists `binaries`;
CREATE TABLE `binaries` (
		`ID` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
		`name` VARCHAR(255) NOT NULL DEFAULT '',
		`fromname` VARCHAR(255) NOT NULL DEFAULT '',
		`date` DATETIME DEFAULT NULL,
		`xref` VARCHAR(255) NOT NULL DEFAULT '',
		`totalParts` INT(11) UNSIGNED NOT NULL DEFAULT '0',
		`groupID` INT(11) UNSIGNED NOT NULL DEFAULT '0',
		procstat INT DEFAULT 0,
		procattempts INT DEFAULT 0,
		filename VARCHAR(255) NULL,
		relpart INT DEFAULT 0,
		reltotalpart INT DEFAULT 0,
		relname VARCHAR(255) NULL,
		releaseID INT NULL,
		PRIMARY KEY  (`ID`),
		KEY `fromname` (`fromname`),
		KEY `date` (`date`),
		KEY `groupID` (`groupID`),
		FULLTEXT KEY `name` (`name`)
		) ENGINE=MYISAM AUTO_INCREMENT=1 ;

drop table if exists `releases`;
CREATE TABLE `releases` 
(
`ID` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
`name` VARCHAR(255) NOT NULL DEFAULT '',
`searchname` VARCHAR(255) NOT NULL DEFAULT '',	
`totalpart` INT DEFAULT 0,	
`groupID` INT UNSIGNED NOT NULL DEFAULT '0',
`size` INT(11) UNSIGNED NOT NULL DEFAULT '0',
`postdate` DATETIME DEFAULT NULL,
`adddate` DATETIME DEFAULT NULL,
guid varchar(50) not null,
`fromname` VARCHAR(255) NULL,
categoryID INT DEFAULT 0,
`grabs` INT UNSIGNED NOT NULL DEFAULT '0',
PRIMARY KEY  (`ID`),
FULLTEXT KEY `searchname` (`searchname`)
) ENGINE=MYISAM AUTO_INCREMENT=1 ;		

drop table if exists `groups`;
CREATE TABLE `groups` (
  `ID` INT(11) NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL DEFAULT '',
  `last_record` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `last_updated` DATETIME DEFAULT NULL,
  `active` TINYINT(1) NOT NULL DEFAULT '0',
  `description` VARCHAR(255) NULL DEFAULT '',
  `postcount` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY  (`ID`),
  KEY `active` (`active`)
) ENGINE=MYISAM AUTO_INCREMENT=1 ;

drop table if exists `parts`;
CREATE TABLE `parts` (
  `ID` INT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
  `binaryID` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `messageID` VARCHAR(255) NOT NULL DEFAULT '',
  `number` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `partnumber` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  `size` INT(11) UNSIGNED NOT NULL DEFAULT '0',
  PRIMARY KEY  (`ID`),
  KEY `binaryID` (`binaryID`)
) ENGINE=MYISAM AUTO_INCREMENT=1 ;

drop table if exists `category`;
CREATE TABLE category
(
ID INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
title VARCHAR(255) NOT NULL,
parentID INT NULL
) ENGINE=MYISAM AUTO_INCREMENT=1 ;

insert into category (ID, title) values (1, 'Console');
insert into category (ID, title) values (2, 'Movies');
insert into category (ID, title) values (3, 'Music');
insert into category (ID, title) values (4, 'PC');
insert into category (ID, title) values (5, 'TV');
insert into category (ID, title) values (6, 'XXX');
insert into category (ID, title) values (7, 'Other');
insert into category (ID, title, parentID) values (8, 'NDS', 1);
insert into category (ID, title, parentID) values (9, 'PSP', 1);
insert into category (ID, title, parentID) values (10, 'Wii', 1);
insert into category (ID, title, parentID) values (11, 'Xbox', 1);
insert into category (ID, title, parentID) values (12, 'Xbox 360', 1);
insert into category (ID, title, parentID) values (13, 'DVD', 2);
insert into category (ID, title, parentID) values (14, 'WMV-HD', 2);
insert into category (ID, title, parentID) values (15, 'XviD', 2);
insert into category (ID, title, parentID) values (16, 'x264', 2);
insert into category (ID, title, parentID) values (17, 'MP3', 3);
insert into category (ID, title, parentID) values (18, 'Video', 3);
insert into category (ID, title, parentID) values (19, '0day', 4);
insert into category (ID, title, parentID) values (20, 'ISO', 4);
insert into category (ID, title, parentID) values (21, 'Mac', 4);
insert into category (ID, title, parentID) values (22, 'DVD', 5);
insert into category (ID, title, parentID) values (23, 'H264', 5);
insert into category (ID, title, parentID) values (24, 'SWE', 5);
insert into category (ID, title, parentID) values (25, 'XviD', 5);
insert into category (ID, title, parentID) values (26, 'x264', 5);
insert into category (ID, title, parentID) values (27, 'DVD', 6);
insert into category (ID, title, parentID) values (28, 'WMV', 6);
insert into category (ID, title, parentID) values (29, 'XviD', 6);
insert into category (ID, title, parentID) values (30, 'x264', 6);
insert into category (ID, title, parentID) values (31, 'Misc', 7);

drop table if exists `users`;
CREATE TABLE `users` (
  `ID` INT(16) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` INT NOT NULL DEFAULT 1,
  `host` VARCHAR(15) NULL,
  `createddate` DATETIME DEFAULT NULL,
  PRIMARY KEY  (`ID`)
) ENGINE=MYISAM AUTO_INCREMENT=1 ;


drop table if exists `content`;
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
`status` INT NOT NULL,
ordinal INT NULL
) ENGINE=MYISAM AUTO_INCREMENT=1 ;

INSERT INTO content (title, body, contenttype, STATUS, metadescription, metakeywords, showinmenu) 
VALUES ('welcome to newznab', '<p>A usenet indexing community site thats easy to configure.</p><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>', 3, 1, '', '', 0);

INSERT INTO content (title, url, body, contenttype, STATUS, showinmenu, metadescription, metakeywords) 
VALUES ('example content', '/great/seo/content/page/', '<p>this is an example content page</p><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>', 2, 1, 1, '', '');

INSERT INTO content (title, url, body, contenttype, STATUS, showinmenu, metadescription, metakeywords) 
VALUES ('another example', '/another/great/seo/content/page/', '<p>this is another example content page</p><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p><p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>', 2, 1, 1, '', '');

drop table if exists `site`;
CREATE TABLE site (
id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
`code` VARCHAR(255) NOT NULL,
title VARCHAR(1000) NOT NULL,
strapline VARCHAR(1000) NOT NULL,
metatitle VARCHAR(1000) NOT NULL,
metadescription VARCHAR(1000) NOT NULL,
metakeywords VARCHAR(1000) NOT NULL,
footer VARCHAR(2000) NOT NULL,
email VARCHAR(1000) NOT NULL,
root VARCHAR(255) NOT NULL,
lastupdate DATETIME NOT NULL,
google_adsense_menu VARCHAR(255) NULL,
google_adsense_search VARCHAR(255) NULL,
google_adsense_sidepanel VARCHAR(255) NULL,
google_analytics_acc VARCHAR(255) NULL
) ENGINE=MYISAM AUTO_INCREMENT=1 ;

INSERT INTO site VALUES (NULL, 'newznab', 'Newznab', 'A great usenet indexer', 'Newznab - A great usenet indexer', 'Newznab a usenet indexing website with community features', 'usenet,nzbs,newznab,cms,community', 'newznab is designed to be a simple usenet indexing site that is easy to configure as a community website.', 'info@newznab.com', '/', NOW(), NULL, NULL, NULL, NULL);