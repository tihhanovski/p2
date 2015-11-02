CREATE TABLE userproperty (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  userId int NOT NULL,
  name varchar(50) not null,
  value text
) COMMENT='@system'