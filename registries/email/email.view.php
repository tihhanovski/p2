<?php
/*
 * Created on Sep 29, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */
 
 
	$obj = $context->obj;

	echo 	
			lockedMemo($obj->recipient, "recipient"),
			($obj->bcc ? lockedMemo($obj->bcc, "bcc") : ""),
			lockedMemo(htmlentities($obj->sender), "sender"),
			lockedMemo($obj->subject, "subject"),
			lockedMemo("<pre><small>{$obj->body}</small></pre>", "body"),
			lockedMemo($obj->signature, "signature"),
			lockedMemo($obj->attachment, "attachment"),
			lockedMemo($obj->getValue("sent"), "sent"),
			($obj->result ? lockedMemo("<pre><small>{$obj->result}</small></pre>", "Sending results") : "");
			
			
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
	
