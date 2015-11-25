<?php
/**
 * Parent class for whmvbatch
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
*/


class WhinventoryrowParent extends WFWObject
{
    public $delta;

    protected $formats = array(
        "mdCreated" =>      FORMAT_DATETIME,
        "mdUpdated" =>      FORMAT_DATETIME,
    );

    protected $validators = array(
        "realQuantity" => VALIDATION_CLASS_METHOD,
    );

    private function initFormats()
    {
        app()->warehouse();
        $this->formats["quantity"] = FORMAT_QUANTITY_WAREHOUSE;
        $this->formats["realQuantity"] = FORMAT_QUANTITY_WAREHOUSE;
        $this->formats["delta"] = FORMAT_QUANTITY_WAREHOUSE;
        $this->formats["cost"] = FORMAT_PRICE_WAREHOUSE;
    }

    public function validate_realQuantity()
    {
        $this->setValue("delta", $this->realQuantity - $this->quantity);
        /*if($this->delta > 0)
            $this->calcCost();
        else
            $this->setValue("cost", "");    //TODO is it good approach?
        */
        return true;
    }

    /**
     * Used to calculate cost if real quantity is bigger than quantity
     */
    protected function calcCost()
    {

    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultValues()
    {
        $this->initFormats();
    }

    /**
     * {@inheritdoc}
     */
    public function loadAdditionalData()
    {
        if($this->articleId)
            $this->articleCaption = app()->getLinkedCaption($this->getLink("articleId"));
        $this->delta = $this->realQuantity - $this->quantity;
        $this->initFormats();
    }

    public function getWhmv($iv)
    {
        $delta = $this->realQuantity - $this->quantity;
        $m = app()->dbo("whmv");
        $m->typeId = WHMVTYPE_INVENTORY;
        $m->articleId = $this->articleId;
        $m->modifierId = $this->modifierId;
        $m->whSrcId = $delta > 0 ? DEFAULT_WAREHOUSE : $iv->whId;
        $m->whDstId = $delta > 0 ? $iv->whId : DEFAULT_WAREHOUSE;
        $m->companySrcId = DEFAULT_COMPANY;
        $m->companyDstId = DEFAULT_COMPANY;
        $m->quantity = abs($delta);
        $m->cost = $delta > 0 ? $this->cost : 0;
        return $m;
    }
}
