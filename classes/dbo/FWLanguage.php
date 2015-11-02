<?php
	/*
	* Created on May 14, 2013
	* (c) ilja@intellisoft.ee
	*/

	class FWLanguage extends WFWObject
	{
	    protected $captionFields = array("code", "name");

	    protected $closedField = "closed";	//closable

	    protected $formats = array(
	    	"mdCreated" => 		FORMAT_DATETIME,
	    	"mdUpdated" => 		FORMAT_DATETIME,
	    );

	    protected $validators = array(
	    	"code" => array(VALIDATION_NOT_EMPTY, VALIDATION_UNIQUE),
	    	"name" => array(VALIDATION_NOT_EMPTY),
	    );
	}