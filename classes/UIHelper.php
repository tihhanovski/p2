<?php

	class UIHelper
	{
		public $contextDataWritten = false;
		public $closeDocumentToolbarWritten = false;
		public $modificationDataWritten = false;

		public function includeStyles()
		{
			$styles = array(
					SETUP_CSS_MAIN,
					SETUP_3RD_COMBOGRID_CSS2,
					SETUP_3RD_COMBOGRID_CSS,	//combogrid
					app()->url("resources/ui.css"),
					SETUP_3RD_MULTISELECT_CSS
				);
			foreach ( $styles as $src)
				echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$src\" />";
		}

		public function includeScripts()
		{
			$scripts = array(
					SETUP_JQUERY,
					SETUP_JQUERY_I18N,
					SETUP_JQUERY_UI,
					SETUP_HOTKEYS,
					SETUP_COMBOGRID,
					SETUP_3RD_MULTISELECT_JS,
					//SETUP_TINY_MCE . "jquery.tinymce.js",
					WFW_WEB . "js/app.js",
					WFW_WEB . "js/utils.js",
					WFW_WEB . "js/autocomplete.js",
					WFW_WEB . "js/bubble.js",
					WFW_WEB . "js/tipSystem.js",
					WFW_WEB . "js/grid.js",
					WFW_WEB . "js/simpleForm.js",
					WFW_WEB . "js/detailGrid.js",
					WFW_WEB . "js/filesControl.js",
					WFW_WEB . "js/messagesControl.js",
					WFW_WEB . "js/commentsControl.js",
				);

			foreach ( $scripts as $src)
				echo "<script type=\"text/javascript\" src=\"$src?v=" . JS_VERSION . "\"></script>";
		}

		public function includePageSetup()
		{
			$u = app()->user();
			if(!$u->dynamicPropertiesLoaded)
				$u->loadDynamicProperties();

			$ud = $u->get_data_for_json();
			unset($ud["pwd"]);
			unset($ud["sessionid"]);

			$vars = array(
				"setup" => array(
					"WFW_WEB" => WFW_WEB,
					"APP_WEB" => APP_WEB,
					"INSTANCE_WEB" => INSTANCE_WEB,
					"SPECIALVALUE_DEFAULT" => SPECIALVALUE_DEFAULT,
					"SPECIALVALUE_MSELECT" => SPECIALVALUE_MSELECT,
					"LOCALE" => app()->getLocale(),
					"DocOpenInTab" => (isset($u->dynDocOpenInTab) && $u->dynDocOpenInTab == 1),
					"datepickerFormat" => FORMATSTRING_DATEPICKER,
					"user" => $ud,
				),
				"req" => array(
					REQUEST_ACTION => app()->request(REQUEST_ACTION),
					REQUEST_REGISTRY => app()->request(REQUEST_REGISTRY),
					REQUEST_ID => app()->request(REQUEST_ID)
				),

				"locales" => app()->localesList,

			);

			if($r = app()->request("startupMsg"))
				if($r != ($t = t($r)))
					$vars["req"]["startupMsg"] = $t;

			$msg = array();
			foreach ( app()->clientTranslations as $m )
			{
				$msg[$m] = t($m);
		    }
		    $vars["msg"] = $msg;

		    ?><script type="text/javascript"><?php

			foreach ( $vars as $vn => $vv )
				echo "var $vn = " . json_encode($vv) . "; ";

			?> $(function(){ <?=tipSystemCommand()?> app.i18n = <?=app()->isI18n() ? "true" : "false"?>; app.finish();}) </script><?php
		}
	}