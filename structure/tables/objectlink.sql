CREATE TABLE objectlink (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  robject1 varchar(100) not null,
  robject2 varchar(100) not null,
  id1 int not null,
  id2 int not null,
  mdCreated datetime DEFAULT NULL,
  mdUpdated datetime DEFAULT NULL,
  mdCreatorId int DEFAULT NULL,
  mdUpdaterId int DEFAULT NULL
)