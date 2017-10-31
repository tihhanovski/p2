CREATE TABLE unit (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(10) not NULL,
  memo text,
  closed tinyint NOT NULL DEFAULT 0,
  mdCreated datetime,
  mdUpdated datetime,
  mdCreatorId int,
  mdUpdaterId int
) COMMENT='Units list'
