<?php

	class GrantAllPrivileges extends CLITask
	{
		public function run()
		{
			$rn = readline("Role to grant (admin)");
			if($rn == "")
				$rn = "admin";

			$role = app()->dbo("role");
			$role->name = $rn;
			if($role->find(true))
			{
				if($rid = (int)$role->id)
					app()->query("insert into objectright(roleId, registryId, s, u, d, l)
							select $rid as roleid, id as registryId, 1 as s, 1 as u, 1 as d, 1 as l  from robject
							where id not in (select registryId from objectright where roleId = $rid)");
			}
		}
	}
