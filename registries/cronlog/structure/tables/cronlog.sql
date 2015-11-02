CREATE TABLE cronlog (
  id int NOT NULL AUTO_INCREMENT primary key,
  memo text not null default '',
  mdCreated datetime DEFAULT NULL,
  mdUpdated datetime DEFAULT NULL
)