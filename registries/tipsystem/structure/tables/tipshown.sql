CREATE TABLE tipshown (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  tipId int NOT NULL,
  userId int NOT NULL,
  mdCreated datetime DEFAULT NULL
)