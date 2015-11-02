CREATE TABLE nrsequence (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(200) not NULL default '',
  prefix varchar(10) not null default '',
  suffix varchar(10) not null default '',
  startNr int not null default 1,
  startDt date,
  finishDt date,
  memo text not NULL default '',
  closed tinyint NOT NULL DEFAULT 0,
  mdCreated datetime DEFAULT NULL,
  mdUpdated datetime DEFAULT NULL,
  mdCreatorId int DEFAULT NULL,
  mdUpdaterId int DEFAULT NULL
)