<?php
/**
 * Profile registry descriptor
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2011 Intellisoft OÜ
 */

	/**
	 * Profile registry descriptor
	 */
	class ProfileRegistryDescriptor extends SetupFormDescriptor
	{
		public function getObj()
		{
			return app()->user();
		}
	}
