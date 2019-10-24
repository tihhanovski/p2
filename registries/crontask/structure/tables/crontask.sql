CREATE TABLE crontask (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(100)  not null DEFAULT '',
  started datetime,
  finished datetime,
  log longtext
)