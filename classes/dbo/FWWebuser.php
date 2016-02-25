<?php
/*
 * Created on Mar 21, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	define("SYSTEM_USER_ID", 1);

	class FWWebuser extends WFWObject
	{
		protected $captionFields = array("uid");
		protected $closedField = "closed";  //closable

		public function afterClose()
		{
			app()->addUserStat($this, "user closed", "", "ok", USERSTATTYPE_MANIPULATION);
		}

		public function afterReopen()
		{
			app()->addUserStat($this, "user reopened", "", "ok", USERSTATTYPE_MANIPULATION);
		}

	    protected $validators = array(
	    	"uid" => array(VALIDATION_NOT_EMPTY, VALIDATION_UNIQUE),
	    	"name" => array(VALIDATION_NOT_EMPTY, VALIDATION_CLASS_METHOD),
	    	"email" => VALIDATION_NOT_EMPTY,
	    	"pwd" => array(VALIDATION_CLASS_METHOD),
	    );

	    function isSuperUser()
	    {
	    	return true;
	    }

	    function validateDocument()
	    {
	    	if($this->getIdValue() == 1)
	    		return true;
	    	return parent::validateDocument();
	    }

	    public function getPasswordError()
	    {
	    	foreach(app()->getPasswordValidators() as $validator)
	    		if($err = $validator->validate($this))
	    			return t($err);
	    	return "";
	    }

	    function validate_pwd()
	    {
	    	if($this->isNew())
	    	{
	    		if($err = $this->getPasswordError())
	    		{
		    		$this->addWarning(new Warning($err, "pwd", WARNING_ERROR));
		    		return false;
	    		}

	    	}
	    	return true;
	    }

	    function validate_name()
	    {
	    	$src = array("ü", "õ", "ä", "ö", "ž", "š");
	    	$dst = array("u", "o", "a", "o", "zh", "sh");
	    	if($this->isNew() && $this->uid == "")
	    	{
	    		$a = explode(" ", str_replace($src, $dst, mb_strtolower($this->name, INNER_CHARSET)));
	    		$b = array();
	    		foreach($a as $s)
	    			if($s != "")
	    				$b[] = $s;
	    		$this->setValue("uid", implode(".", $b), true);
	    	}
	    	return true;
	    }


	    private function updatePasswd()
	    {
	    	$tid = $this->getIdValue();
	    	if($tid && isset($this->pwd))
	    	{
	    		$sql = "update " . $this->__table .
						" set pwd = password('" . $this->escape($this->pwd) .
						"') where id = " . $tid;
				$this->getDatabaseConnection()->query($sql);
	    	}
	    }

	    function fetch()
	    {
	    	$ret = parent::fetch();
	    	unset($this->pwd);

	    	if($this->getIdValue() === SYSTEM_USER_ID)
	    		unset($this->captionFields);
	    	return $ret;
	    }

	    function insert()
	    {
	    	$this->state = 2;	//TODO Do we need it?
	    	$ret = parent::insert();
	    	$this->updatePasswd();

	    	//TODO rewrite and test it before uncomment
	    	/*
	    	$eml = app()->dbo("email");
	    	$emlText = $this->getNewAccountEmailBody();
	    	$this->updatePasswd();
	    	if(!defined("USER_NEW_SEND_EMAIL"))
	    		define("USER_NEW_SEND_EMAIL", false);
			if(USER_NEW_SEND_EMAIL)
	    		$eml->simpleSend($this->email, APP_TITLE . " : " . t("Your account is created"), $emlText);
	    	*/

	    	app()->addUserStat($this, "user created", "", "ok", USERSTATTYPE_MANIPULATION);

	    	return $ret;
	    }

	    function getNewAccountEmailBody()
	    {
	    	//TODO use proper template for email body
	    	$ret =
	    		t("Hello there") . " " . $this->name . "!<br/><br/>" .
	    		t("We created an account for you") . "<br/><br/>" .
	    		t("Your login name is") . ": " . $this->uid . "<br/>" .
	    		t("And password is") . ": " . $this->pwd . "<br/><br/>" .
	    		t("You could log in here") . "<br/>" .
	    		FULL_INSTANCE_ROOT . "<br/><br/>" .

	    		t("Please change your password as soon as possible. You can do it here") . ":<br/>" .
	    		FULL_INSTANCE_ROOT . "?registry=profile#tabPasswd" . "<br/><br/>" .

	    		t("Sincerelly yours") . "<br/>" . APP_TITLE;

	    	return $ret;
	    }

	    function update()
	    {
	    	$ou = app()->get($this->__table, $this->getIdValue());
	    	$chg = array();
	    	foreach (array("uid", "name", "email") as $f)
	    		if($this->$f != $ou->$f)
	    			$chg[] = $f . ": " . $ou->$f . " -> " . $this->$f;
	    	if($chgs = implode("\n", $chg))
	    		app()->addUserStat($this, "properties updated", $chgs, "ok", USERSTATTYPE_MANIPULATION);

	    	$ret = parent::update();
	    	$this->updatePasswd();
	    	return $ret;
	    }

	    function loadChildrenByClass($var, $cls, $tree)
	    {
	    	if($cls == "userrole")
	    	{
	    		$role = app()->dbo("role");
	    		if($role->find())
	    			while($role->fetch())
	    			{
	    				$rid = $role->getIdValue();
	    				$v1 = "role" . $rid;
	    				$ur = app()->dbo("userrole");
	    				$ur->userId = $this->getIdValue();
	    				$ur->roleId = $rid;
	    				$this->$v1 = $ur->find(true);
	    			}
	    	}
	    	else
	    		return parent::loadChildrenByClass($var, $cls, $tree);
	    }

	    function saveChildrenByClass($var, $cls, $tree)
	    {
	    	if($cls == "userrole")
	    	{
	    		$tid = $this->getIdValue();
	    		$roles = array();
	    		$role = app()->dbo("role");
	    		if($role->find())
	    			while($role->fetch())
	    			{
	    				$rid = $role->getIdValue();
	    				$v1 = "role" . $rid;
	    				$ur = app()->dbo("userrole");
	    				$ur->userId = $tid;
	    				$ur->roleId = $rid;
	    				$found = $ur->find(true);

	    				$checked = isset($this->$v1) && $this->$v1;
	    				if($checked)
	    					$roles[] = $role->name;

	    				if($found != $checked)
	    				{
	    					if($checked)
	    						$ur->insert();
	    					else
	    						$ur->delete();
	    				}
	    			}
	    		$r2 = implode(", ", $roles);
	    		if($r2 != $this->roles)
	    		{
	    			$oldRoles = $this->roles;
	    			$this->roles = $r2;
	    			$this->update();
	    			app()->addUserStat($this, "roles update", $oldRoles . " -> " . $this->roles, "ok", USERSTATTYPE_MANIPULATION);
	    		}
	    	}
	    	else
	    		return parent::saveChildrenByClass($var, $cls, $tree);
	    }
	}