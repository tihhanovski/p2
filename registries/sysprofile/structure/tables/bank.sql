CREATE TABLE bank (
id int not null auto_increment primary key,
userId varchar(200) not null,
name varchar(200) not null,
iban varchar(200) not null,
swift varchar(200) not null
) COMMENT='banks registry'
