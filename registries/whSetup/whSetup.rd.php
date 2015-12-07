<?php
/**
 * Warehouse setup
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
 */

	/**
	 * Warehouse setup descriptor
	 */
	class WhsetupRegistryDescriptor extends SetupFormDescriptor
	{
		function getObj()
		{
			return app()->system();
		}
	}