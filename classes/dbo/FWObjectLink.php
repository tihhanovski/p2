<?php
/*
 * Created on Mar 21, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */


class FWObjectLink extends WFWObject
{
	protected $formats = array(
    	"mdCreated" => 		FORMAT_DATETIME,
    	"mdUpdated" => 		FORMAT_DATETIME,
    );

    public $mainObject = "";

    public function getObject()
    {
    	if($this->mainObject)
    	{
    		if($this->mainObject == $this->robject1)
    			return app()->get($this->robject2, $this->id2);
    		if($this->mainObject == $this->robject2)
    			return app()->get($this->robject1, $this->id1);
    	}
    	return null;
    }

}