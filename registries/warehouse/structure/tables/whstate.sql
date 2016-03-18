CREATE TABLE whstate (
	whId int not null,
	articleId int not null,
	quantity decimal(18,6) not null default 0,
	total decimal(18,6) not null default 0
)