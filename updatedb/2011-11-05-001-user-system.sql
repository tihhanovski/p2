insert into webuser(ID, uid, pwd, state)values(1, 'system', 'xxx', 1), (2, 'admin', password('admin'), 2);
insert into role(ID, name, state)values(1, 'system', 2), (2, 'admin', 2);
insert into userrole(userID, roleID)values(1, 1), (2, 2);
insert into objectright(roleID, registryID, s, u, d) select 2 as roleID, id as registryID, 1, 1, 1 from robject;