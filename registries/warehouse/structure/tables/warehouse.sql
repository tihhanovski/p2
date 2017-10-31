CREATE TABLE warehouse (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  code varchar(50) not NULL,
  name varchar(200) not NULL default '',
  memo text,
  closed tinyint NOT NULL DEFAULT 0,
  mdCreated datetime,
  mdUpdated datetime,
  mdCreatorId int,
  mdUpdaterId int
)