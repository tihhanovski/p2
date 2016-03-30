<?php

	class UpdateDB extends CLITask
	{
		public function run()
		{
			app()->updater()->run();
		}
	}
