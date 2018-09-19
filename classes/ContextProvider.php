<?php
/**
 * Application
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2014 Intellisoft OÜ
 *
 */

	define("CONTEXT_PROVIDER_SESSION", "SessionContextProvider");
	define("CONTEXT_PROVIDER_USERFILES", "UserFilesContextProvider");
	define("CONTEXT_PROVIDER_USERFILES_VAREXPORT", "UserFilesVarExportContextProvider");
	define("CONTEXT_PROVIDER_USERFILES_STRIPPED", "UserFilesStrippedContextProvider");

	/**
	 * Context provider superclass
	 */
	class ContextProvider
	{
		/**
		 * retrieves context from session and returns it
		 * @return Context
		 */
		public function getContext($name)
		{}

		/**
		 * Saves context data to session
		 * @param Context $context
		 * @return void
		 */
		public  function putContext($context)
		{}

		public static function getInstance()
		{
			if(!defined("CONTEXT_PROVIDER_FLAVOR"))
				define("CONTEXT_PROVIDER_FLAVOR", CONTEXT_PROVIDER_SESSION);

			if(CONTEXT_PROVIDER_FLAVOR == CONTEXT_PROVIDER_SESSION)
				return new SessionContextProvider();
			if(CONTEXT_PROVIDER_FLAVOR == CONTEXT_PROVIDER_USERFILES)
				return new UserFilesContextProvider();
			if(CONTEXT_PROVIDER_FLAVOR == CONTEXT_PROVIDER_USERFILES_VAREXPORT)
				return new UserFilesVarExportContextProvider();
			if(CONTEXT_PROVIDER_FLAVOR == CONTEXT_PROVIDER_USERFILES_STRIPPED)
				return new UserFilesStrippedContextProvider();

			die("No context provider found for flavor " . CONTEXT_PROVIDER_FLAVOR);
		}

		public function clearContexts()
		{
		}
	}

	/**
	 * Holds contexts in session
	 */
	class SessionContextProvider extends ContextProvider
	{
		function getContext($name)
		{
			if(isset($_SESSION[INSTANCE_WEB][$name]))
				return unserialize($_SESSION[INSTANCE_WEB][$name]);
			return null;
		}

		function putContext($context)
		{
			if(!is_array($_SESSION))
				$_SESSION = array();
			if(!is_array($_SESSION[INSTANCE_WEB]))
				$_SESSION[INSTANCE_WEB] = array();
			$_SESSION[INSTANCE_WEB][$context->name()] = serialize($context);
		}
	}

	/**
	 * Holds contexts in files
	 */
	class UserFilesContextProvider extends ContextProvider
	{

		public function __construct()
		{
			if(!defined("CONTEXTDIRECTORY"))
				define("CONTEXTDIRECTORY", "context/");
		}

		/** @var String */
		private $userDir;

		protected function getUserDir()
		{
			if(!isset($this->userDir))
			{
				$mainDir = INSTANCE_ROOT . USERFILES . CONTEXTDIRECTORY;
				if(!file_exists($mainDir))
					mkdir($mainDir);
				$this->userDir = $mainDir . app()->user()->uid . "/";
				if(!file_exists($this->userDir))
					mkdir($this->userDir);
			}
			return $this->userDir;
		}

		/**
		 * @param String $name
		 * @return String;
		 */
		protected function getFileName($name)
		{
			/*if(!isset($this->userDir))
			{
				$mainDir = INSTANCE_ROOT . USERFILES . CONTEXTDIRECTORY;
				if(!file_exists($mainDir))
					mkdir($mainDir);
				$this->userDir = $mainDir . app()->user()->uid . "/";
				if(!file_exists($this->userDir))
					mkdir($this->userDir);
			}*/
			return $this->getUserDir() . $name . ".context";
		}

		function getContext($name)
		{
			$fn = $this->getFileName($name);
			if(file_exists($fn))
				return unserialize(file_get_contents($fn));
			return null;
		}

		function putContext($context)
		{
			file_put_contents($this->getFileName($context->name()), serialize($context));
		}

		public function clearContexts()
		{
			$ud = $this->getUserDir();
			$d = dir($ud);
			if ($d) {
				while (false !== ($file = $d->read()))
					if(substr($file, 0, 1) != ".")
						unlink($ud . $file);
				$d->close();
			}
		}
	}

	class UserFilesStrippedContextProvider extends UserFilesContextProvider
	{
		function putContext($context)
		{
			//echo "<pre><small>";
			//print_r($context);
			//echo "<hr/>";
			//die($this->getFileName($context->name()));

			file_put_contents($this->getFileName($context->name()), serialize($context));
		}
	}

	/**
	 * Holds contexts in files, no serialisation used
	 */
	class UserFilesVarExportContextProvider extends ContextProvider
	{

		public function __construct()
		{
			if(!defined("CONTEXTDIRECTORY"))
				define("CONTEXTDIRECTORY", "context/");
		}

		/** @var String */
		private $userDir;
		/**
		 * @param String $name
		 * @return String;
		 */
		private function getFileName($name)
		{
			if(!isset($this->userDir))
			{
				$mainDir = INSTANCE_ROOT . USERFILES . CONTEXTDIRECTORY;
				if(!file_exists($mainDir))
					mkdir($mainDir);
				$this->userDir = $mainDir . app()->user()->uid . "/";
				if(!file_exists($this->userDir))
					mkdir($this->userDir);
			}
			return $this->userDir . $name . ".context";
		}

		function getContext($name)
		{
			$fn = $this->getFileName($name);
			if(file_exists($fn))
			{
				$ret = include($fn);
				return $ret;
			}
			return null;
		}

		function putContext($context)
		{
			/*
			echo "<pre><small>";
			print_r($context);
			echo "<hr/>";
			die($this->getFileName($context->name()));
			/**/
			file_put_contents($this->getFileName($context->name()), "<?php\nreturn ".
                                var_export($context, true).
                                ";");
		}
	}
