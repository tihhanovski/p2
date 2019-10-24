CREATE TABLE article (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  unitId int,
  code varchar(50) not null,
  name varchar(100) not null,
  memo text,
  closed tinyint NOT NULL DEFAULT 0,
  typeId int NOT NULL DEFAULT 1,
  mdCreated datetime,
  mdUpdated datetime,
  mdCreatorId int,
  mdUpdaterId int
) COMMENT='Articles'