<?php
/**
 * Application
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2011 Intellisoft OÜ
 *
 */

	/**
	 * Report context
	 */
	class ReportContext extends Context
	{
		function __construct($namePrefix)
		{
			$this->namePrefix = $namePrefix;
			$this->id = 0;
			$this->treeStructure = null;
			$className = $namePrefix;
			$this->obj = new ReportObject($className);
			$this->obj->fullpath = $this->name();
		}

		function setValueByPath($path, $value)
		{
			$this->obj->setValueByPath($path, $value, $this->treeStructure);
		}
	}