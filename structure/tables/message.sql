CREATE TABLE message (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  senderId int NOT NULL DEFAULT '0',
  recieverId int NOT NULL DEFAULT '0',
  caption varchar(200) DEFAULT NULL,
  body text,
  sent datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  recieved datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  replyToId int DEFAULT NULL,
  robject varchar(50) DEFAULT NULL
) COMMENT='@system\nSõnumid kasutajate vahel (seotud dokumentidega jms)'