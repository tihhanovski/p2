<?php
/**
 * Unit detailform
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) Ilja Tihhanovski
 *
 */


	echo simpleform(array(
			textbox($obj, "name", "Name"),
			textarea($obj, "memo"),
			textbox($obj, "articleIdEntry", "Article"),
		));

?><input type="text" id="articleId"/><input type="text" id="articleLabel"/><script>

$(function()
{
	setKeySel3(obj.fullpath + "_articleId", "article.php");
});


</script>
