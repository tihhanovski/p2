<?php
/**
 * Adds subdocument capabilities to RegistryDescriptor
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 */

	//TODO remove it!

	/**
	 * Adds subdocument capabilities to RegistryDescriptor
	 */
	class SubdocumentRegistryDescriptor extends RegistryDescriptor
	{
		public function getTopToolbar()
		{
			if(isset($this->noToolbar) && $this->noToolbar)
				return "";
			else
				return parent::getTopToolbar();
		}

		public function saveAndReload()
		{
			app()->startTimer();
		 	app()->requireLoginJson();
		 	$context = $this->getContext();
		 	if(app()->request("fieldsData") == "1")
		 	{
		 		foreach ($_REQUEST as $k => $v)
		 		{
		 			list($path, $path2) = app()->explodePath($k);
		 			if($path == $context->namePrefix)
				 		$context->setValueByPath($path2, $v);
		 		}
		 	}
			app()->endTimerAndLogDbo("document saved", $context->obj);
			if($this->saveContext())
				app()->location($this->getEditInnerDocumentUrl(app()->request(REQUEST_STARTUPMSG)));
		}

		protected function getEditInnerDocumentUrl($msg = "")
		{
			return "?" . REQUEST_REGISTRY . "=" . app()->request(REQUEST_REGISTRY) .
						"&" . REQUEST_ACTION . "=editInner" .
						"&id=" . app()->request("id") .
						($msg ? "&" . REQUEST_STARTUPMSG . "=" . urlencode($msg) : "");
		}

		public function editInner()
		{
			app()->requirePrivilege(PRIVILEGE_SELECT);
			if(!is_object($context = app()->getContext($this->getContextName())))
				die("No context loaded");

			$this->noToolbar = true;
			$this->indexMode = false;
			include app()->getAbsoluteFile("ui/pagestart.php");

			if($fn = $this->getDetailFormPath())
			{
				?><body><div class="editorContentInner"><div id="doc_warnings" class="docWarningContainer"></div><?php

				if(isset($context) && isset($context->obj))
					$obj = $context->obj;

				include $fn;
				include app()->getAbsoluteFile("ui/edit.finish.php");
			}
			else
				include app()->getAbsoluteFile("ui/detailNotFound.php");

			include app()->getAbsoluteFile("ui/pagefinish.php");
		}
	}