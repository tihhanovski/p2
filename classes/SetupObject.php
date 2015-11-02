<?php
/*
 * Created on Nov 10, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */


	class SetupObject extends WFWObject
	{
		public $name;

		protected $settings;

		function __construct($name)
		{
			$this->name = $name;
			$suf = $this->getSuffix();
			$this->fullpath = $name . ($suf ? CHILD_DELIMITER . $suf : "");
		}

		/**
		 * {@inheritdoc}
		 */
		public function getSuffix()
		{
			return "";
		}

		/**
		 * {@inheritdoc}
		 */
		public function find()
		{
			return true;
		}

		/**
		 * {@inheritdoc}
		 */
		public function get()
		{
			return $this->fetch();
		}

		/**
		 * {@inheritdoc}
		 */
		public function fetch()
		{
			if($this->name)
			{
				$suf = $this->getSuffix();
				if($suf)
					$suf = "." . $suf;
				$this->settings = array();
				$s = app()->dbo("userproperty");
				$s->whereAdd("name like '" . $s->escape($this->name) . $suf . ".%' and userId = " . app()->user()->getIdValue());
				if($s->find())
				{
					while($s->fetch())
					{
						$vn = substr($s->name, strlen($this->name . $suf . "."));
						$this->settings[$vn] =  clone $s;
						$this->$vn = $s->value;
					}
				}
			}
			return true;
		}

		/**
		 * {@inheritdoc}
		 */
		public function setValue($field, $value)
		{
			$ret = parent::setValue($field, $value);
			$value = $this->$field;

			$suf = $this->getSuffix();
			if($suf)
				$suf = "." . $suf;

			if(is_array($this->settings))
				if(isset($this->settings[$field]))
				{
					$s = $this->settings[$field];
					if($s->value != $value)
					{
						$s->value = $value;
						if(is_null($value))
						{
							$s->delete();
							unset($this->settings[$field]);
							unset($this->$field);
						}
						else
							$s->update();
					}
					return $ret;
				}

			if(!is_null($value))
			{
				$s = app()->dbo("userproperty");
				$s->name = $this->name . $suf . "." . $field;
				$s->userId = app()->user()->getIdValue();
				$found = $s->find(true);
				$s->value = $value;
				if($found)
					$s->update();
				else
					$s->insert();
				$this->settings[$field] = $s;
			}
			return $ret;
		}

		/**
		 * {@inheritdoc}
		 */
	    public function getPrimaryKeyField()
	    {
	    	return null;
	    }

		/**
		 * {@inheritdoc}
		 */
		public function isNew()
		{
			return false;
		}

		/**
		 * {@inheritdoc}
		 * Not needed, doesn't work
		 */
		public function insert() {}

		/**
		 * {@inheritdoc}
		 * Not needed, doesn't work
		 */
		public function update() {}

		/**
		 * {@inheritdoc}
		 * Not needed, doesn't work
		 */
		public function delete() {}
	}