create table whinventoryrow(
	id int not null auto_increment primary key,
	whinventoryId int not null,
	articleId int not null,
	modifierId int not null default 1,
	quantity decimal(18,6),
	realQuantity decimal(18,6),
	cost decimal(18,6),
	memo text,
  	mdCreated datetime,
  	mdUpdated datetime,
  	mdCreatorId int,
  	mdUpdaterId int
)