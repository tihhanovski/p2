create table robjfile (
  id int not null auto_increment primary key,
  name varchar(100) not null,
  robj varchar(50) not null,
  rid int not null,
  memo text not null default '',
  mdCreated datetime,
  mdUpdated datetime,
  mdCreatorId int,
  mdUpdaterId int
)