<?php
/*
 * Created on Mar 21, 2012
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */


class FWMessage extends WFWObject
{
    function send()
    {
    	$this->sent = app()->now();
    	$this->senderId = app()->user()->getIdValue();
    }
}