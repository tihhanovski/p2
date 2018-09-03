<?php

	class CleanTempFolder extends CronTask
	{
		public $interval = 60;
		private $expire = 10; // Minutes

		function run()
		{
			// Define the folder to clean
			$tempDir   = sys_get_temp_dir().'/';

			// Filetypes to check (you can also use *.*)
			$fileTypes = '*.tmp';

			// Find all files of the given file type
			foreach (glob($tempDir . $fileTypes) as $filename) {

			    // Read file creation time
			    $fileCreationTime = filectime($filename);

			    // Calculate file age in seconds
			    $fileAge = time() - $fileCreationTime;

			    // Is the file older than the given time span?
			    if ($fileAge > ($this->expire * 60)){
					// delete file
			        unlink($filename);
			    }

			}
		}
	}
