insert into language(id, code, name)
select id, code, name from languages;
drop table languages;