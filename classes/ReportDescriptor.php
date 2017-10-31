<?php
/**
 * ReportDescriptor
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2011 Intellisoft OÜ
 *
 */

	class ReportDescriptor extends RegistryDescriptor
	{
		public $pdfEnabled = true;
		public $htmlEnabled = false;
		public $xlsEnabled = false;


		public function needAskBeforeLeavePage()
		{
			return false;
		}

		/**
		 * Utility function to set default sort order to first column.
		 * @param Context $context
		 * @param string $f1 period start field
		 * @param string $f2 period finish field
		 * @param string $func method name to call. Function will be passed $context as only parameter
		 * @return bool true if something was changed
		 */
		protected function setDefaultPeriod($context, $f1, $f2, $func)
		{
			$obj = $context->obj;
			if(!(isset($obj->$f1) || isset($obj->$f2)))
			{
				$this->$func($context);
				return true;
			}
			return false;
		}

		/**
		 * Utility function to set default sort order to first column.
		 * @param Context $context
		 * @return bool true if something was changed
		 */
		protected function setDefaultOrderBy($context)
		{
			$obj = $context->obj;
			if(!isset($obj->orderBy))
			{
				foreach ($this->getAvailableColumns() as $c)
				{
					$obj->setValue("orderBy", $c->name);
					return true;	//only one iteration is OK
				}
			}
			return false;
		}

		/**
		 * Utility function checks if any column is selected to be printed and selects all otherwise.
		 * @param Context $context
		 * @return bool true if something was changed
		 */
		protected function setDefaultColumns($context)
		{
			$obj = $context->obj;
			$b = true;
			$ac = $this->getAvailableColumns();
			if(is_array($ac))
			{			
				foreach($ac as $c)
				{
					$cn = AVAILABLECOLUMN_FIELD_PREFIX . $c->name;
					if(isset($obj->$cn) && $obj->$cn)
						$b = false;
				}

				if($b)
					foreach($ac as $c)
						$obj->setValue(AVAILABLECOLUMN_FIELD_PREFIX . $c->name, 1);
			}
			return $b;
		}

		public function setDefaults($context)
		{
			return false;
		}

		public function outputIndexForm()
		{
			$context = $this->getContext();
			if($this->setDefaults($context))	//TODO maybe move elsewhere
				app()->putContext($context);
			$obj = $context->obj;
			include app()->getAbsoluteFile("ui/report.index.php");
		}

		function getType()
		{
			return RD_TYPE_REPORT;
		}

		function getTopToolbar()
		{
			return "";
		}

		function getAvailableForms()
		{
			return $this->forms;
		}

		public function clearFields()
		{
			if(is_object($c = new ReportContext($this->getContextName())))
				if(is_object($obj = $c->obj))
				{
					$this->setDefaults($c);
					$this->setDefaultColumns($c);
					app()->putContext($c);
					app()->requireReloadPage();
				}
			echo app()->jsonMessage();
		}

		private $columns;

		function initColumns()
		{
			return null;
		}

		public function getAvailableColumns()
		{
			if(!isset($this->columns))
				$this->columns = $this->initColumns();
			return $this->columns;
		}

		public function prevYear()
		{
			if(($f1 = app()->request("f1")) && ($f2 = app()->request("f2")))
				if(is_object($c = $this->getContext()))
				{
					$y = (int)date("Y") - 1;
					$f = getFormatter(FORMAT_DATE);
					$c->obj->setValue($f1, $f->encodeHuman("" . $y . "-01-01"));
					$c->obj->setValue($f2, $f->encodeHuman("" . $y . "-12-31"));
					app()->putContext($c);
				}
				else
					app()->addWarning("NO CONTEXT");
			echo app()->jsonMessage();
		}

		public function thisYear()
		{
			if(($f1 = app()->request("f1")) && ($f2 = app()->request("f2")))
				if(is_object($c = $this->getContext()))
				{
					$y = (int)date("Y");
					$f = getFormatter(FORMAT_DATE);
					$c->obj->setValue($f1, $f->encodeHuman("" . $y . "-01-01"));
					$c->obj->setValue($f2, $f->encodeHuman("" . $y . "-12-31"));
					app()->putContext($c);
				}
			echo app()->jsonMessage();
		}

		protected function setThisMonthPeriod($obj, $f1, $f2)
		{
					$y = date("Y");
					$m = date("m");
					$dt1 = "$y-$m-01";
					$m = 1 + $m;
					if($m > 12)
					{
						$y++;
						$m = 1;
					}
					$dt2 = "$y-$m-01";

					$d1 = strtotime($dt1);
					$d2 = strtotime($dt2) - SECS_DAY;

					$dt1 = date("Y-m-d", $d1);
					$dt2 = date("Y-m-d", $d2);

					$f = getFormatter(FORMAT_DATE);


					$obj->setValue($f1, $f->encodeHuman($dt1));
					$obj->setValue($f2, $f->encodeHuman($dt2));
		}

		public function thisMonth()
		{
			if(($f1 = app()->request("f1")) && ($f2 = app()->request("f2")))
				if(is_object($c = $this->getContext()))
				{
					$this->setThisMonthPeriod($c->obj, $f1, $f2);
					app()->putContext($c);
				}

			echo app()->jsonMessage();
		}

		public function prevMonth()
		{
			if(($f1 = app()->request("f1")) && ($f2 = app()->request("f2")))
				if(is_object($c = $this->getContext()))
				{
					$y = date("Y");
					$m = date("m");
					$dt2 = "$y-$m-01";
					$m = $m - 1;
					if($m < 1)
					{
						$y--;
						$m = 12;
					}
					$dt1 = "$y-$m-01";

					$d1 = strtotime($dt1);
					$d2 = strtotime($dt2) - SECS_DAY;

					$dt1 = date("Y-m-d", $d1);
					$dt2 = date("Y-m-d", $d2);

					$f = getFormatter(FORMAT_DATE);


					$c->obj->setValue($f1, $f->encodeHuman($dt1));
					$c->obj->setValue($f2, $f->encodeHuman($dt2));
					app()->putContext($c);
				}

			echo app()->jsonMessage();
		}
	}
