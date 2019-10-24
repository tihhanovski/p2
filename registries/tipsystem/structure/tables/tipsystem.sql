CREATE TABLE tipsystem (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  page varchar(100) NOT NULL DEFAULT '',
  body longtext,
  active tinyint NOT NULL DEFAULT 1,
  mdCreated datetime,
  mdUpdated datetime,
  mdCreatorId int,
  mdUpdaterId int,
  control varchar(100) NOT NULL DEFAULT ''
)