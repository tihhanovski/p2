create table salesinvoicerow
(
	id int not null auto_increment primary key,
	salesinvoiceId int not null,
	articleId int not null,
	quantity decimal(18,2) not null,
	priceNoVat decimal(18,2) not null,
	vatId int,
	vat decimal(18,2) not null default 0,
	priceWithVat decimal(18,2) not null,
	totalNoVat decimal(18,2) not null default 0,
	totalVat  decimal(18,2) not null,
	totalWithVat decimal(18,2) not null,
	memo text not null default ''
)