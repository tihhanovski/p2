<?php
/*
 * Created on Mar 21, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

define("SYSTEM_ROLE_ID", 1);

class FWRole extends WFWObject
{
	protected $captionFields = array("name");
	protected $availableRights = array("s", "u", "d", "l");

    protected $validators = array(
    	"name" => array(VALIDATION_NOT_EMPTY, VALIDATION_UNIQUE),
    );

	protected $formats = array(
    	"mdCreated" => 		FORMAT_DATETIME,
    	"mdUpdated" => 		FORMAT_DATETIME,
    );

    function isEditable()
    {
    	if($this->getIdValue() == SYSTEM_ROLE_ID)	//system role not editable
    		return false;
    	return parent::isEditable();
    }

	function grantRobject($id, $b)
	{
		$val = $b ? 1 : 0;
		if($id)
		{
			$obj = app()->dbo("robject");
			$obj->setIdValue($id);
			if($obj->find(true))
				foreach($this->availableRights as $v)
					$this->setValue("obj" . $obj->getIdValue() . $v, $val);
		}
		return true;
	}

	function grantGlobalPrivilege($id, $b)
	{
		$val = $b ? 1 : 0;
		if($id)
		{
			$obj = app()->dbo("robject");
			if($obj->find())
				while($obj->fetch())
					$this->setValue("obj" . $obj->getIdValue() . $id, $val);
		}
	}

	    function loadChildrenByClass($var, $cls, $tree)
	    {
	    	if($cls == "objectright")
	    	{
	    		$obj = app()->dbo("robject");
	    		if($obj->find())
	    			while($obj->fetch())
	    			{
	    				$oid = $obj->getIdValue();
	    				$sel = "obj" . $oid . "s";
	    				$upd = "obj" . $oid . "u";
	    				$del = "obj" . $oid . "d";
	    				$lck = "obj" . $oid . "l";

	    				$rgt = app()->dbo("objectright");
	    				$rgt->roleId = $this->getIdValue();
	    				$rgt->registryId = $oid;
	    				$rgt->find(true);
	    				$this->$sel = $rgt->s;
	    				$this->$upd = $rgt->u;
	    				$this->$del = $rgt->d;
	    				$this->$lck = $rgt->l;
	    			}
	    	}
	    	else
	    		return parent::loadChildrenByClass($var, $cls, $tree);
	    }

	    function saveChildrenByClass($var, $cls, $tree)
	    {
	    	if($cls == "objectright")
	    	{
	    		$obj = app()->dbo("robject");
	    		if($obj->find())
	    			while($obj->fetch())
	    			{
	    				$oid = $obj->getIdValue();
	    				$sel = "obj" . $oid . "s";
	    				$upd = "obj" . $oid . "u";
	    				$del = "obj" . $oid . "d";
	    				$lck = "obj" . $oid . "l";

	    				$rgt = app()->dbo("objectright");
	    				$rgt->roleId = $this->getIdValue();
	    				$rgt->registryId = $oid;
	    				$found = $rgt->find(true);
	    				$rgt->s = $this->$sel;
	    				$rgt->u = $this->$upd;
	    				$rgt->d = $this->$del;
	    				$rgt->l = $this->$lck;
	    				if($found)
	    					$rgt->update();
	    				else
	    					$rgt->insert();
	    			}
	    	}
	    	else
	    		return parent::saveChildrenByClass($var, $cls, $tree);
	    }
}


