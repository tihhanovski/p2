CREATE TABLE softwareissue (
  id int NOT NULL AUTO_INCREMENT primary key,
  caption varchar(200) not null default '',
  memo text not null default '',
  resolution text  not null default '',
  priority int not null default 0, 
  state varchar(100) not null default '',
  mdCreated datetime DEFAULT NULL,
  mdUpdated datetime DEFAULT NULL,
  mdCreatorI int,
  mdUpdaterId int,
  closed tinyint not null default 0,
  cc varchar(255) not null default '',
  ownerId int,
  deadline date default null
)