<?php
/*
 * Created on Nov 03, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */


	echo textbox($obj, "name"),
		textbox($obj, "state"),
		textbox($obj, "typeId"),
		dynMultiTextArea($obj, "HelpLink", "Help link");