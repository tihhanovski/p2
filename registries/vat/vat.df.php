<?php
/**
 * VAT registry detail form
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 06.09.2014 Intellisoft OÜ
 */

	echo simpleform(array(
			textbox($obj, "name", "Name"),
			textboxdouble($obj, "pct", "Percent"),
			textarea($obj, "memo"),
		));