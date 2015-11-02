<?php
/*
 * Created on Nov 5, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	include app()->getAbsoluteFile("ui/pagestart.php");
	include app()->getAbsoluteFile("ui/index.start.php");

	$d = app()->getRegistryDescriptor();
	$g = $d->getGrid();
	$d->setupGridStuff($g);

	$g->setup = $d->getGridSettings()->get_data_for_json();

?><div id="gridContainer"></div><script type="text/javascript"> var grid = <?=json_encode($g)?>; $(function(){ setupGrid(grid); grid.run();}); </script><?php

	include app()->getAbsoluteFile("ui/index.finish.php");
	include app()->getAbsoluteFile("ui/pagefinish.php");
