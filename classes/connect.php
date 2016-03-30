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
	$config = parse_ini_file('setup/dbo.ini', TRUE);
	$options = $config['DB_DataObject'];
