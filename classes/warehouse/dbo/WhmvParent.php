<?php
/**
 * Parent class for whmv
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
*/


class WhmvParent extends WFWObject
{
    public $modifier;
    public $totalPrice;
    public $totalCost;

    protected $formats = array(
        "dt" =>             FORMAT_DATE,
        "quantity" =>       FORMAT_FLOAT6,
        "cost" =>           FORMAT_FLOAT6,
        "price" =>          FORMAT_FLOAT6,
        "discount" =>       FORMAT_FLOAT2,
        "totalCost" =>      FORMAT_FLOAT2,
        "totalPrice" =>     FORMAT_FLOAT2,
        "mdCreated" =>      FORMAT_DATETIME,
        "mdUpdated" =>      FORMAT_DATETIME,
    );

    protected $validators = array(
        "articleId" => array(VALIDATION_NOT_EMPTY, VALIDATION_CLASS_METHOD),
        "quantity" => array(VALIDATION_NOT_EMPTY, VALIDATION_CLASS_METHOD),
        "cost" => VALIDATION_CLASS_METHOD,
        "price" => VALIDATION_CLASS_METHOD,
        "discount" => VALIDATION_CLASS_METHOD,
        "whDstId" => array(VALIDATION_NOT_EMPTY, VALIDATION_CLASS_METHOD),
        "whSrcId" => array(VALIDATION_NOT_EMPTY, VALIDATION_CLASS_METHOD),
    );

    public function validate_whDstId()
    {
        if($this->whDstId == DEFAULT_WAREHOUSE && (
            $this->typeId == WHMVTYPE_INITIAL ||
            $this->typeId == WHMVTYPE_INCOME ||
            $this->typeId == WHMVTYPE_INTRA
            ))
        {
            $this->addWarning(new Warning("whDstId cant be default", "whDstId", WARNING_ERROR));
            return false;
        }
        return true;
    }

    public function validate_whSrcId()
    {
        if($this->whSrcId == DEFAULT_WAREHOUSE && (
            $this->typeId == WHMVTYPE_OUTCOME ||
            $this->typeId == WHMVTYPE_WRITEOFF ||
            $this->typeId == WHMVTYPE_INTRA
            ))
        {
            $this->addWarning(new Warning("whSrcId cant be default", "whSrcId", WARNING_ERROR));
            return false;
        }
        return true;
    }

    private function initFormats()
    {
        app()->warehouse();
        $this->formats["price"] = FORMAT_PRICE_WAREHOUSE;
        $this->formats["quantity"] = FORMAT_QUANTITY_WAREHOUSE;
        $this->formats["cost"] = FORMAT_PRICE_WAREHOUSE;
    }

    /**
     * {@inheritdoc}
     */
    public function loadAdditionalData()
    {
        if($this->articleId)
            $this->validate_articleId();
        $this->calcTotalCost();
        $this->calcTotalPrice();
        if($this->modifierId !== DEFAULT_WHMV_MODIFIER)
            $this->modifier = $this->getLink("modifierId")->name;


        $this->whDstLink = app()->warehouse()->getWarehouseLinkedCaption($this->whDstId);
        $this->whSrcLink = app()->warehouse()->getWarehouseLinkedCaption($this->whSrcId);

        $this->initFormats();
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultValues()
    {
        $this->initFormats();
    }

    /**
     * Sets mandatory fields data for type given
     * @param int $typeId
     */
    public function setMandatoryForType($typeId = -1)
    {
        if($typeId > 0)
            $this->typeId = $typeId;
        switch ($this->typeId)
        {
            case WHMVTYPE_INITIAL:
                $this->whSrcId = DEFAULT_WAREHOUSE;
                $this->companyDstId = DEFAULT_COMPANY;
                $this->companySrcId = DEFAULT_COMPANY;
                break;
            case WHMVTYPE_INCOME:
                $this->whSrcId = DEFAULT_WAREHOUSE;
                $this->companyDstId = DEFAULT_COMPANY;
                break;
            case WHMVTYPE_OUTCOME:
                $this->whDstId = DEFAULT_WAREHOUSE;
                $this->companySrcId = DEFAULT_COMPANY;
                break;
            case WHMVTYPE_INTRA:
                $this->companyDstId = DEFAULT_COMPANY;
                $this->companySrcId = DEFAULT_COMPANY;
                break;
            case WHMVTYPE_WRITEOFF:
                $this->companyDstId = DEFAULT_COMPANY;
                $this->companySrcId = DEFAULT_COMPANY;
                $this->whDstId = DEFAULT_WAREHOUSE;
                break;
            case WHMVTYPE_PRODUCTION:
                $this->companyDstId = DEFAULT_COMPANY;
                $this->companySrcId = DEFAULT_COMPANY;
                break;
            case WHMVTYPE_INVENTORY:
                $this->companyDstId = DEFAULT_COMPANY;
                $this->companySrcId = DEFAULT_COMPANY;
                break;
        }
    }

    /**
     * Loading data linked with article
     * @return bool true if valid
     */
    public function validate_articleId()
    {
        if(is_object($a = app()->get("article", $this->articleId)))
        {
            $this->setValue("articleLink", app()->getLinkedCaption($a));
            if(is_object($u = $a->getLink("unitId")))
            {
                $this->setValue("unitName", $u->name);
                return true;
            }
        }
        if($this->unitCode != "")
            $this->setValue("unitName", "");
        return true;
    }

    /**
     * Calculates movements total cost
     */
    public function calcTotalCost()
    {
        $this->setValue("totalCost", round($this->cost * $this->quantity, WHMV_TOTALCOST_ROUNDING));
    }

    /**
     * Calculates movements total price
     */
    public function calcTotalPrice()
    {
        $this->setValue("totalPrice", round($this->price * $this->quantity * (100 - $this->discount) / 100, WHMV_TOTALPRICE_ROUNDING));
    }

    /**
     * Recalculate total cost after changing cost
     */
    public function validate_cost()
    {
        $this->calcTotalCost();
        return true;
    }

    /**
     * Recalculate total price after changing price
     */
    public function validate_price()
    {
        $this->calcTotalPrice();
        return true;
    }

    /**
     * Recalculate total cost and price after changing quantity
     */
    public function validate_quantity()
    {
        $this->calcTotalCost();
        $this->calcTotalPrice();
        return true;
    }

    /**
     * Recalculate total cost and price when discount is changed
     */
    public function validate_discount()
    {
        $this->calcTotalCost();
        $this->calcTotalPrice();
        return true;
    }

    /**
     * Fills modifierId using modifier.
     * Adds new modifier to database if necessary.
     */
    private function findModifier()
    {
        if("" === $mod = trim($this->modifier))
            $this->modifierId = DEFAULT_WHMV_MODIFIER;
        else
        {
            $m = app()->dbo("whmvmodifier");
            $m->name = $mod;
            $m->find(true);
            if(!$m->id)
            {
                $m->name = $mod;
                $m->insert();
            }
            $this->modifierId = $m->id;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function update()
    {
        $this->findModifier();
        $ret = parent::update();
        app()->warehouse()->resetQPForWhmv($this);
        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function insert()
    {
        $this->findModifier();
        $ret = parent::insert();
        app()->warehouse()->resetQPForWhmv($this);
        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $this->findModifier();
        $ret = parent::delete();
        app()->warehouse()->resetQPForWhmv($this);
        return $ret;
    }

}