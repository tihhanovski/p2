<?php
/**
 * Software issue registry descriptor
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 19.09.2011 Intellisoft OÜ
 */


/**
 * Software issue registry descriptor
 */
class SoftwareissueRegistryDescriptor extends RegistryDescriptor
{
	public $gridSql = "select i.id, if(i.closed = 1, 'gclosed', '') as style, i.closed,
		i.id, i.caption, i.priority, i.deadline, i.state,
		o.uid as owner,
		c.uid as creator, i.mdCreated, u.uid as updater, i.mdUpdated

		from softwareissue i
		left join webuser c on c.id = i.mdCreatorID
		left join webuser u on u.id = i.mdUpdaterID
		left join webuser o on o.id = i.ownerId";

	public $secondSortName = "id";
	public $secondSortOrder = "";

	/**
	 * {@inheritdoc}
	 */
	function getGrid()
	{
		$ret = new RegFlexiGrid();

		$ret->sortname = "mdCreated";
		$ret->sortorder = "desc";

		$ret->addColumn(new StyleColumn());
		$ret->addClosedIconColumn();
		$ret->addColumn(new SimpleFlexiGridColumn("id", null, "60"));
		$ret->addColumn(new SimpleFlexiGridColumn("caption", null, "600"));
		$ret->addColumn(new SimpleFlexiGridColumn("priority", null, "40", MGRID_ALIGN_RIGHT, FORMAT_INT));
		$ret->addColumn(new SimpleFlexiGridColumn("deadline", null, "70", MGRID_ALIGN_LEFT, FORMAT_DATE));
		$ret->addColumn(new SimpleFlexiGridColumn("state", null, "80"));
		$ret->addColumn(new SimpleFlexiGridColumn("Owner", "owner", "60"));
		$ret->addColumn(new SimpleFlexiGridColumn("Creator", "creator", "60"));
		$ret->addColumn(new SimpleFlexiGridColumn("mdCreated", null, "130", MGRID_ALIGN_LEFT, FORMAT_DATETIME));
		$ret->addColumn(new SimpleFlexiGridColumn("Updater", "updater", "60"));
		$ret->addColumn(new SimpleFlexiGridColumn("mdUpdated", null, "130", MGRID_ALIGN_LEFT, FORMAT_DATETIME));

		return $ret;
	}

	/**
	 * {@inheritdoc}
	 */
	function appendFilter($sql, $filter)
	{
		if(isset($filter->state) && $filter->state != "")
			$sql = $this->addWhere($sql, "i.state like " . quote("%" . $filter->state . "%"));
		if(isset($filter->activeOnly))
			$sql = $this->addWhere($sql, "i.closed = 0");
		if(isset($filter->ownerId) && $filter->ownerId)
			$sql = $this->addWhere($sql, "i.ownerId = " . $filter->ownerId);
		return $sql;
	}

	/**
	 * {@inheritdoc}
	 */
	function getFilterFields()
	{
		$fo = $this->getFilter();
		return array(
			new TextBox($fo, "state"),
			new SelectSql($fo, "ownerId", "Owner", SQL_COMBO_WEBUSER),
		 	new CheckBox($fo, "activeOnly", "Active objects only"),
		 	filterActiveCheckbox($fo)
		);
	}

	/**
	 * Send e-mail
	 */
	function sendEmail()
	{
		app()->requirePrivilegeJson(PRIVILEGE_UPDATE);
		if(is_object($context = app()->getContext($this->getContextName())))
			if(is_object($obj = $context->obj))
				if($obj->getIdValue())
				{
					$obj->sendEmail();
					app()->putContext($context);
					echo app()->jsonMessage();
					return;
				}
		echo app()->jsonMessage(MSG_ERROR, "something went wrong");
	}
}


