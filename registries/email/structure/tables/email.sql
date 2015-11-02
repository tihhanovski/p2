CREATE TABLE email (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  recipient varchar(100) NOT NULL DEFAULT '',
  sender varchar(100) NOT NULL DEFAULT '',
  body longtext NOT NULL default '',
  sent datetime DEFAULT NULL,
  mdCreated datetime DEFAULT NULL,
  mdUpdated datetime DEFAULT NULL,
  mdCreatorId int DEFAULT NULL,
  mdUpdaterId int DEFAULT NULL,
  subject varchar(200) NOT NULL DEFAULT '',
  bcc varchar(200) NOT NULL DEFAULT '',
  signature longtext NOT NULL default '',
  attachment longtext NOT NULL default '',
  result text NOT NULL default ''
)