<?php
/*
 * Created on 29.08.2015
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

class FWVat extends WFWObject
{
    protected $captionFields = array("name");

    protected $formats = array(
        "pct" =>            FORMAT_FLOAT2,
        "mdCreated" =>      FORMAT_DATETIME,
        "mdUpdated" =>      FORMAT_DATETIME,
    );

    protected $validators = array(
        "name" => array(VALIDATION_NOT_EMPTY, VALIDATION_UNIQUE),
    );

    protected $closedField = "closed";  //closable

    function canCopy(){return true;}
}