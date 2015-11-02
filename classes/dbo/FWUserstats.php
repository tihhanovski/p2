<?php

class FWUserstats extends WFWObject
{

    function insert()
    {
    	$this->dt = app()->now();
    	return parent::insert();
    }

    protected $formats = array(
    	"dt" => 			FORMAT_DATETIME
    );
}