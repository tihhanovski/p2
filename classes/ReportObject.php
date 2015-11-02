<?php
/*
 * Created on Nov 10, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */
 
 
	class ReportObject extends SetupObject
	{
		
		
		/*public $name;
		
		protected $settings;
		
	    function getPrimaryKeyField()
	    {
	    	return null;
	    }
	    
		function isNew()
		{
			return false;
		}	    

		function __construct($name)
		{
			$this->name = $name;
		}
		
		function find()
		{
			return true;
		}
		
		function get()
		{
			return fetch();
		}
		
		function fetch()
		{
			if($this->name)
			{
				$this->settings = array();
				$s = app()->dbo("userproperty");
				$s->whereAdd("name like '" . $s->escape($this->name) . ".%' and userId = " . app()->user()->getIdValue());
				if($s->find())
				{
					while($s->fetch())
					{
						$vn = substr($s->name, strlen($this->name) + 1);
						$this->settings[$vn] =  clone $s;
						$this->$vn = $s->value;
					}
				}
			}
			return true;
		}
		
		function setValue($field, $value)
		{
			$ret = parent::setValue($field, $value);
			
			$value = $this->$field;

			if(is_array($this->settings))
				if(isset($this->settings[$field]))
				{
					$s = $this->settings[$field];
					if($s->value != $value)
					{
						$s->value = $value;
						$s->update();
					}
					return $ret;
				}
				
			$s = app()->dbo("userproperty");
			$s->name = $this->name . "." . $field;
			$s->userId = app()->user()->getIdValue();
			$s->value = $value;
			$s->insert();
			$this->settings[$field] = $s;
			return $ret;
			
		}
		
		
		function insert()
		{
			
		}
		
		function update()
		{
			
		}
		
		function delete()
		{
			
		}*/
	}