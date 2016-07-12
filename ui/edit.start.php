<body><?php include app()->ui()->getFilePath("html/topmenu.php"); ?><div class="editorContent"><div id="doc_warnings" class="docWarningContainer"></div><?php

	if(isset($context) && isset($context->obj))
		$obj = $context->obj;