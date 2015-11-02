<?php
/**
 * CLI Task
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */

const CLITASK_DIRECTORY = "clitasks";

/**
 * CLI task base class
 */
class CLITask
{

	/**
	 * executes task
	 */
	public function runTask()
	{
		//TODO log
		$this->run();
	}

	/**
	 * Method should be implemented in actual CLITasks
	 */
	public function run()
	{
		$this->log("default method, you should not see this message in your logs");
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

function discoverCLITasks($dir, &$arr)
{
	if(file_exists($dir . CLITASK_DIRECTORY))
	{
		$d = dir($dir . CLITASK_DIRECTORY);
		$fs = array();
		while (false !== ($file = $d->read()))
			if(substr($file, 0, 1) != ".")
			{
				$cn = substr($file, 0, -4);
				$arr[$cn] = $cn;
			}
		$d->close();
	}
}

/**
 * Discover and execute cron tasks.
 * @param String $dir if empty, looking for tasks in framework, app and instance folders
 */
function runCLITask()
{
	global $argv;
	$task = isset($argv[1]) ? $argv[1] : "";
	app()->localAuth();
	app()->setLocale(DEFAULT_LOCALE);

	$at = array();
	discoverCLITasks(WFW_ROOT, $at);
	discoverCLITasks(APP_ROOT, $at);
	discoverCLITasks(INSTANCE_ROOT, $at);

	if($task == "")
		echo "Available tasks:\n\t" . implode("\n\t", $at) . "\n";
	else
	{
		if(isset($at[$task]))
		{
			$fn = $task . ".php";
			if($afn = app()->getAbsoluteFile(CLITASK_DIRECTORY . "/" . $fn))
				include_once $afn;
			if(class_exists($task))
				if(is_object($o = new $task))
					if(is_subclass_of($o, "CLITask"))
						$o->runTask();
		}
		else
		{
			echo "Task $task not found. Available tasks:\n\t" . implode("\n\t", $at) . "\n";
		}
	}
}