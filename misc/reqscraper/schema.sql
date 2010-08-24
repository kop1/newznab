DROP TABLE IF EXISTS `feed`;
CREATE TABLE `feed` 
(
	`ID` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`code` VARCHAR(50) NULL,
	`name` VARCHAR(255) NULL,
	`url` VARCHAR(1000) NOT NULL,
	`lastupdate` DATETIME NULL,
	`status` INT NOT NULL DEFAULT 1,
	PRIMARY KEY  (`ID`)
) ENGINE=MYISAM AUTO_INCREMENT=1 ;

CREATE INDEX ix_feed_code ON feed (code);

insert into feed (code, name, url, lastupdate) values ('tv', 'abteevee', 'http://abteevee.allfilled.com/rss.php', null);
insert into feed (code, name, url, lastupdate) values ('xxx', 'aberotica', 'http://aberotica.allfilled.com/rss.php', null);
insert into feed (code, name, url, lastupdate) values ('abgx', 'abgx', 'http://www.abgx.net/rss/abgw/reqs.rss', null);


DROP TABLE IF EXISTS `item`;
CREATE TABLE `item` 
(
	`ID` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`feedID` INT NOT NULL,
	`reqid` INT NOT NULL,
	`title` VARCHAR(255) NULL,
	`link` VARCHAR(1000) NULL,
	`description` VARCHAR(1000) NULL,
	`pubdate` DATETIME NOT NULL,
	`guid` VARCHAR(50) NULL,
	`adddate` DATETIME NOT NULL,
	PRIMARY KEY  (`ID`)
) ENGINE=MYISAM AUTO_INCREMENT=1 ;

CREATE INDEX ix_item_feedID ON item (feedID);
CREATE INDEX ix_item_reqid ON item (reqid);
CREATE UNIQUE INDEX ix_item_guid ON item (guid);
