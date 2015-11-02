<?php
/*
 * Created on Sep 29, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */
 
 
	$obj = $context->obj;

	echo 	
			rightPanel($context, array(
				new SendButton($context, 2, "send")
				)), 
			textbox($obj, "recipient"),
			textbox($obj, "bcc"),
			lockedMemo(htmlentities($obj->sender), "sender"),
			textbox($obj, "subject"),
			textarea($obj, "body", null, array("class" => "mailBody textBox")),
			lockedMemo($obj->signature, "signature"),
			lockedMemo($obj->attachment, "attachment"),
			lockedMemo($obj->getValue("sent"), "sent"),
			($obj->result ? lockedMemo("<pre><small>{$obj->result}</small></pre>", "Sending results") : ""),
			lockedMemo($obj->isEditable(), "editable");
			
			
?><script type="text/javascript">

	$(function(){
		
		$(".mailBody").css("height", "200px");	//.css("width", "600px");
		
	});

</script><?php

	class SendButton extends RightPanelItem
	{
		function __construct($context, $form, $caption)
		{
			$this->context = $context;
			$this->form = $form;
			$this->caption = $caption;
		}
		
		function toHtml()
		{
			$obj = $this->context->obj;
			$ret = "<div class=\"rightPanelItem\">" .
					"<a href=\"JavaScript:ajaxCommand('?action=send&registry=" . $this->context->obj->__table . 
					"&id=" . $obj->getIdValue() . "');\">" .
					"<img src=\"" . app()->url("ui/img/16/print.png") . "\" border=\"0\"/>" . t($this->caption) . 
					"</a></div>";
			return $ret;
		}
	}
	
