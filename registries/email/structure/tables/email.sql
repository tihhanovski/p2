CREATE TABLE email (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  recipient varchar(100) NOT NULL DEFAULT '',
  sender varchar(100) NOT NULL DEFAULT '',
  body longtext,
  sent datetime,
  mdCreated datetime,
  mdUpdated datetime,
  mdCreatorId int,
  mdUpdaterId int,
  subject varchar(200) NOT NULL DEFAULT '',
  bcc varchar(200) NOT NULL DEFAULT '',
  signature longtext,
  attachment longtext,
  result text
)