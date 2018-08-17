create table robjfile (
  id int not null auto_increment primary key,
  name varchar(100) not null,
  robj varchar(50) not null,
  rid int not null,
  memo text,
  closed tinyint NOT NULL DEFAULT 0,
  mdCreated datetime,
  mdUpdated datetime,
  mdCreatorId int,
  mdUpdaterId int
)