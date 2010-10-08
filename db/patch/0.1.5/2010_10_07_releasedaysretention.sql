alter table site add column releaseretentiondays int not null default 0;
INSERT INTO `site` (`releaseretentiondays`) VALUES (0);