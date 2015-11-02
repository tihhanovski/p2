CREATE TABLE vat (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name varchar(100) not null,
  pct decimal(18,2) not null,
  memo text not null default '',
  mdCreated datetime,
  mdUpdated datetime,
  mdCreatorId int,
  mdUpdaterId int,
  closed tinyint not null default 0
) ENGINE=InnoDB