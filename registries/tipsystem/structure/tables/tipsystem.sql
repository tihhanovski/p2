CREATE TABLE tipsystem (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  page varchar(100) NOT NULL DEFAULT '',
  body longtext NOT NULL,
  active tinyint NOT NULL DEFAULT 1,
  mdCreated datetime DEFAULT NULL,
  mdUpdated datetime DEFAULT NULL,
  mdCreatorId int DEFAULT NULL,
  mdUpdaterId int DEFAULT NULL,
  control varchar(100) NOT NULL DEFAULT ''
)