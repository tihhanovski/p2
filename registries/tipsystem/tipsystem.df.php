<?php
/*
 * Created on Sep 19, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	echo
			textbox($obj, "page"),
			textbox($obj, "control"),
			textarea($obj, "body"),
			dynMultiTextArea($obj, "body", "body");