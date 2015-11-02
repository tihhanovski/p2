CREATE TABLE warehouse (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  code varchar(50) not NULL,
  name varchar(200) not NULL default '',
  memo text not NULL default '',
  closed tinyint NOT NULL DEFAULT 0,
  mdCreated datetime DEFAULT NULL,
  mdUpdated datetime DEFAULT NULL,
  mdCreatorId int DEFAULT NULL,
  mdUpdaterId int DEFAULT NULL
)