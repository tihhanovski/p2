<?php
/**
 * Article detailform
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) Intellisoft OÜ
 *
 */

	echo simpleform(array(
			textbox($obj, "code", "Code"),
			textbox($obj, "name", "Name"),
			selectSqlTranslated($obj, "typeId", "Type", SQL_COMBO_ARTICLETYPE),
			selectSql($obj, "unitId", "Unit", SQL_COMBO_UNIT),
			textarea($obj, "memo", "Memo"),

		));
