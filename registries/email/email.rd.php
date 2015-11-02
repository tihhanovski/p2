<?php
/*
 * Created on Sep 29, 2011
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */


	class _RegistryDescriptor extends RegistryDescriptor
	{
		public $gridSql = "select e.id, e.recipient, e.subject, e.sent, e.mdCreated, e.mdUpdated, e.sender
				from email e
				";

		function getGrid()
		{
			$ret = new RegFlexiGrid();

			$ret->sortname = "e.mdUpdated";
			$ret->sortorder = MGRID_ORDER_DESC;

			$ret->addColumn(new SimpleFlexiGridColumn("recipient", "recipient", "200"));
			$ret->addColumn(new SimpleFlexiGridColumn("subject", "subject", "400"));
			$ret->addColumn(new SimpleFlexiGridColumn("sent", "sent", "150", MGRID_ALIGN_LEFT, FORMAT_DATETIME));
			$ret->addColumn(new SimpleFlexiGridColumn("mdCreated", "mdCreated", "150", MGRID_ALIGN_LEFT, FORMAT_DATETIME));
			$ret->addColumn(new SimpleFlexiGridColumn("mdUpdated", "mdUpdated", "150", MGRID_ALIGN_LEFT, FORMAT_DATETIME));
			$ret->addColumn(new SimpleFlexiGridColumn("sender", "sender", "200"));

			return $ret;
		}

		function getChildrenTree()
		{
			//return array("webuser" => array("userroles" => "userrole"));
		}

		function send()
		{
			if(is_object($context = app()->getContext($this->getContextName())))
			{
				$eml = $context->obj;

				$eml->send();

				app()->requireReload();
				app()->jsonMessage();
			}
		}
	}
