<?php
/*
 * Created on Sep 29, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	//print_r(app()->getEnumeratedObjects());

	$so = array();
	foreach (app()->getEnumeratedObjects() as $eo)
		$so[] = checkbox($obj, "dynEnum" . $eo, $eo);

	echo simpleform(array(
			textbox($obj, "name"),
			textbox($obj, "prefix"),
			textbox($obj, "suffix"),
			textboxdouble($obj, "startNr"),
			textarea($obj, "memo"),
			implode("", $so),
		));

