CREATE TABLE role (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(100) NOT NULL DEFAULT '',
  memo text,
  state int NOT NULL DEFAULT 1,
  mdCreated datetime,
  mdUpdated datetime,
  mdCreatorId int,
  mdUpdaterId int
)