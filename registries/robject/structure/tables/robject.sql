CREATE TABLE robject (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(100) NOT NULL,
  state int NOT NULL DEFAULT 1,
  typeId int NOT NULL DEFAULT 1,
  module int NOT NULL DEFAULT 1,
  menupartId int NOT NULL DEFAULT 1,
  pos int NOT NULL DEFAULT 0
)