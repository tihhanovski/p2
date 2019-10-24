<?php
/**
 * Connect to database
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2011 Intellisoft OÜ
 *
 */


	require_once "PEAR.php";
	require_once 'DB/DataObject.php';
	require_once 'DB/DataObject/Error.php';

	$options = &PEAR::getStaticProperty('DB_DataObject', 'options');
	if(file_exists("setup/dbo.php"))
		require_once("setup/dbo.php");
	else
	{
		if(file_exists("setup/dbo.ini"))
		{
			$config = parse_ini_file('setup/dbo.ini', TRUE);
			$options = $config['DB_DataObject'];
		}
	}