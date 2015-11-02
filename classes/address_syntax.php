<?php
/**
 * Path syntax
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2011 Intellisoft OÜ
 *
 */

 	DEFINE("INDEX_DELIMITER", "-");
	DEFINE("CHILD_DELIMITER", "_");

	/**
	 * returns true if path contains indexed variables
	 *
	 * @param String $var
	 * @return bool
	 */
	function isIndexed($var)
	{
		return strpos($var, INDEX_DELIMITER);
	}

	/**
	 * returns true if path is complex eg consists of object and its child(ren)
	 *
	 * @param String $path
	 * @return bool
	 */
	function isComplex($path)
	{
		return strpos($path, CHILD_DELIMITER);
	}