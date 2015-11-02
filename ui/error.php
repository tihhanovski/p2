<div class="errorMessage">
	<div><img src="<?=app()->url("ui/img/large/error.png")?>" border="0"/></div>
	<h1><div class="errorHeader"><a href="JavaScript:history.back(-1);"><?=app()->errorMessage?></a></div></h1>
	<div><a href="JavaScript:alert('TODO');"><?=t("Report to developer")?></a></div>
	<div><a href="JavaScript:history.back(-1);"><?=t("Go back")?></a></div>
</div>