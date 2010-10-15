ALTER TABLE site DROP COLUMN passwordstatus ;
alter table releases add passwordstatus int not null default 0;

