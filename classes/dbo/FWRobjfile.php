<?php
/**
 * Parent class for robjfile
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
*/

class FWRobjfile extends WFWObject
{
	protected $captionFields = array("name");

    protected $formats = array(
    	"mdCreated" => FORMAT_DATETIME,
    	"mdUpdated" => FORMAT_DATETIME,
    );

    /** {@inheritdoc} */
    public function loadAdditionalData()
    {
    	$this->linkedCaption = app()->getLinkedCaption($this);
        $this->downloadLink = "<a href=\"" . $this->url("downloadFile") . "\">" . t("Download") . "</a>";
        $this->showLink = $this->getContentType() ? "<a href=\"" . $this->url("showFile") . "\" target=\"_blank\">" . t("Show") . "</a>" : "";
    }

    public function getActualPath()
    {
        if($this->isInDatabase())
            return INSTANCE_ROOT . USERFILES . $this->__table . "/" . $this->getIdValue();
        else
            return "";
    }

    public function getFileSize()
    {
        if("" !== $p = $this->getActualPath())
            if(file_exists($p))
                return filesize($p);
        return 0;
    }

    public function getLinkedDocument()
    {
        return app()->get($this->robj, $this->rid);
    }

    public function canAccess()
    {
        return app()->canSelect($this->robj);
    }

    public function getContentType()
    {
        return app()->getMimeType(pathinfo($this->name, PATHINFO_EXTENSION));
    }

    public function url($action)
    {
        return app()->url("?registry=robjfile&id=" . $this->getIdValue() . "&action=" . $action);
    }

}