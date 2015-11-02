CREATE TABLE userip (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  ip varchar(50) DEFAULT NULL,
  memo text,
  mdCreated datetime DEFAULT NULL,
  mdUpdated datetime DEFAULT NULL,
  mdCreatorId int DEFAULT NULL,
  mdUpdaterId int DEFAULT NULL
) COMMENT='@system'