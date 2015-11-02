CREATE TABLE webuser (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  uid varchar(32) NOT NULL,
  pwd varchar(255)  NOT NULL,
  sessionid varchar(255),
  state int NOT NULL DEFAULT 1,
  roles text NOT NULL,
  docSignature text NOT NULL,
  name varchar(100) ,
  email varchar(100) ,
  closed tinyint not null default 0,
  mdCreated datetime,
  mdUpdated datetime,
  mdCreatorId int,
  mdUpdaterId int
)