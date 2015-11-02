CREATE TABLE userstats (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  userId int,
  useripId int,
  useragentId int,
  typeId int not null default 1,
  dt datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  name varchar(100) DEFAULT NULL,
  memo text,
  status varchar(100) DEFAULT NULL,
  actorId int
) COMMENT='@system'