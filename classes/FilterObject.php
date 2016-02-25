<?php
/**
 * FilterObject
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2011 Intellisoft OÜ
 *
 */

	/**
	 * "unsaved" properties prefix
	 */
	const UNSAVED_PREFIX = "unsaved";
	const SAVENOW_PREFIX = "savenow";

	/**
	 * DBO for filter
	 */
	class FilterObject extends SetupObject
	{
		/**
		 * {@inheritdoc}
		 * @return string
		 */
		public function getSuffix()
		{
			return "filter";
		}

		/**
		 * @return bool
		 */
		public function isActive()
		{
			return isset($this->active) && $this->active;
		}

		/**
		 * {@inheritdoc}
		 */
		public function fetch()
		{
			if(isset($this->formats) && is_array($this->formats))
				foreach ($this->formats as $k => $v)
					if((strpos($k, UNSAVED_PREFIX) === false) && (strpos($k, SAVENOW_PREFIX) === false))
					{
						if(!isset($this->formats[UNSAVED_PREFIX . $k]))
							$this->formats[UNSAVED_PREFIX . $k] = $v;
						if(!isset($this->formats[SAVENOW_PREFIX . $k]))
							$this->formats[SAVENOW_PREFIX . $k] = $v;
					}
			parent::fetch();
		}

		/**
		 * Cancel previously entered filter setup
		 */
		public function cancelFilter()
		{
			if($this->name)
			{
				$suf = $this->getSuffix();
				if($suf)
					$suf = "." . $suf;
				$sql = "delete from userproperty
					where name like " . quote($this->name . $suf . "." . UNSAVED_PREFIX . "%") .
					" and userId = " . app()->user()->getIdValue();
				//echo $sql . "<br/>";
				app()->query($sql);
				return true;
			}
			else
				return false;
		}

		/**
		 * Apply previously entered filter setup.
		 * Moves in database all "%unsaved%" records to "saved" state and reloads filter object.
		 * @return bool true if successfull
		 */
		public function applyFilter()
		{
			if($this->name)
			{
				$suf = $this->getSuffix();
				if($suf)
					$suf = "." . $suf;
				$this->settings = array();
				$s = app()->dbo("userproperty");
				$s->whereAdd("name like " . quote($this->name . $suf . "." . UNSAVED_PREFIX . "%") .
					" and userId = " . app()->user()->getIdValue());
				if($s->find())
					while($s->fetch())
					{
						$s->name = str_replace(UNSAVED_PREFIX, "", $s->name);
						app()->query("delete from userproperty " .
							"where userId = " . app()->user()->getIdValue() .
							" and name = " . quote($s->name));
						if($s->value === "")
							$s->delete();
						else
							$s->update();
					}

				parent::setValue("active", "1");

				return $this->fetch();
			}
			return false;
		}

		/**
		 * Empty filter setup
		 * @return bool true if successfull
		 */
		public function emptyFilter()
		{
			if($this->name)
			{
				$suf = $this->getSuffix();
				if($suf)
					$suf = "." . $suf;
				app()->query("delete from userproperty " .
					"where userId = " . app()->user()->getIdValue() .
					" and name like " . quote($this->name . $suf . ".%"));
				return $this->fetch();
			}
			return false;
		}

		/**
		 * {@inheritdoc}
		 */
		function setValue($field, $value)
		{
			if(strpos($field, SAVENOW_PREFIX) === 0)
				parent::setValue(substr($field, strlen(SAVENOW_PREFIX)), $value);
			else
				parent::setValue(UNSAVED_PREFIX . $field, $value);
		}
	}