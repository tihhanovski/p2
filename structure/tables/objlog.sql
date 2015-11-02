CREATE TABLE objlog (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  dt datetime NOT NULL,
  robject varchar(50) NOT NULL,
  val longtext NOT NULL,
  acn tinyint NOT NULL,
  acntype tinyint NOT NULL DEFAULT 0,
  userId int not null default 1
)