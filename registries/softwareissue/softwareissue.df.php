<script type="text/javascript">

	$(function(){
		$("#" + obj.fullpath + "_memo").css("width", "800px").css("height", "150px");
		$("#" + obj.fullpath + "_resolution").css("width", "800px").css("height", "150px");
	})

</script><?php
/*
 * 2013
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */
	
	echo simpleform(array(
			rightPanel($context, array(
				new AjaxCommandButton($context, "?registry=softwareissue&id=" . $obj->getIdValue() . "&action=sendEmail", "Send email"),
			), false),
			textbox($obj, "caption"),
			textarea($obj, "memo"),
			textboxdouble($obj, "priority"),
			datepicker($obj, "deadline"),
			textarea($obj, "resolution"),
			textboxAutocomplete($obj, "state"),
			selectSql($obj, "ownerId", "Owner", SQL_COMBO_WEBUSER),
			textbox($obj, "cc", "Send CC"),
		));
