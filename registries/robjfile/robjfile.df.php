<?php

/**
 * Warehouse income detailform
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */
	$loc = "ru_" . $obj->robj;
	if($loc == $t = t($loc))
		$t = t("ro_" . $obj->robj);

	echo simpleform(array(
		lockedMemo($obj->name, "name"),
		textarea($obj, "memo"),
		lockedMemo(getFormatter(FORMAT_FILESIZE)->encodeHuman($obj->getFileSize()), "File size"),
		lockedMemo($t . " " . app()->getLinkedCaption($obj->getLinkedDocument()), "Linked document"),

		$obj->canAccess() ? lockedMemo("<a href=\"" . $obj->url("downloadFile") . "\">" . t("Download") . "</a>", "&nbsp;") : "",
		$obj->canAccess() ? lockedMemo("<a href=\"" . $obj->url("showFile") . "\" target=\"_blank\">" . t("Show") . "</a>", "&nbsp;") : "",
		));