<?php
/*
 * Created on Mar 21, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */


class FWUserStats extends WFWObject
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
