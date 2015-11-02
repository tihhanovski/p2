<?php
/**
 * Application
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2011 Intellisoft OÜ
 *
 */

	class SetupFormDescriptor extends RegistryDescriptor
	{
		function getType()
		{
			return RD_TYPE_SETUP;
		}

		function getContext()
		{
			$context = app()->getContext($this->registry);
			if(!is_object($context))
			{
				$context = $this->createContext();
				app()->putContext($context);
			}
			return $context;
		}

		public function outputIndexForm()
		{
			$context = $this->getContext();
			include app()->getAbsoluteFile("ui/setup.index.php");
		}

		function getObj()
		{
			return null;
		}

		function createContext()
		{
			$c = new Context($this->getContextName(), $this->getChildrenTree(), "");
			$c->obj = $this->getObj();
			$c->obj->fullpath = app()->request(REQUEST_REGISTRY);// "sysprofile";
			$c->obj->loadChildren(null);
			return $c;
		}

		function getTopToolbar()
		{
		 	$buttons = array();

		 	if(app()->canUpdate())
		 	{
		 		$buttons["Save"] = "saveDocument()";
		 		//$buttons["Undo"] = "reopenDocument()";
		 	}

			return toolbar($buttons);
		}
	}
