<?php
/*
 * Created on Nov 10, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	include app()->getAbsoluteFile("ui/pagestart.php");
	include app()->getAbsoluteFile("ui/edit.start.php");


?><div class="editorContent"><div style="margin: 20px; margin-top: 10px;"><?php

	if($s = app()->getRegistryDescriptor()->getSetupInputPath())
	{
		$obj = $context->obj;
		include $s;
		if(!app()->uiHelper()->contextDataWritten)
			if(isset($context))
				echo contextData($context);
	}
	else
		echo t("input form not found");

?></div></div><?php

	include app()->getAbsoluteFile("ui/edit.finish.php");
	include app()->getAbsoluteFile("ui/pagefinish.php");