<?php
/*
 * Created on Sep 29, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */


	echo simpleform(array(
			textbox($obj, "code", "Code"),
			textbox($obj, "name", "Name"),
			textarea($obj, "memo"),
		));

