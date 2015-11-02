CREATE TABLE article (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  unitId int default null,
  code varchar(50) not null,
  name varchar(100) not null,
  memo text not null,
  closed tinyint NOT NULL DEFAULT 0,
  typeId int NOT NULL DEFAULT 0,
  mdCreated datetime DEFAULT NULL,
  mdUpdated datetime DEFAULT NULL,
  mdCreatorId int DEFAULT NULL,
  mdUpdaterId int DEFAULT NULL
) COMMENT='Articles'