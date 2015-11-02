<?php
	/*
	* Created on Jun 21, 2013
	* (c) ilja@intellisoft.ee
	*/

class FWTipsystem extends WFWObject
{
    protected $formats = array(
    	"mdCreated" => 		FORMAT_DATETIME,
    	"mdUpdated" => 		FORMAT_DATETIME,
    );

    //closable infrastructure
	protected $closedField = "active";
	protected $closedValue = 0;
	protected $notClosedValue = 1;

	function getMessage()
	{
		if(!isset($this->lang))
		{
			$this->lang = "";
			$this->lang = app()->getLocale();
			if(!$this->lang)
				$this->lang = DEFAULT_LOCALE;
		}
		$ret = "";
		if($this->lang)
		{
			$this->loadDynamicPropertiesIfNotLoaded();
			$f = "dynbody_" . $this->lang;
			if(isset($this->$f))
				$ret = $this->$f;
		}
		if(!$ret)
			$ret = $this->body;
		return $ret;
	}


}