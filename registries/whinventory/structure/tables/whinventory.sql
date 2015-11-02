CREATE TABLE whinventory (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  dt date not null,
  nrprefix varchar(10) not null,
  nrsuffix varchar(10) not null,
  nr int not null,
  nrsequenceId int not null,
  fullNr varchar(50) not null,
  whId int not null,
  articlegroupId int,
  locked tinyint not null default 0,
  memo text not null default '',
  mdCreated datetime,
  mdUpdated datetime,
  mdCreatorId int,
  mdUpdaterId int
)