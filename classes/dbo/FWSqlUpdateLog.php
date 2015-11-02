<?php
/*
 * Created on Mar 21, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */
 
 
	class FWSqlUpdateLog extends WFWObject
	{
	    function execute()
	    {
	    	if(!$this->find(true))
	    	{
	    		$this->result = "not executed";
	    		$this->insert();
	    		
	    		$r = $this->getDatabaseConnection()->query($this->command);
	    		
	    		if(is_object($r))
	    			if("DB_Error" == get_class($r))
	    			{
	    				$this->result = $r->message . ": " . $r->userinfo;
	    				$this->update();
	    				return true;
	    			}
	    			
	    		$this->result = "ok";
	    		$this->update();
	    		return true;
	    	}
	    	else
	    		return false;
	    }
	    
	    function output()
	    {
	    	return $this->command . "\n#\t" . $this->result . "\n\n\n";
	    }
		
	}