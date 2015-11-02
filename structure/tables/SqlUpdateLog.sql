CREATE TABLE sqlupdatelog (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  file varchar(100),
  command text,
  result text
)