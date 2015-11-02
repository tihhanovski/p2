CREATE TABLE useragent (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(100) DEFAULT NULL,
  rawdata text DEFAULT NULL,
  memo text,
  mdCreated datetime DEFAULT NULL,
  mdUpdated datetime DEFAULT NULL,
  mdCreatorId int DEFAULT NULL,
  mdUpdaterId int DEFAULT NULL  
) COMMENT='@system'