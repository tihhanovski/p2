create table objcomment (
  id int not null auto_increment primary key,
  userId int not null,
  comment text,
  dt datetime not null,
  objreg varchar(50) not null,
  objId int not null
)