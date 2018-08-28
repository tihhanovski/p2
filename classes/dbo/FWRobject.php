<?php
/*
 * Created on Mar 21, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */


	class FWRobject extends WFWObject
	{
	    function getCaption()
	    {
	    	return "ro_" . $this->name;
		}
		
		public function specialrights()
		{
			$special = app()->dbo('specialright');
			$special->registryId = $this->id;
			return $special->fetchAll();
		}
	}