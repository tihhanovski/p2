CREATE TABLE nrsequence (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(200) not NULL default '',
  prefix varchar(10) not null default '',
  suffix varchar(10) not null default '',
  startNr int not null default 1,
  startDt date,
  finishDt date,
  memo text,
  closed tinyint NOT NULL DEFAULT 0,
  mdCreated datetime,
  mdUpdated datetime,
  mdCreatorId int,
  mdUpdaterId int
)