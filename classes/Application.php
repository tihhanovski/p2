<?php
/**
 * Application
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2011 Intellisoft OÜ
 *
 */

	//gc_disable();

	/**
	* CRLF
	*/
	define("CRLF", "\r\n");

	define("AUTH_ACTION", "auth");
	define("AUTH_ACTION_LOGIN", "login");
	define("AUTH_ACTION_LOGOUT", "logout");
	define("AUTH_ACTION_LOGIN_UID", "uid");
	define("AUTH_ACTION_LOGIN_PWD", "pwd");
	define("USERFILES", "userfiles/");

	define("PRIVILEGE_SELECT", "s");
	define("PRIVILEGE_UPDATE", "u");
	define("PRIVILEGE_DELETE", "d");
	define("PRIVILEGE_LOCK", "l");

	define("STATE_DRAFT", 1);
	define("STATE_NORMAL", 2);
	define("STATE_DELETED", 3);

	define("RESULT_ERROR", "error");
	define("RESULT_OK", "ok");
	define("MSG_NOT_LOGGED_IN", "not logged in");
	define("MSG_INSUFFICIENT_RIGHTS", "insufficient rights");

	define("REQUEST_REGISTRY", "registry");
	define("REQUEST_LOCALE", "locale");
	define("REQUEST_STARTUPMSG", "startupMsg");
	define("VERSIONLOG_FILE", "classes/versionlog.json");
 	define("MAX_XLS_WORKSHEET_NAME_LENGTH", 31);
 	define("INNER_CHARSET", "UTF-8");

 	define("USERSTATTYPE_STATISTICS", 1);
 	define("USERSTATTYPE_MANIPULATION", 2);
 	define("USERSTATTYPE_SECURITY", 3);

 	const DEFAULT_REGISTRYDESCRIPTOR_CLASSNAME = "_RegistryDescriptor";

 	const ROBJECT_TYPE_SIMPLEWIDGET = 11;

 	define("SQL_COMBO_WEBUSER", "select id, uid from webuser order by uid");

	require_once "connect.php";

	if(file_exists($clsFn = APP_ROOT . "index.php"))
		include_once $clsFn;
	else
		if(file_exists($clsFn = APP_ROOT . "classes/classes.php"))		//TODO deprecate it
			include_once $clsFn;

	/**
	* just the same as PHP stdObject
	* @deprecated since 2.0
	*/
	class EmptyObject{}

	class ApplicationDeferredTask
	{
		public function run()
		{
			//implement in child
		}
	}

	/**
	* Application object,
	* @see app() function
	*/
	class WFWApp
	{
		/** @var FWWebuser current user*/
		private $user;
		/** @var FWWebuser system user*/
		private $system;
		/** @var RegistryDescriptor current registry descriptor*/
		private $registryDescriptor;
		/** @var bool*/
		private $registryDescriptorInitialized = false;
		/** @var array<String,String> messages translations*/
		private $translations;
		/** @var String current language eg en, et, ru etc*/
		private $locale;
		/** @var String[] possible locales*/
		public $localesList;

		/** @var RequestHandler*/
		private $requestHandler;

		/** @var array objects fields that has been updated to output to browser*/
		public $updatedFields = array();
		/** @var array  warnings to output to browser*/
		public $warnings = array();

		/** @var DBDocumentor instance of DBDocumentor*/
		private $dbdoc;
		/**
		* initializes and returns DBDocumentor instance
		* @return DBDocumentor
		*/
		function dbDocumentor()
		{
			if(!isset($this->dbdoc))
				$this->dbdoc = new DBDocumentor();
			return $this->dbdoc;
		}

		public function addMetrics($meteredObjectType, $meteredObjectDescription, $objId, $val, $memo = "")
		{
			$m = app()->dbo("metrics");
			$m->meteredObjectType = $meteredObjectType;
			$m->meteredObjectDescription = $meteredObjectDescription;
			$m->objId = $objId;
			$m->val = $val;
			$m->dt = app()->now();
			$m->memo = $memo;
			$m->insert();
			$m->free();
		}

		protected $deferredTasks = array();

		public function addDeferredTask($task, $index = "")
		{
			if($index === "")
				$this->deferredTasks[] = $task;
			else
				$this->deferredTasks[$index] = $task;
		}

		public function clearDeferredTasks()
		{
			$this->deferredTasks = array();
		}

		public function runDeferredTasks()
		{
			//$this->debug("<h2>runDeferredTasks</h2>");
			//$this->debug(print_r($this->deferredTasks, 1));
			//$this->debug("<hr/>");
			$this->organizeDeferredTasks();
			foreach ($this->deferredTasks as $task)
				$task->run();
		}

		public function organizeDeferredTasks()
		{
			//implement in child classes if needed
		}

		/**
		* application version log
		* reads data from versionlog.json and returns it as PHP array
		* @return array
		*/
		function getVersionData()
		{
			if($fn = $this->getAbsoluteFile(VERSIONLOG_FILE))
				return json_decode(file_get_contents($fn));
			return null;
		}

		/** @var WarehouseModule */
		private $_warehouse;

		/**
		 * Warehouse module.
		 */
		function warehouse()
		{
			if(!isset($this->_warehouse))
				$this->_warehouse = new WarehouseModule();
			return $this->_warehouse;
		}

		/**
		* application version number
		* @return String
		*/
		function getVersion()
		{
			$v = $this->getVersionData();
			if(!is_null($v))
				return $v->version . (defined("VERSION_SUFFIX") ? VERSION_SUFFIX : "");
			return "";
		}

		/**
		* Is it possible for new users to add themselves, not implemented yet
		* @return bool
		*/
		function canPubliclyRegister()
		{
			return false;
		}

		/**
		* Return translated page title based on current registry in $_REQUEST array
		* @return String
		*/
		public function getPageTitle()
		{
			if($rc = $this->request(REQUEST_REGISTRY))
			{
				$t1 = "";
				if($this->request(REQUEST_ACTION) == ACTION_EDIT)
				{
					$t = "ru_" . $rc;
					$t1 = t($t);
					if($t1 == $t)
						$t1 = "";
				}
				if(!$t1)
					$t1 = t("ro_" . $rc);
				return $t1;
			}
			else
				return APP_TITLE;
		}

		/**
		* Shows fatal error message to user, tries to send message to developer, stops execution
		* Not properly implemented yet
		* @return void
		*/
		function panic($msg = "")
		{
			//TODO email to developer
			//TODO panic log
			echo "<hr/>";
			echo t("Something went really wrong") . "<br/>";
			if($msg)
				echo t($msg);
			echo "<hr/><pre><small>";

			echo "SERVER: " . htmlentities(print_r($_SERVER, true));

			echo "</small></pre>";

			die();
		}

		/**
		 * @param string $langCode
		 * @return string
		 */
		function getCompanyAddress($langCode = null)
		{
			if(is_null($langCode))
				$langCode = app()->getLocale();
			$sys = app()->system();
			$sys->loadDynamicPropertiesIfNotLoaded();

			$myAddr = "";
			if($langCode)
			{
				$af = PROPERTY_PREFIX . "CompanyAddress_" . $langCode;
				if(isset($sys->$af))
					$myAddr = $sys->$af;
			}
			if(!$myAddr)
				if(isset($sys->dynCompanyAddress))
					$myAddr = $sys->dynCompanyAddress;
			return $myAddr;
		}

		/**
		* shows error message
		* @return void
		*/
		function showError($msg = "")
		{
			include app()->getAbsoluteFile("ui/pagestart.php");

			$this->errorMessage = t($msg);

			include app()->getAbsoluteFile("ui/error.php");
			//echo "<a href=\"JavaScript:history.back(-1);\">" . t($msg) . "</a>";

			include app()->getAbsoluteFile("ui/pagefinish.php");
		}

		private $iconUrlCache = array();

		public function getIconUrl($size, $icon)
		{
			$u = $size . "/" . $icon;
			if(!isset($this->iconUrlCache[$u]))
				$this->iconUrlCache[$u] = $this->url("ui/img/$u.png");
			return $this->iconUrlCache[$u];
		}

		/**
		* initializes reporting
		* @return void
		*/
		function initReporting()
		{
			//includes TCPDF stuff and classes/reporting.php from framework, app and instance if found there.
			if(defined("SETUP_TCPDF_LANG"))
				require_once SETUP_TCPDF_LANG;
			require_once SETUP_TCPDF_CLASS;
			$this->includeAll("classes/reporting.php");


			if(!defined("REPORT_FONTFAMILY"))
				define("REPORT_FONTFAMILY", "helvetica");	//
		}

		/** @var TestRunner test infrastructure object */
		private $_testrunner;
		/**
		* initializes test stuff
		* @return TestRunner
		*/
		function getTestInfrastructure()
		{
			$this->includeAll("classes/test/index.php");
			if(!(isset($this->_testrunner) && is_object($this->_testrunner)))
				$this->_testrunner = new TestRunner();
			return $this->_testrunner;
		}

		/**
		* Includes (if exist) named file from every part of application (framework, application, instance)
		* @param String $s file name to include
		* @return void
		*/
		function includeAll($s)
		{
			$this->includeIfExists(WFW_ROOT . $s);
			moduleManager()->includeAll($s);
			$this->includeIfExists(APP_ROOT . $s);
			$this->includeIfExists(INSTANCE_ROOT . $s);
		}

		/**
		* Includes file if it exists
		* @return void
		*/
		function includeIfExists($f)
		{
			if(file_exists($f))
				include_once $f;
		}

		/**
		* adds upfated field's data to output to browser later
		* @param String $o
		* @return void
		*/
		function addUpdated($o)
		{
			$this->updatedFields[] = $o;
		}

		/**
		* Adds warning to queue
		* @param Warning|String $w
		* @return void
		*/
		function addWarning($w)
		{
			if(is_object($w))
				$w->message = t($w->message);
			else
				$w = new Warning(t($w));
			$this->warnings[] = $w;
		}

		private $requireReload = false;
		private $requireReloadPage = false;

		/**
		 * Use to require web browser to fully reload document from database
		 * @param bool $r
		 * @return void
		 * @deprecated since 2.0
		 */
		function requireReload($r = true)
		{
			$this->requireReload = $r;
		}

		/**
		 * Use to require web browser to fully reload document from database
		 * @param bool $r
		 * @return void
		 */
		function requireReloadContext($r = true)
		{
			$this->requireReload($r);
		}

		/**
		 * Use to require web browser to refresh documents UI (does not load actual data from database)
		 * @param bool $r
		 * @return void
		 */
		function requireReloadPage($r = true)
		{
			$this->requireReloadPage = $r;
		}

		/**
		 * Initializes and returns Request Handler
		 * @return RequestHandler
		 */
		function getRequestHandler()
		{
			if(!isset($this->requestHandler))
				$this->requestHandler = new RequestHandler();

			return $this->requestHandler;
		}

		/**
		 * Add not translated message to translation log. Dont use in production!
		 * In production TRANSLATED_WATCH should be set to false.
		 * @param String $s message
		 * @return void
		 */
		function reportNotTranslated($s)
		{
			if(TRANSLATED_WATCH)
				if(is_object($t = app()->dbo("translated")))
					if(!$this->isDBError($t))
					{
						$t->name = $s;
						if(!$t->find(true))
						{
							$t->name = $s;
							$t->insert();
						}
					}
		}

		/**
		 * Translate message
		 * @param String $s
		 * @return String
		 */
		function translate($s)
		{
			if(isset($this->translations[$s]))
				return $this->translations[$s];
			else
			{
				$this->reportNotTranslated($s);
				return $s;
			}
		}

		/**
		 * Checks if application have translations
		 * @return bool
		 */
		function isI18n()
		{
			return (is_array($this->localesList) && (count($this->localesList) > 1));
		}

		/**
		 * sets locales list
		 * @param array<String> $ll
		 * @return void
		 */
		function setLocalesList($ll)
		{
			$this->localesList = $ll;
		}

		/**
		 * Sets current locale, loads translations
		 * @param String $l
		 * @return void
		 */
		function setLocale($l)
		{
			//if(I18N_EXTENDED_LOCALES)
			//	$this->translations = array();
			$l = stripDirBack($l);
			$this->locale = $l;
			$iFile = "i18n/" . $l . ".php";
			$reg = stripDirBack($this->request(REQUEST_REGISTRY));

			if(I18N_EXTENDED_LOCALES)
			{
				$this->translations = array();
				if(file_exists($lf = WFW_ROOT . $iFile))
					include $lf;
				moduleManager()->includeAll($iFile);
				//foreach (moduleManager()->getList() as $root)
				//	$this->includeIfExists($root . $iFile);
			}

			$this->includeIfExists(APP_ROOT . $iFile);

			if(I18N_EXTENDED_LOCALES && $reg)
			{
				moduleManager()->includeAll("registries/" . $reg . "/" . $iFile);
				if($lf = $this->getAbsoluteFile("registries/" . $reg . "/" . $iFile))
					include $lf;
			}
		}

		/**
		 * Split variable path to two parts
		 * @param String $path
		 * @return array<String>
		 */
		function explodePath($path)
		{
			if(strpos($path, CHILD_DELIMITER))
				list($pref, $path2) = explode(CHILD_DELIMITER, $path, 2);
			else
			{
				$pref = $path;
				$path2 = "";
			}
			return array($pref, $path2);
		}

		/**
		 * Sets user property
		 * @param String $name
		 * @param mixed $value
		 * @return void
		 */
		function setUserProperty($name, $value)
		{
			$s = app()->dbo("userproperty");
			$s->userId = $this->user()->getIdValue();
			$s->name = $name;
			$s->find(true);
			$s->value = $value;
			$s->persist();
		}

		/**
		 * Fetches user property from DB
		 * @param String $name
		 * @param mixed $def
		 * @return mixed
		 */
		function getUserProperty($name, $def)
		{
			$s = app()->dbo("userproperty");
			$s->userId = $this->user()->getIdValue();
			$s->name = $name;
			$s->find(true);
			if($s->isInDatabase())
				return $s->value;
			return $def;
		}

		/**
		 * Saves users locale to DB
		 * @param String $l
		 * @return void
		 */
		function saveLocale($l1)
		{
			//TODO save locale to db
			$_SESSION[INSTANCE_WEB][REQUEST_LOCALE] = $l1;
		}

		//Copyright © 2008 Darrin Yeager
		//http://www.dyeager.org/
		//Licensed under BSD license.
		//http://www.dyeager.org/downloads/license-bsd.txt
		/**
		 * Gets preferred locale from browser
		 * @return String
		 */
      	static function getBrowserLanguage()
      	{
         	if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"]))
            	return self::parseBrowserLanguage($_SERVER["HTTP_ACCEPT_LANGUAGE"]);
         	else
            	return self::parseBrowserLanguage(NULL);
        }

		//Copyright © 2008 Darrin Yeager
		//http://www.dyeager.org/
		//Licensed under BSD license.
		//http://www.dyeager.org/downloads/license-bsd.txt
		/**
		 * @param String $http_accept
		 * @param String $defLang
		 * @return String
		 */
      	static function parseBrowserLanguage($http_accept, $deflang = DEFAULT_LOCALE) 
      	{
        	if(isset($http_accept) && strlen($http_accept) > 1)  
         	{
            	//Split possible languages into array
            	$x = explode(",",$http_accept);
            	foreach ($x as $val)
            	{
               		//check for q-value and create associative array. No q-value means 1 by rule
               		if(preg_match("/(.*);q=([0-1]{0,1}.d{0,4})/i",$val,$matches))
                  		$lang[$matches[1]] = (float)$matches[2];
               		else
                		$lang[$val] = 1.0;
            	}

            	//return default language (highest q-value)
            	$qval = 0.0;
            	foreach ($lang as $key => $value)
            	{
               		if ($value > $qval)
               		{
	                  	$qval = (float)$value;
	                  	$deflang = $key;
	               	}
	            }
        	}
        	return strtolower($deflang);
     	} 

		/**
		 * Initializes i18n
		 * @return void
		 */
      	function initLocale()
		{
			if(!isset($this->locale))
			{
				if($l1 = $this->request(REQUEST_LOCALE))
				{
					$this->saveLocale($l1);
					$this->setLocale($l1);
				}
				else
				{
					if(isset($_SESSION[INSTANCE_WEB][REQUEST_LOCALE]))
						$this->setLocale($_SESSION[INSTANCE_WEB][REQUEST_LOCALE]);
				}
				//TODO load locale from db
			}

			if(!isset($this->locale))
				$this->setLocale(DEFAULT_LOCALE);
				//$this->setLocale($this->getBrowserLanguage());
		}

		/**
		 * Returns current locale
		 * @return String
		 */
		function getLocale()
		{
			return $this->locale;
		}

		/**
		 * @return void
		 */
		function setTranslations($t)
		{
			if(defined("I18N_EXTENDED_LOCALES") && I18N_EXTENDED_LOCALES)
				$this->addTranslations($t);
			else
				$this->translations = $t;
		}

		/**
		 * @return void
		 */
		function addTranslations($t)
		{
			$this->translations = array_merge($this->translations, $t);
		}

		/**
		 * @return array<String,String>
		 */
		function getTranslations()
		{
			return $this->translations;
		}

		/**
		 * Returns absolute URL of file based on file existance in filesystem
		 * @param String $s
		 * @return String
		 */
		function url($s = "")
		{
			if(file_exists(INSTANCE_ROOT . $s))
				return INSTANCE_WEB . $s;
			if(file_exists(APP_ROOT . $s))
				return APP_WEB . $s;
			if($fn = moduleManager()->getLastUrl($s))
				return $fn;
			if(file_exists(WFW_ROOT . $s))
				return WFW_WEB . $s;

			return INSTANCE_WEB . $s;
		}

		function icon($fn, $size = 16)
		{
			return "<img src=\"" . $this->url("ui/img/$size/$fn") . "\" width=\"$size\" height=\"$size\" border=\"0\"/>";
		}

		/**
		 * Returns absolute path of file based on file existance in filesystem
		 * @param String $file
		 * @return String
		 */
		function getAbsoluteFile($file)
		{
			if(file_exists($f = INSTANCE_ROOT . $file))
				return $f;
			if(file_exists($f = APP_ROOT . $file))
				return $f;
			if($fn = moduleManager()->getLastAbs($file))
				return $fn;
			if(file_exists($f = WFW_ROOT . $file))
				return $f;
			return "";
		}

		/**
		 * Returns UI form file
		 * @param String $reg registry name
		 * @param String $name form name
		 * @return String
		 */
		function getFormFile($reg, $name)
		{
			if($fn = app()->getAbsoluteFile("registries/$reg/$reg$name"))
				return $fn;
			return app()->getAbsoluteFile("registries/$reg$name");
		}

		/**
		 * Returns current registry name based on request
		 * @return String
		 */
		function getCurrentRegistry()
		{
			return $this->request(REQUEST_REGISTRY);
		}

		/**
		 * Add comment and return result of inserting it to DB
		 * @param string $comment
		 * @param string $objreg
		 * @param int $objId
		 * @param int $userId
		 * @return mixed
		 */
		public function addComment($comment, $objreg = "", $objId = 0, $userId = 0)
		{
			echo "addComment<br/>";
			if(!$comment)
				throw new WFWException("no comment");
			if($objreg = "")
				$objreg = $this->getCurrentRegistry();
			if($objId == 0)
				$objId = $this->request("id");
			if($userId == 0)
				$userId = $this->user()->id;
			$c = app()->dbo("objcomment");
			$c->comment = $comment;
			return $c->insert();
		}

		/**
		 * Loads registry descriptor
		 * @param String $reg registry name
		 * @return void
		 */
		function initRegistryDescriptor($reg)
		{
			$reg = preg_replace("/[^a-zA-Z\s\p{P}]/", "", $reg);
			//TODO strip from request everything except a-z, A-Z
	 		if($fn = $this->getAbsoluteFile("registries/" . $reg . "/" . $reg . ".rd.php"))
 				include_once($fn);
 			else
	 			if($fn = $this->getAbsoluteFile("registries/" . $reg . ".rd.php"))
 					include_once($fn);

 			$rdClassName = ucfirst($reg) . "RegistryDescriptor";
 			if(!class_exists($rdClassName))
 			{
 				$rdClassName = DEFAULT_REGISTRYDESCRIPTOR_CLASSNAME;
		 		if(!class_exists($rdClassName))
		 		{
		 			$this->showError(t("No registry descriptor for") . " " . $reg);
		 			die();
		 		}
 			}

	 		app()->setRegistryDescriptor(new $rdClassName($reg));
		}

		/**
		 * Checks if registry descriptor initialized
		 * @return bool
		 */
		function hasRegistryDescriptor()
		{
			if(!$this->registryDescriptorInitialized)
				$this->initRegistryDescriptor();
			return is_object($this->registryDescriptor);
		}

		/**
		 * Returns registry descriptor
		 * @param String $reg registry name
		 * @return RegistryDescriptor
		 */
		function getRegistryDescriptor($reg = "")
		{
			if(!$reg)
				$reg = $this->getCurrentRegistry();
			if(!$this->registryDescriptorInitialized)
				$this->initRegistryDescriptor($reg);
			return $this->registryDescriptor;
		}

		/**
		 * Set registry descriptor
		 * @param RegistryDescriptor $r
		 * @return void
		 */
		function setRegistryDescriptor($r)
		{
			$this->registryDescriptor = $r;
		}

		/**
		 * Initialize application
		 */
		function __construct()
		{
			$this->checkSetup();

			if(defined("SERVER_TIMEZONE"))
				date_default_timezone_set(SERVER_TIMEZONE);
		}

		/**
		 * Check setup and set defaults
		 * @return void
		 */
		function checkSetup()
		{
			foreach ($this->getDefaultDefines() as $k => $v)
				if(!defined($k))
					define($k, $v);
		}

		public function getDefaultDefines()
		{
			return array(
						"STRUCTURE_COLLATION" => "utf8_general_ci",
						"FORMATSTRING_DATE_HUMAN" => "d.m.Y",
						"FORMATSTRING_TIME_HUMAN" => "H:i",
						"FORMATSTRING_DATETIME_HUMAN" => "d.m.Y H:i",
						"FORMATSTRING_DATETIME_SHORT_HUMAN" => "d.m.Y H:i",
						"I18N_EXTENDED_LOCALES" => true,
						"CONTEXT_PROVIDER_FLAVOR" => "UserFilesContextProvider",

						"OBJLOG_ENABLED" => true,

						"SETUP_TOOLBAR_CAPTIONS_VISIBLE" => false,
						"REPORT_FONTFAMILY" => "freesans",
						"DEBUG" => false,
						"DEFAULT_LOCALE" => "en",
						"ENTER_AS_TAB" => false,
						"SETUP_CHARSET" => "utf-8",
						"TRANSLATED_WATCH" => false,
						"CONTEXT_AUTOSAVE" => false,
						"PWD_MIN_LENGTH" => 5,
						"RPCONTROL_FILES_ENABLED" => true,
						"RPCONTROL_MESSAGES_ENABLED" => false,
						"RPCONTROL_COMMENTS_ENABLED" => true,

						"CURRENCY_DEFAULT_N1" => "EUR",
						"CURRENCY_DEFAULT_N2" => "EUR",
						"CURRENCY_DEFAULT_D1" => "c",
						"CURRENCY_DEFAULT_D2" => "c",
						"FORMATSTRING_DATEPICKER" => "dd.mm.yy",
						"SERVER_TIMEZONE" => "Europe/Tallinn",

						//TODO get rid of it
						//"SETUP_3RD_COMBOGRID_CSS" => L3RD_WEB . "combogrid-1.5.0/resources/css/smoothness/jquery-ui-1.8.9.custom.css",
						"SETUP_3RD_COMBOGRID_CSS2" => L3RD_WEB . "combogrid-1.5.0/resources/css/smoothness/jquery.ui.combogrid.css",


						"L3RD_FONT_AWESOME_CSS" => L3RD_WEB . "font-awesome/css/font-awesome.min.css",
						"L3RD_METISMENU_JS" => L3RD_WEB . "metisMenu/metisMenu.min.js",
						"L3RD_METISMENU_CSS" => L3RD_WEB . "metisMenu/metisMenu.min.css",
						//"L3RD_BOOTSTRAP_CSS" => "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/css/bootstrap.min.css",
						//"L3RD_BOOTSTRAP_JS" => "https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/3.3.6/js/bootstrap.min.js",
						"SETUP_CSS_MAIN" => WFW_WEB . "ui/wfw.css",
						"SETUP_HOTKEYS" => L3RD_WEB . "jquery.hotkeys.js",
						"SETUP_3RD_XLS" => L3RD_ROOT . "phpexcel/Classes/PHPExcel.php",
						"SETUP_3RD_MULTISELECT_JS" => L3RD_WEB . "jquery.multiselect.js",
						"SETUP_3RD_MULTISELECT_CSS" => L3RD_WEB . "jquery.multiselect.css",
						"SETUP_JQUERY" => L3RD_WEB . "jquery.js",
						"SETUP_JQUERY_MIGRATE" => L3RD_WEB . "jquery-migrate-1.3.0.js",
						"SETUP_JQUERY_UI" => L3RD_WEB . "jquery-ui/jquery-ui.js",
						"SETUP_JQUERY_UI_CSS" => L3RD_WEB . "jquery-ui/jquery-ui.css",

						"L3RD_MORRIS_JS" => L3RD_WEB . "morris/morris.js",
						"L3RD_MORRIS_CSS" => L3RD_WEB . "morris/morris.css",
						"L3RD_RAPHAEL_JS" => L3RD_WEB . "raphael-min.js",

						//"SETUP_JQUERY_UI" => L3RD_WEB . "combogrid-1.5.0/resources/jquery/jquery-ui-1.8.9.custom.min.js",
						"SETUP_JQUERY_I18N" => L3RD_WEB . "jquery-ui-i18n.js",
						"SETUP_COMBOGRID" => L3RD_WEB . "combogrid-1.5.0/resources/plugin/jquery.ui.combogrid-1.5.0.js",

						"SETUP_TCPDF_CLASS" => L3RD_ROOT . "tcpdf/6_2_6/tcpdf.php",

						//"SETUP_TINY_MCE" => L3RD_WEB . "tinymce/jscripts/tiny_mce/",

						"SOFTWAREISSUES_EMAIL_ON_INSERT" => true,
						"DEVELOPER_USER" => "admin",
						"CLEAR_CONTECTS_ON_LOGIN" => false,
						"UI_MODULE" => "ui/old/",


						//TODO remove, backward compatibility only
						"WEB_ROOT" => INSTANCE_WEB,
				);
		}

		/**
		 * initializes password validators
		 * @return array<PasswordValidator>
		 */
		protected function initPasswordValidators()
		{
			return array(
				new ShortPasswordValidator(),
				new SameWithUsernamePasswordValidator(),
				new UppercaseCharPasswordValidator(),
				new LowerCharPasswordValidator(),
				new NumberPasswordValidator(),
				);
		}

		private $passwordValidators;
		/**
		 * returns password validators
		 * @return array<PasswordValidator>
		 */
		public function getPasswordValidators()
		{
			if(!isset($this->passwordValidators))
				$this->passwordValidators = $this->initPasswordValidators();
			return $this->passwordValidators;
		}

		/**
		 * Current user
		 * @return FWWebuser
		 */
		function user()
		{
			if(!is_object($this->user))
				$this->auth();
			return $this->user;
		}

		/**
		 * System user
		 * @return FWWebuser
		 */
		function system()
		{
			if(!is_object($this->system))
			{
				$this->system = app()->dbo("webuser");
				$this->system->uid = "system";
				$this->system->find(true);
				$this->system->loadDynamicProperties();
			}
			return $this->system;
		}

		/**
		 * Return true if current request is in debug mode
		 * Debug mode could be enabled by adding to request "debug=1" or "dbg=1"
		 * Debug mode possible only if constant DEBUG = true (by default false, could be set in app instance setup)
		 * @return bool
		 */
		function isDebug()
		{
			return DEBUG && ((0 < 0 + $this->request("debug") + $this->request("dbg")) || (php_sapi_name() == "cli"));
		}

		/**
		* prints debug data to output
		* @param String $s
		* @return void
		*/
		function debug($s)
		{
			if($this->isDebug())
				echo $s . "<br/>\n";
		}

		/**
		* Instanciates new DB_Dataobject object by table name
		* @param String $s table name
		* @return DB_DataObject
		*/
		function dbo($s)
		{
			return DB_DataObject::Factory($s);
		}

		/**
		* Instanciates new DB_Dataobject object by table name and loads it using primary key value
		* @param String $table table name
		* @param mixed $id ID
		* @return DB_DataObject
		*/
		function get($table, $id)
		{
			$o = DB_DataObject::Factory($table);
			if($this->isDBError($o))
				return null;
			$f = $o->getPrimaryKeyField();
			$o->$f = $id;
			if($o->find(true))
				return $o;
			else
				return null;
		}

		/**
		* Change password for current user
		* @param String $old old password
		* @param mixed $new new password
		* @return String
		*/
		function passwd($old, $new)
		{
			$u = $this->dbo("webuser");
			$u->whereAdd("uid = '" . $u->escape($this->user->uid) .
					"' and pwd = password('" . $u->escape($old) . "') " .
					" and state = " . STATE_NORMAL);
			if(!$u->find(true))
				return app()->jsonMessage(RESULT_ERROR, t("Old password does not match"));

			$u->pwd = $new;
			if($err = $u->getPasswordError())
			{
				$a = array();
				foreach (app()->getPasswordValidators() as $validator)
					$a[] = t($validator->getErrorMessage());

				return app()->jsonMessage(RESULT_ERROR, implode("\n", $a));
			}

			$sql = "update " . $this->user->__table .
				" set pwd = password('" . $this->user->escape($new) . "')" .
				" where pwd = password('" . $this->user->escape($old) . "')" .
				" and uid = '" . $this->user->escape($this->user->uid) . "'";

			$this->user->getDatabaseConnection()->query($sql);

			app()->addWarning(new Warning("Password changed"));

			return app()->jsonMessage(RESULT_OK, "ok");
		}

		/**
		* Initialize current user and fix database connection
		* @return void
		*/
		private function initConnection()
		{
			$this->user = DB_DataObject::Factory("webuser");
			if($this->isDBError($this->user))
				throw new WFWException("Cant initialize DB connection");
			$this->user->getDatabaseConnection()->query("SET NAMES utf8");
		}

		/**
		* Used when application is runned from CLI, for example cron tasks or database updates
		* @return void
		*/
		function localAuth()
		{
			$this->initConnection();
			$this->user->setIdValue(SYSTEM_USER_ID);
			$this->user->find(true);
		}

		/**
		* Login and output results as JSON
		*/
		function loginJson()
		{
			session_start();
			$this->doAuth();
			if($this->user->getIdValue())
				echo app()->jsonMessage();
			else
				echo app()->jsonMessage(RESULT_ERROR, "login unsuccessfull");
		}

		/**
		* User authentication
		*/
		private function doAuth()
		{
			$this->initConnection();
			$this->user->sessionid = session_id();
			$this->user->closed = 0;
			$found = $this->user->find(true);

			if(!$found)
				$this->login();
		}

		/**
		* User authentication, login/logout
		*/
		function auth()
		{
			if(php_sapi_name() !== "cli")
			{
				session_start();
				header("Content-Type:text/html; charset=utf-8");
			}

			$this->doAuth();

			if($this->user->getIdValue())
			{
				if($this->request(AUTH_ACTION) == AUTH_ACTION_LOGOUT)
					return $this->logout();

				$this->loadRights();
			}

			//if(file_exists(APP_ROOT . "i18n/index.php"))
			//	include APP_ROOT . "i18n/index.php";
			if($fn = $this->getAbsoluteFile("i18n/index.php"))
				include $fn;
			else
				$this->setLocalesList(array(DEFAULT_LOCALE));
			$this->initLocale();
		}

		/**
		* Client IP
		* @return String
		*/
		function getClientIp()
		{
			return isset($_SERVER["REMOTE_ADDR"]) && $_SERVER["REMOTE_ADDR"] ? $_SERVER["REMOTE_ADDR"] : "--";
		}

		/**
		* Client IP as DB object
		* @return DB_DataObject
		*/
		function getUserIp()
		{
			$i = app()->dbo("userip");
			$i->ip = $this->getClientIp();
			if(!$i->find(true))
				$i->insert();
			return $i;
		}

		/**
		* Client UA
		* @return String
		*/
		function getClientUserAgent()
		{
			return $_SERVER["HTTP_USER_AGENT"];
		}

		/** @var DB_DataObject user agent*/
		private $ua;

		/**
		* Client UA as DB object
		* @return DB_DataObject
		*/
		function getUserAgent()
		{
			if(!isset($this->ua))
			{
				$this->ua = app()->dbo("useragent");
				$this->ua->rawdata = $this->getClientUserAgent();
				if(!$this->ua->find(true))
					$this->ua->insert();
			}
			return $this->ua;
		}

		/**
		* Save user statistics
		* @return void
		*/
		public function addStat($name, $memo, $status = "", $type = USERSTATTYPE_STATISTICS)
		{
			$this->addUserStat($this->user, $name, $memo, $status, $type);
		}

		/**
		* Save user statistics for user
		* @return void
		*/
		public function addUserStat($user, $name, $memo, $status = "", $type = USERSTATTYPE_STATISTICS)
		{
			$us = $this->dbo("userstats");
			$us->userId = $user->getIdValue();
			$us->useripId = $this->getUserIp()->id;
			$us->useragentId = $this->getUserAgent()->id;
			$us->name = $name;
			$us->memo = $memo;
			$us->status = $status;
			$us->actorId = $this->user->id;
			$us->typeId = $type;
			$us->insert();
		}

		/**
		* Object caption with link (if object is viewable for current user)
		* @param WFWObject $obj
		* @param String $caption
		* @param String target browser's target
		* @return String
		*/
		function getLinkToObject($obj, $caption, $target = "_blank")
		{
	    	if(is_object($obj))
	    	{
	    		if($this->hasRight(PRIVILEGE_SELECT, $obj->__table))
		    		return "<a href=\"" . $obj->getDocOpenUrl() . "\"" .
		    			($target ? " target=\"$target\"" : "") .
		    			">" . t($caption) . "</a>";
	    	}
	    	return "";
		}

		/**
		* Object caption
		* @param WFWObject $obj
		* @param String $caption
		* @param String target browser's target
		* @return String
		* @return String
		*/
		function getUnlinkedCaption($obj, $fields = null, $sep = " : ")
		{
	    	if(is_object($obj))
		    	return $obj->getCaption($fields, $sep);
	    	else
	    		return "";
		}

		/**
		* Object objects caption with link (if object is viewable for current user)
		* @param WFWObject $obj
		* @param array<String> $fields
		* @param String $sep fields separator in caption
		* @param String target browser's target
		* @return String
		*/
	    function getLinkedCaption($obj, $fields = null, $sep = null, $target = "_blank")
	    {
	    	if(is_object($obj))
	    	{
	    		if($this->hasRight(PRIVILEGE_SELECT, $obj->__table))
		    		return "<a href=\"" . $obj->getDocOpenUrl() . "\"" .
		    			($target ? " target=\"$target\"" : "") .
		    			">" . $obj->getCaption($fields, $sep) . "</a>";
		    	else
		    		return $obj->getCaption($fields, $sep);
	    	}
	    	else
	    		return "";
	    }

		/**
		* Object objects translated caption with link (if object is viewable for current user)
		* @param WFWObject $obj
		* @param array<String> $fields
		* @param String $sep fields separator in caption
		* @param String target browser's target
		* @return String
		*/
	    function getLinkedCaptionTranslated($obj, $fields = null, $sep = null, $target = "_blank")
	    {
	    	if(is_object($obj))
	    	{
	    		if($this->hasRight(PRIVILEGE_SELECT, $obj->__table))
		    		return "<a href=\"" . $obj->getDocOpenUrl() . "\"" .
		    			($target ? " target=\"$target\"" : "") .
		    			">" . $obj->getCaptionTranslated($fields, $sep) . "</a>";
		    	else
		    		return $obj->getCaptionTranslated($fields, $sep);
	    	}
	    	else
	    		return "";
	    }

		/**
		* Fetches query results and returns it as array
		* @param String sql
		* @param $fetchMode
		* @param array<String,String> $formatters formatters for specific fields
		* @return array
		*/
	    function queryAsArray($sql, $fetchMode = DB_FETCHMODE_ASSOC, $formatters = null)
	    {
	    	$q = $this->query($sql);
	    	$ret = array();
	    	$o = array();
	    	if($this->isDBError($q))
	    	{
	    		die("Error executing SQL: " . $sql);
	    	}
	    	while($q->fetchInto($o, $fetchMode))
	    	{
	    		if(is_array($formatters))
	    		{
	    			foreach ( $formatters as $field => $format )
	    			{
	    				if(is_array($o))
		    				if(isset($o[$field]))
		    					$o[$field] = getFormatter($format)->encodeHuman($o[$field]);
	    				if(is_object($o))
		    				if(isset($o->$field))
		    					$o->$field = getFormatter($format)->encodeHuman($o->$field);
	    			}
	    		}
	    		$ret[] = $o;
	    	}
	    	return $ret;
	    }

	    /** @var UIHelper */
		private $_uiHelper;

		/**
		* UI helper
		* @return UIHelper
		*/
		function uiHelper()
		{
			if(!isset($this->_uiHelper))
				$this->_uiHelper = new UIHelper();
			return $this->_uiHelper;
		}

		private $_uiModule;

		function ui()
		{
			if(!isset($this->_uiModule))
			{
				$fn = $this->getAbsoluteFile(UI_MODULE . "index.php");
				if($fn)
					require_once $fn;
				else
					$this->panic("No UI module found");
			}
			return $this->_uiModule;
		}


		/**
		* Fetches query and returns as array of Strings
		* @param String sql
		* @param String $separator
		* @return array<String>
		*/
	    function queryAsStringsArray($sql, $separator = " ")
	    {
	    	$q = $this->query($sql);
	    	$ret = array();
	    	$o = array();
	    	while($q->fetchInto($o, DB_FETCHMODE_ASSOC))
	    	{
	    		$ret[] = implode($separator, $o);
	    	}
	    	return $ret;
	    }

		/**
		 * Execute database query
		 * @param String $sql
		 * @return Statement
		 */
		function query($sql)
		{
			if(!isset($this->user))
				$this->user();
			if(!is_object($this->user))
				$this->user();
			$c = $this->user->getDatabaseConnection();
			$q =& $c->query($sql);
			return $q;
		}

		/**
		 * Execute database query and fetch first row first field data
		 * @param String $sql
		 * @return mixed
		 */
		function valFromDB($sql)
		{
			$r = $this->rowFromDB($sql);
			return $r[0];
		}

		/**
		 * Execute database query and fetch first row
		 * @param String $sql
		 * @return array
		 */
		function rowFromDB($sql, $fetchMode = DB_FETCHMODE_ORDERED)
		{
			$q = $this->query($sql);
			$row = array();
			if($this->isDBError($q))
				die("DBError in rowFromDB: " . $sql . "<hr/><pre><small>" . print_r($q, true));
			$q->fetchInto($row, $fetchMode);
			return $row;
		}

		/**
		 * Load current user's rights
		 */
		function loadRights()
		{
			$sql = "select o.name, max(r.s), max(r.u), max(r.d), max(r.l) " .
					"from objectright r " .
					"inner join robject o on o.id = r.registryID " .
					"inner join userrole u on u.roleid = r.roleid and u.userid = " . $this->user->getIdValue() .
					" group by o.name";
			$c = $this->user->getDatabaseConnection();
			$q =& $c->query($sql);
			$row = array();
			$rights = array();
			while($q->fetchInto($row, DB_FETCHMODE_ORDERED))
			{
				$o = array(
					PRIVILEGE_SELECT => $row[1],
					PRIVILEGE_UPDATE => $row[2],
					PRIVILEGE_DELETE => $row[3],
					PRIVILEGE_LOCK => $row[4]
				);
				$rights[$row[0]] = $o;
			}
			$this->user->rights = $rights;
		}

		/**
		 * Checks for current users specific right on specific registry (current if empty)
		 * @param String $right
		 * @param String $registry registry (current if empty)
		 * @return bool
		 */
		private function hasRight($right, $registry = "")
		{
			if(!$registry)
				$registry = $this->getCurrentRegistry();
			return $this->user->getIdValue() && isset($this->user->rights[$registry]) && is_array($this->user->rights[$registry]) && isset($this->user->rights[$registry][$right]) && $this->user->rights[$registry][$right];
		}

		/**
		 * Execute if current user can view specific registry (current if empty)
		 * @param String $s registry (current registry if empty)
		 * @return mixed
		 */
		function canSelect($s = "")
		{
			return $this->hasRight(PRIVILEGE_SELECT, $s);
		}

		/**
		 * Execute if current user can update specific registry (current if empty)
		 * @param String $s registry (current registry if empty)
		 * @return mixed
		 */
		function canUpdate($s = "")
		{
			return $this->hasRight(PRIVILEGE_UPDATE, $s);
		}

		/**
		 * Execute if current user can delete rows from specific registry (current if empty)
		 * @param String $s registry (current registry if empty)
		 * @return mixed
		 */
		function canDelete($s = "")
		{
			return $this->hasRight(PRIVILEGE_DELETE, $s);
		}

		/**
		 * Execute if current user can lock documents in specific registry (current if empty)
		 * @param String $s registry (current registry if empty)
		 * @return mixed
		 */
		function canLock($s = "")
		{
			return $this->hasRight(PRIVILEGE_LOCK, $s);
		}

		function escape($s)
		{
			return $this->user->escape($s);
		}

		public function getContextByPath($path)
		{
			if($path)
			{
				list($pref, $path2) = $this->explodePath($path);
				if(is_object($context = $this->getContext($pref)))
					return $context;
			}
			throw new NoPathException(ERROR_NO_PATH);
		}

		/**
		 * Login user
		 */
		function login()
		{
			if(isset($_REQUEST[AUTH_ACTION]) && $_REQUEST[AUTH_ACTION] == AUTH_ACTION_LOGIN)
			{
				unset($this->user->sessionid);
				$password = $_REQUEST[AUTH_ACTION_LOGIN_PWD];
				$sql = "uid = " . quote($_REQUEST[AUTH_ACTION_LOGIN_UID]) .
					" and pwd = password(" . quote($password) . ") " .
					" and state = " . STATE_NORMAL .
					" and closed = 0";
				$this->user->whereAdd($sql);
				$found = $this->user->find(true);

				if($this->user->getIdValue())
				{
					$this->user->sessionid = session_id();
					$this->user->setLogEnabled(false);
					$this->user->update();
					$this->user->setLogEnabled(true);
					$this->addStat("login", "success", "ok");

					if(CLEAR_CONTECTS_ON_LOGIN)
						$this->getContextProvider()->clearContexts();

					//check password errors
					$u = app()->get("webuser", $this->user->getIdValue());
					$u->pwd = $password;
					if($err = $u->getPasswordError())
						$this->addWarning(new Warning("requirePasswd", "", WARNING_ERROR));
				}
				else
				{
					$this->addStat("login failed", $_REQUEST[AUTH_ACTION_LOGIN_UID], "failed", USERSTATTYPE_SECURITY);
					sleep(2);
				}
			}
		}

		/**
		 * Logout user and reset users data
		 */
		function logout()
		{
			session_destroy();
			if($this->user->getIdValue())
			{
				$this->user->sessionid = "-";
				$this->user->setLogEnabled(false);
				$this->user->update();
				$this->user->setLogEnabled(true);
				$this->addStat("logout", "success", "ok");
				$this->user = DB_DataObject::Factory("webuser");
			}
		}

		/**
		 * Return current DB connection
		 * @return Connection
		 */
		function getDBConnection()
		{
			return $this->user()->getDatabaseConnection();
		}

		/**
		 * Request variable
		 * @param $s var name
		 * @param $def default value if var is not in request ("" by default)
		 * @return String
		 */
		function request($s, $def = "")
		{
			if(isset($_REQUEST[$s]))
				return $_REQUEST[$s];
			else
				return $def;
		}

		/**
		 * If user is not logged in, redirects to login form and dies
		 */
		function requireLogin()
		{
			if(!$this->user()->getIdValue())
				$this->location("?action=loginform");
		}

		/**
		 * If user is not logged in, dies with JSON encoded message
		 */
		function requireLoginJson()
		{
			if(!$this->user()->getIdValue())
				die($this->jsonMessage(RESULT_ERROR, MSG_NOT_LOGGED_IN));	//TODO consider throwing exception
		}

		/**
		 * If user does not have specific privilege on specific registry, dies with JSON encoded message
		 */
		function requirePrivilegeJson($privilege, $reg = "")
		{
			$this->requireLoginJson();
			if(!$this->hasRight($privilege, $reg))
				die($this->jsonMessage(RESULT_ERROR, t(MSG_INSUFFICIENT_RIGHTS)));	//TODO consider throwing exception
		}

		/**
		 * If user does not have specific privilege on specific registry, dies with JSON encoded message
		 */
		function requirePrivilege($privilege, $reg = "")
		{
			$this->requireLogin();
			if(!$this->hasRight($privilege, $reg))
			{
				$this->addStat("unprivileged", ($reg ? $reg : $this->getCurrentRegistry()) . "." . $privilege, "failed", USERSTATTYPE_SECURITY);
				$this->location();
			}
		}

		/**
		 * Redirect to specified location in app (app index page by default)
		 * @param $s relative URI to redirect
		*/
		function location($s = "")
		{
			header("Location: " . $this->url($s));
			die();
		}

		/**
		 * Create temp file
		 * @param String $s file name
		 * @return String
		 */
		function tempFile($s)
		{
			if(!file_exists($p1 = INSTANCE_ROOT . USERFILES . "tmp/"))
				mkdir($p1);
			return $p1 . $s;
		}

		/**
		 * Construct JSON encoded message with request execution results
		 * @param String $state
		 * @param String $msg
		 * @param array $additional
		 * @return String
		 */
		function jsonMessage($state = RESULT_OK, $msg = "", $additional = null)
		{
			$obj = new EmptyObject();
			$obj->state = $state;
			$obj->message = $msg;

			if(count($this->updatedFields))
			{
				if(!is_array($additional))
					$additional = array();
				$additional[FIELD_UPDATE] = $this->updatedFields;
			}

			if(is_array($additional))
			{
				reset($additional);
				while(list($k, $v) = each($additional))
					$obj->$k = $v;
			}

			if($this->requireReload)
				$obj->reload = true;
			if($this->requireReloadPage)
				$obj->reloadPage = true;

			if(is_array($this->warnings))
				if(count($this->warnings))
					$obj->warnings = $this->warnings;

			if(defined("DEBUG_COMMENTS_ALLOWED") && DEBUG_COMMENTS_ALLOWED)
				if(isset($this->comments) && is_array($this->comments))
						if(count($this->comments))
							$obj->comments = $this->comments;

			return json_encode($obj);
		}

		/**
		 * Add debug comments to output with jsonMessage
		 * @param String $s comment
		 * @return String
		 */
		function addDebugComment($s)
		{
			if(defined("DEBUG_COMMENTS_ALLOWED") && DEBUG_COMMENTS_ALLOWED)	//TODO do we need this check here?
				$this->comments[] = $s;
		}

		/** @var Context */
		private $_context;

		/**
		 * @return Context
		 */
		function getCurrentContext()
		{
			return $this->_context;
		}

		/** @var ContextProvider */
		private $_contextProvider;

		/**
		 * @return ContextProvider
		 */
		function getContextProvider()
		{
			if(!isset($this->_contextProvider))
				$this->_contextProvider = ContextProvider::getInstance();
			return $this->_contextProvider;
		}

		/**
		 * retrieves context from session and returns it
		 * @return Context
		 */
		function getContext($name)
		{
			$this->_context = $this->getContextProvider()->getContext($name);
			return $this->_context;
		}

		/**
		 * Saves context data to session
		 * @param Context $context
		 * @return void
		 */
		function putContext($context)
		{
			$this->getContextProvider()->putContext($context);
		}

		/** @var array<String, mixed> changed fields*/
		private $changedFields;

		/**
		 * @return void
		 */
		public function initChangedFields()
		{
			$this->changedFields = array();
		}

		/**
		 * @param String $f
		 * @param mixed $v
		 * @return void
		 */
		public function addChangedField($f, $v)
		{
			if(!is_array($this->changedFields))
				$this->changedFields = array();
			$this->changedFields[$f] = $v;
		}

		/** @var array<FWRobject> list of registries current user can view*/
		private $regs;

		/**
		 * List of registries current user can view
		 * @return array<FWRobject>
		 */
		function registries()
		{
			if(!is_array($this->regs))
			{
				$this->regs = array();
				$obj = $this->dbo("robject");
				$obj->whereAdd("id in (select distinct r.registryID " .
						"from objectright r " .
						"inner join userrole u on u.roleid = r.roleid " .
						"and u.userid = " . $this->user->getIdValue() . " and r.s = 1)");
				$obj->orderBy("pos asc, id asc");
				//$obj->state = STATE_NORMAL;
				if($obj->find())
					while($obj->fetch())
						$this->regs[] = clone $obj;
			}

			return $this->regs;
		}

		/**
		 * Return new DB updater object
		 * @return DBUpdater
		 */
		function updater()
		{
			return new DBUpdater();
		}

		/**
		 * checks if $obj is DB error object
		 * @param DB_DataObject|DB_DataObject_Error|DB_Error $obj
		 * @return bool
		 */
		function isDBError($obj)
		{
			$c = get_class($obj);
			return ($c == "DB_DataObject_Error") || ($c == "DB_Error");
		}

		/**
		 * Link to help for current registry
		 * @return String
		 */
		function helpLink()
		{
			if($r = $this->request(REQUEST_REGISTRY))
			{
				$hls = "helpLink_" . $r . "_" . $this->locale;
				if(isset($_SESSION[INSTANCE_WEB][$hls]))
				{
					if($_SESSION[INSTANCE_WEB][$hls])
						return $_SESSION[INSTANCE_WEB][$hls];
				}
				else
				{
					$ro = app()->dbo("robject");
					$ro->name = $r;
					$ro->find(true);
					if($ro->getIdValue())
					{
						$ro->loadDynamicProperties();
						$fld = "dynHelpLink_" . $this->locale;

						if(isset($ro->$fld) && ($ro->$fld))
						{
							$_SESSION[INSTANCE_WEB][$hls] = $ro->$fld;
							return $ro->$fld;
						}
						else
							$_SESSION[INSTANCE_WEB][$hls] = "";
					}
				}
			}

			$helpLink = t("help_link");
			if("help_link" == $helpLink)
				return "";
			else
				return $helpLink;
		}

		/**
		 * @var array settings
		 * @deprecated since 2.0
		 */
		private $_settings;

		/**
		 * @deprecated since 2.0
		 */
		function getSetting($name, $default = "")
		{
			if(!isset($this->_settings))
			{
				$q = $this->query("select name, value from setup");
				$this->_settings = array();
				$o = array();
				while($q->fetchInto($o, DB_FETCHMODE_ORDERED))
					$this->_settings[$o[0]] = $o[1];
			}
			if(isset($this->_settings[$name]))
				return $this->_settings[$name];
			return $default;
		}

		/**
		 * Translate number (0-999)
		 * @param mixed @n number (0 - 999)
		 * @param String @pow power of n
		 * @return String
		 */
		private function tptN2W($n, $pow)
		{
			$p = t("nwpower" . $pow);
			if($n == 0)
				return "";
			if($n == 1)
				return t("n1") . " " . $p;

			$n1 = (int)($n / 100);
			$n2 = (int)(($n - $n1 * 100 ) / 10);
			$n3 = (int)(($n - $n1 * 100 - $n2 * 10));

			$rn1 = $n1 ? t("n_" . $n1) . t("hundred") : "";
			$rn3 = $n3 ? t("n_" . $n3) : "";

			if($n2)
			{
				if($n2 == 1)
				{
					if($n3)
					{
						$rn2 = $rn3 . t("teen");
						$rn3 = "";
					}
					else
						$rn2 = t("n_10");
				}
				else
					$rn2 = t("n_" . $n2) . t("ty");
			}

			return  trim($rn1 . " " . $rn2 . " " . $rn3 . " " . $p);
		}

		/**
		 * not used anywhere
		 * initialized values moved to checkSetup
		 * @deprecated since 2.0
		 */
		function initCurrencySetup()
		{
			if(!defined("CURRENCY_DEFAULT_N1"))
				define("CURRENCY_DEFAULT_N1", "EUR");
			if(!defined("CURRENCY_DEFAULT_N2"))
				define("CURRENCY_DEFAULT_N2", "EUR");
			if(!defined("CURRENCY_DEFAULT_D1"))
				define("CURRENCY_DEFAULT_D1", "c");
			if(!defined("CURRENCY_DEFAULT_D2"))
				define("CURRENCY_DEFAULT_D2", "c");
		}

		/**
		 * Translate number in words.
		 *
		 * @param mixed $nn number to translate
		 * @param String $cur currency code
		 * @param String $sCur2 currency code plural
		 * @param String $sCent currency cents name
		 * @param String $sCent2 currency cents name plural
		 * @return String
		 */
		function numberInWords($nn, $cur = "", $sCur2 = "", $sCent = "", $sCent2 = "")
		{
			if(is_object($cur))
			{
				$sCur = $cur->code;
				$sCur2 = $cur->code;
				$sCent = CURRENCY_DEFAULT_D1;
				$sCent2 = CURRENCY_DEFAULT_D2;
			}
			else
				$sCur = $cur;

			if(!($cur || $sCur2 || $sCent || $sCent2))
			{
				$sys = $this->system();
				$sys->loadDynamicPropertiesIfNotLoaded();
				if(isset($sys->dynCurrency) && $sys->dynCurrency)
					$cur = app()->get("currency", $sys->dynCurrency);
				if(is_object($cur))
				{
					$sCur = $cur->code;
					$sCur2 = $cur->code;
					$sCent = CURRENCY_DEFAULT_D1;
					$sCent2 = CURRENCY_DEFAULT_D2;
				}
			}

			if(!($cur && $sCur2 && $sCent && $sCent2))
			{
				app()->initCurrencySetup();
				$sCur = CURRENCY_DEFAULT_N1;
				$sCur2 = CURRENCY_DEFAULT_N2;
				$sCent = CURRENCY_DEFAULT_D1;
				$sCent2 = CURRENCY_DEFAULT_D2;
			}



			$niwm = "t_nrInWords_" . $this->getLocale();
			if(function_exists($niwm))
				return $niwm($nn, $sCur, $sCur2, $sCent, $sCent2);


			$n = $nn;
			$prefix = "";
			if($n < 0)
			{
				$prefix = t("minus");
				$n = -$n;
			}

			$n1 = "" . (int)$n;
			$n2 = round($n -$n1, 2) * 100;

			$arr = array();
			while($n1)
			{
				$arr[] = substr($n1, -3);
				$n1 = strlen($n1) > 3 ? substr($n1, 0, strlen($n1) - 3) : "";
			}

			$nx = "";

			reset($arr);
			for($x = 0; $x < $cnt = count($arr); $x++)
				$nx = $this->tptN2W($arr[$x], $x) . " " . $nx;

			$ret = trim($prefix . " " . trim($nx) . " " . t("euros") . ($n2 ? " " . t("and") . " " . $n2 . " " . t("cents") : ""));

			$ret = strtoupper(substr($ret, 0, 1)) . substr($ret, 1);

			return $ret;
		}

		/** @var array list of messages for client JS */
		public $clientTranslations = array(
			"Delete document?",
			"Delete row?",
			"unlock document",
			"Add row",
			"Visible columns",
			"New",
			"files",
			"messages",
			"send message",
			"reciever",
			"subject",
			"send",
			"cancel",
			"Loading...",
			"Filter",
			"logout",
			"Change locale",
			"User profile",
			"saving",
			"making copy",
			"sending email",
			"locking",
			"unlocking",
			"Leave unsaved document?",
			"cant delete new document",
			"Yes",
			"No",
		);

		/**
		 * Add own client translated messages
		 * @param array $arr
		 */
		function addClientTranslations($arr)
		{
			$this->clientTranslations = array_merge($this->clientTranslations, $arr);
		}

		/**
		 * Add own client translation
		 * @param string $m message
		 * @param string $t translation
		 */
		function addClientTranslation($m)
		{
			$this->clientTranslations[] = $m;
		}

		/**
		 * Fetches query and outputs as Excel file
		 * @todo not yet implemented
		 */
		function queryToXLS($sql)
		{

		}

		/**
		 * outputs array as excel file
		 * @param array $a array to be exported
		 * @param String $sheetName
		 * @param String $fileName
		 */
		function arrayToXLS($a, $sheetName = "Sheet1", $fileName = "export.xls")
		{
			$w = new XLSExporter();
			$y = 0;
			foreach ($a as $row)
			{
				$y++;
				$x = 0;
				foreach ($row as $cell)
				{
					$x++;
					$c = $w->getCoord($x, $y);
					$w->write($c, $cell);
				}
			}
			$w->download();
		}

		/**
		 * machine encoded current date and time, see FORMATSTRING_DATETIME_MACHINE for format definition
		 * @return String
		 */
		function now()
		{
			return date(FORMATSTRING_DATETIME_MACHINE);
		}

		/**
		 * human readable current date and time, see FORMAT_DATETIME for format definition
		 * @return String
		 */
		function nowHumanReadable()
		{
			return getFormatter(FORMAT_DATETIME)->encodeHuman(app()->now());
		}

		/**
		 * machine encoded current date, see FORMATSTRING_DATE_MACHINE for format definition
		 * @return String
		 */
		function today()
		{
			return date(FORMATSTRING_DATE_MACHINE);
		}

		/**
		 * human readable current date, see FORMAT_DATE for format definition
		 * @return String
		 */
		function todayHumanReadable()
		{
			return getFormatter(FORMAT_DATE)->encodeHuman(app()->today());
		}

		private $savedDebugMicrotime;

		public function isTimingStatisticsEnabled()
		{
			return defined("TIMING_STATISTICS") && TIMING_STATISTICS;
		}

		public function startTimer()
		{
			if($this->isTimingStatisticsEnabled())
				return $this->savedDebugMicrotime = microtime(true);
			return false;
		}

		public function endTimer()
		{
			if($this->isTimingStatisticsEnabled())
			{
				$t = microtime(true) - $this->savedDebugMicrotime;
				return $t;
			}
			return false;
		}

		public function endTimerAndLogDbo($logName, $logDbo)
		{
			$t = $this->endTimer();
			if($t !== false)
				app()->addStat($logName, (is_object($logDbo) ? t("ro_" . $logDbo->__table) . "/" . $logDbo->getCaption() . "\n" : "") . round($t, 6) . " s"); //μ
		}

		private $_enumeratedObjects;

		/**
		 * returns list of enumerated objects (from table enumeratedobject)
		 * @return array<string>
		 */
		public function getEnumeratedObjects()
		{
			if(!isset($this->_enumeratedObjects))
				$this->_enumeratedObjects = $this->queryAsStringsArray("select name from sequencedobject order by name");
			return $this->_enumeratedObjects;
		}

		private $mimetypes;

		public function getMimeType($e)
		{
			if(!isset($this->mimetypes))
			{
				$this->mimetypes = array();
				$a = file_get_contents("/etc/mime.types");
				foreach (explode("\n", $a) as $r)
				{
					$r = trim($r);
					if($r != "" && substr($r, 0, 1) != "#")
					{
						$w = explode("\t", $r);
						if(count($w) > 1)
						{
							$mme = $w[0];
							foreach(explode(" ", $w[count($w) - 1]) as $ext)
								$this->mimetypes[$ext] = $mme;
						}
					}
				}
			}
			return isset($this->mimetypes[$e]) ? $this->mimetypes[$e] : "";
		}

	}	//end of Application

	/** @var Current application instance */
	$_app;

	/**
	 * Application instance
	 * @return WFWApp
	 */
	function app()
	{
		global $_app;
		if(!isset($_app))
			if(class_exists("Application"))
				$_app = new Application();
			else
				$_app = new WFWApp();

		return $_app;
	}

	/**
	 * Current user, shortcut for app()->user()
	 * @return WFWebuser
	 */
	function user()
	{
		return app()->user();
	}

	/**
	 * Quoted string
	 * @return String
	 * @param String $s
	 */
	function quote($s)
	{
		return "'" . escape($s) . "'";
	}

	/**
	 * Escaped string
	 * @param mixed $s
	 * @return String
	 */
	function escape($s)
	{
		return app()->getDBConnection()->escapeSimple($s);
	}

	/**
	 * DBO classes autoload
	 * @param String name
	 * @return void
	 */
	function __autoload($name)
	{
		$a = explode("_", $name);
		if(isset($a[1]))
			if(file_exists($f = APP_ROOT . "/classes/dbo/" . $a[1] . ".php"))
				require_once $f;
	}

	/**
	 * Strip backdir from directory name
	 * @param String $s
	 * @return String
	 */
	function stripDirBack($s)
	{
		return str_replace("../", "", $s);
	}

	/**
	 * Translate message, shortcut for app()->translate()
	 * @param mixed $s 
	 * @return String
	 */
	function t($s)
	{
		return app()->translate($s);
	}

	/**
	 * SELECT $representationField FROM $foreignTable WHERE $foreignTableField = $field
	 * @param String $field
	 * @param String $foreignTable
	 * @param String $foreignTableField
	 * @param String $representationField
	 * @return String|null
	 * @todo security
	 */
	function tableValue($field, $foreignTable, $foreignTableField, $representationField){
		$a = tableRow($field, $foreignTable, $foreignTableField);
		if (is_object($a)){
			return $a->$representationField;
		}
		return null;
	}

	/**
	 * retrieve table row from database as DB_DataObject and return it
	 * @param String $field
	 * @param String $foreignTable
	 * @param String $foreignTableField
	 * @return DB_DataObject|null
	 * @todo security
	 */
	function tableRow($field, $foreignTable, $foreignTableField){
		$a = DB_DataObject::factory($foreignTable);

		if (is_object($a)){
			$a->getDatabaseConnection()->query("SET NAMES utf8");
			$a->get($foreignTableField, $field);
			return $a;
		}
		return null;
	}

	/**
	 * Shortcut for app()->registries()
	 * @return array
	 */
	function getMenu()
	{
		return app()->registries();
	}

	/**
	* prints debug data to output
	* @param String $s
	* @return void
	* @deprecated since 2.0
	*/
	function dbglog($s)
	{
		if(app()->isDebug())
			app()->debug($s);
	}

	/**
	* microtime delta, used for debug
	* @param number $t1
	* @param number $t2
	* @return number
	*/
	function mt2ms($t1, $t2 = 0)
	{
		if($t2 == 0)
			$t2 = microtime(true);
		return round($t2 - $t1, 3);
	}

	//error handling

	register_shutdown_function( "fatal_handler" );

	/**
	* Custom error handler
	* @todo implement it properly
	*/
	function fatal_handler()
	{
	  	$errfile = "unknown file";
	  	$errstr  = "shutdown";
	  	$errno   = E_CORE_ERROR;
	  	$errline = 0;

	  	$error = error_get_last();

	  	if( $error !== NULL)
	  		if ($error['type'] == 1)
		  	{
		    	$errno   = $error["type"];
			    $errfile = $error["file"];
			    $errline = $error["line"];
			    $errstr  = $error["message"];

			    echo "<hr/><h1>error handler called</h1><pre/>";
			    print_r($error);

			    //error_mail(format_error( $errno, $errstr, $errfile, $errline));
			}
	}

	function splitDateTime($dt)
	{
		$s = strtotime($dt);
		return array(
			date(FORMATSTRING_DATE_MACHINE, $s),
			date(FORMATSTRING_TIME_MACHINE, $s),
			);
	}

	function combineDateTime($dt, $tm)
	{
		return $dt . " " . $tm;
	}

	function xmlField($f, $v)
	{
		return "<$f>$v</$f>\n";
	}

	function xmlSimpleField($f, $v)
	{
		return "<$f>" . xmlize($v) . "</$f>\n";
	}

	function xmlize($v)
	{
		return $v;
	}

	function getXmlValidationErrors($xml, $xsd)
	{
        //echo "validate $xml against $xsd<br/>";
        $d = new DOMDocument();
        $d->load($xml);
		libxml_use_internal_errors(true);
		if($d->schemaValidate($xsd) === true)
			return "";
		else
        	return libxml_format_errors();
	}

	function libxml_format_error($error)
	{
    	$r = "";
    	switch ($error->level)
    	{
        	case LIBXML_ERR_WARNING:
            	$r .= "<b>Warning $error->code</b>: ";
            	break;
        	case LIBXML_ERR_ERROR:
            	$r .= "<b>Error $error->code</b>: ";
            	break;
        	case LIBXML_ERR_FATAL:
            	$r .= "<b>Fatal Error $error->code</b>: ";
            	break;
    	}
    	$r .= trim($error->message);
    	if ($error->file)
        	$r .= " in <b>$error->file</b>";
    	$r .= " on line <b>$error->line</b>\n";
    	return $r;
	}

	function libxml_format_errors()
	{
		$ret = "";
    	$errors = libxml_get_errors();
    	foreach ($errors as $error)
	    	$ret .= libxml_format_error($error) . "<br/>";
    	libxml_clear_errors();
	    return $ret;
	}
