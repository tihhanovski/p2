<?php

	//here we assume that $context already initialized!

	if(isset($context))
	{
		if(isset($context->obj))
		{
			if(!app()->uiHelper()->contextDataWritten)
				echo closeDocumentToolbar($context->obj);

			if(!app()->uiHelper()->modificationDataWritten)
				echo modificationData($context->obj);
		}

		if(!app()->uiHelper()->contextDataWritten)
			echo contextData($context);
	}


?><div style="clear: both;"></div></div><script type="text/javascript"> $(function(){filesControl.create($("#filesControl")); msgControl.create($("#msgControl"));<?php 

	if(defined("ENTER_AS_TAB") && ENTER_AS_TAB) echo "enterAsTab();";
	if($this->needAskBeforeLeavePage())
		echo "window.onbeforeunload = function() {return app.askBeforeLeave();};";

	// try{
	//var xstartfocused = false; $("input,select").each(function(){if(!xstartfocused) if(!$(this).hasClass("datepicker")) {log(this.id); xstartfocused = true; this.focus();}}) 
	//}catch(e){}
	//});


?>});</script>