<?php
/**
 * RobjfileUploadControl
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */

	class RobjfileUploadControl extends BaseInput
	{
		/** {@inheritdoc}*/
		public function toHtml()
		{
			return "<div class=\"robjfileAppendBox\">
	<form id=\"fileInputForm\" method=\"POST\" action=\"" . app()->url() . "\" enctype=\"multipart/form-data\">
		<input type=\"hidden\" name=\"registry\" id=\"fileInputFormregistry\" value=\"\"/>
		<input type=\"hidden\" name=\"action\" value=\"uploadRobjfile\"/>
		<input type=\"hidden\" name=\"id\" id=\"fileInputFormid\" value=\"\"/>
		<input type=\"file\" name=\"fc\" id=\"fileInput\"/>
	</form>
	</div>
	<script type=\"text/javascript\">

		$(function(){

			$('#fileInput').change(function ()
			{
				$(\"#fileInputFormid\").val(req.id);
				$(\"#fileInputFormregistry\").val(req.registry);
				$(\"#fileInputForm\")[0].submit();
			});

		});

	</script>";
		}

		public function cantUploadOnNewDocument()
		{
			return lockedMemo("<a href=\"JavaScript:app.saveDocument();\">" . t("Save document to upload files") . "</a>", "&nbsp;");
		}

		/** {@inheritdoc}*/
		public static function getType()
		{
			return "";
		}
	}

	function robjfileUploadControl($obj = null)
	{
		$c = new RobjfileUploadControl();
		if(is_object($obj))
			if(!$obj->isInDatabase())
				return $c->cantUploadOnNewDocument();
		return $c->toHtml();
	}