<?php
/*
 * Created on Nov 24, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

	define("WARNING_ERROR", "error");
	define("WARNING_WARNING", "warning");
	define("WARNING_INFO", "info");

	/**
	 * Warning sent from application to UI
	 */
	class Warning
	{
		public $severity;
		public $field;
		public $message;

		/**
		 * @param string $message
		 * @param string $field
		 * @param string $severity
		 */
		function __construct($message, $field = "", $severity = WARNING_INFO)
		{
			$this->message = $message;
			$this->severity = $severity;
			$this->field = $field;
		}
	}