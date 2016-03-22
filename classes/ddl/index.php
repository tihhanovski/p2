<?php
/**
 * DDL module index
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */

 	const DEFAULT_STRUCTURE_CHARSET = "utf8";
 	const DEFAULT_STRUCTURE_COLLATION = "utf8_unicode_ci";
 	const DEFAULT_STRUCTURE_ENGINE = "InnoDB";

 	if(!defined("STRUCTURE_CHARSET"))
 		define("STRUCTURE_CHARSET", DEFAULT_STRUCTURE_CHARSET);
 	if(!defined("STRUCTURE_COLLATION"))
 		define("STRUCTURE_COLLATION", DEFAULT_STRUCTURE_COLLATION);
 	if(!defined("STRUCTURE_ENGINE"))
 		define("STRUCTURE_ENGINE", DEFAULT_STRUCTURE_ENGINE);

	require_once WFW_CLASSPATH . "ddl/DBUpdater.php";
	require_once WFW_CLASSPATH . "ddl/DBDocumentor.php";
