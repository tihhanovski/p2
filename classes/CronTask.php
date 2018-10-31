<?php
/**
 * Context
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2013 Intellisoft OÜ
 *
 */

define("CRONTASK_INTERVAL_DEFAULT", 60);
define("CRONTASK_DIRECTORY", "crontasks");

/**
 * Scheduled task base class
 */
class CronTask
{
	/** @var bool if true then executed even if time interval not passed */
	public $forced = false;

	/**
	 * Checks if task should be executed and executes task if necessary
	 */
	public function runTask()
	{
		$this->log($cn = get_class($this));
		if(!$this->forced)
		{
			$t = app()->dbo("crontask");
			$t->name = $cn;
			$intx = (int)$this->getInterval();
			if(!$intx)
				$intx = 60;
			$t->whereAdd("date_add(coalesce(finished, started), interval $intx minute) > now()");
			$t->orderBy("coalesce(finished, started) desc");
			if($t->find(true))
			{
				if(app()->isDebug())
					echo "last time " . ($t->finished ? $t->finished : $t->started) . " finished less than $intx minutes before\n";
				return;
			}
		}

		$t = app()->dbo("crontask");
		$t->name = $cn;
		$t->started = app()->now();
		$this->_log = "";
		$this->run();
		$t->log = $this->_log;
		$t->finished = app()->now();
		$t->insert();
	}

	/**
	 * Method should be implemented in actual CronTasks
	 */
	public function run()
	{
		$this->log("default method, you should not see this message in your logs");
	}

	/**
	 * execution interval in minutes
	 * @return int
	*/
	public function getInterval()
	{
		if(isset($this->interval))
			return (int)$this->interval;
		return CRONTASK_INTERVAL_DEFAULT;
	}

	/** @var String execution log */
	private $_log = "";

	/**
	 * log
	 * @param String $s
	 */
	public function log($s)
	{
		if(app()->isDebug())
			echo $s . "\n";
		$this->_log .= $s . "\n";
	}
}

/**
 * Discover and execute cron tasks.
 * @param String $dir if empty, looking for tasks in framework, app and instance folders
 */
function runCronTasks($dir = "")
{
	if(php_sapi_name() != "cli")
	{
		app()->location();
		return;
	}
	app()->localAuth();
	
	// Set locale only once
	// Avoids losing translations after first cron task since setLocale empties translations array
	// and translation files are included using include_once
	if (is_null(app()->getLocale())) {
		app()->setLocale(DEFAULT_LOCALE);
	}

	$forced = false;
	global $argv;
	foreach ($argv as $a)
	{
		$a = strtolower($a);
		if($a == "forced")
			$forced = true;
	}

	if($dir == "")
	{
		if(defined("WFW_ROOT"))
			runCronTasks(WFW_ROOT . CRONTASK_DIRECTORY);
		foreach (moduleManager()->getList() as $root)
			runCronTasks($root . CRONTASK_DIRECTORY);
		if(defined("APP_ROOT"))
			runCronTasks(APP_ROOT . CRONTASK_DIRECTORY);
		if(defined("INSTANCE_ROOT"))
			runCronTasks(INSTANCE_ROOT . CRONTASK_DIRECTORY);
		return;
	}

	if(file_exists($dir))
	{
		if(app()->isDebug())
			echo "$dir\n";

		$d = dir($dir);
		$fs = array();
		while (false !== ($file = $d->read()))
			if(substr($file, 0, 1) != ".")
				$fs[] = $file;
		$d->close();

		foreach ($fs as $f)
		{
			$acn = explode(".", $f);
			include_once $dir . "/" . $f;
			$c = $acn[0];
			if(class_exists($c))
				if(is_object($o = new $c))
					if(is_subclass_of($o, "CronTask"))
					{
						$o->forced = $forced;
						$o->runTask();
					}
		}
		if(app()->isDebug())
			echo "\n";
	}
}
