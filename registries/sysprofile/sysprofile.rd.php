<?php
/**
 * System profile registry descriptor
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 TMB Elements OÜ
 */

	/**
	 * System prodile descriptor
	 */
	class SysprofileRegistryDescriptor extends SetupFormDescriptor
	{
		public function getObj()
		{
			return app()->system();
		}
	}