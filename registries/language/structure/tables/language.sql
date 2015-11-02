CREATE TABLE language (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  code char(2)  NOT NULL DEFAULT '',
  name varchar(100)  NOT NULL DEFAULT '',
  mdCreated datetime DEFAULT NULL,
  mdUpdated datetime DEFAULT NULL,
  mdCreatorId int DEFAULT NULL,
  mdUpdaterId int DEFAULT NULL,
  closed tinyint not null default 0
)