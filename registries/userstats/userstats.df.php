<?php
/*
 * Created on Sep 29, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	$u = $obj->getLink("userId");
	$a = $obj->getLink("actorId");
	$t = $obj->getLink("typeId");
	$ip = $obj->getLink("useripId");
	$ua = $obj->getLink("useragentId");

	echo simpleform(array(
		lockedMemo($u->uid, "user"),
		lockedMemo($obj->getValue("dt"), "date"),
		lockedMemo(t(USERSTATTYPE_CAPTION_PREFIX . $t->name), "Type"),
		lockedMemo(t($obj->name), "Name"),
		lockedMemo($obj->memo, "Memo"),
		lockedMemo($a->uid, "Actor"),

		lockedMemo($ip->ip, "IP"),
		lockedMemo($ua->rawdata, "Browser"),
		));
