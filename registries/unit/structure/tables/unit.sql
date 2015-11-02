CREATE TABLE unit (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(10) not NULL,
  memo text not NULL default '',
  closed tinyint NOT NULL DEFAULT 0,
  mdCreated datetime DEFAULT NULL,
  mdUpdated datetime DEFAULT NULL,
  mdCreatorId int DEFAULT NULL,
  mdUpdaterId int DEFAULT NULL
) COMMENT='Units list'
