<?php
/**
 * Parent class for whmvbatch
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
*/


class WhmvbatchParent extends WFWObject
{
    protected $formats = array(
        "dt" =>             FORMAT_DATE,
        "totalCost" =>      FORMAT_FLOAT2,
        "totalPrice" =>     FORMAT_FLOAT2,
        "mdCreated" =>      FORMAT_DATETIME,
        "mdUpdated" =>      FORMAT_DATETIME,
    );

    protected $validators = array(
        "whSrcId" => VALIDATION_CLASS_METHOD,
        "whDstId" => VALIDATION_CLASS_METHOD,
    );

    public function validate_whDstId()
    {
        $this->updChildrenWhField("whDstId");
        if($this->typeId == WHMVTYPE_INITIAL && $this->whDstId == DEFAULT_WAREHOUSE)
        {
            $this->addWarning(new Warning("whDstId cant be default", "whDstId", WARNING_ERROR));
            return false;
        }
        return true;
    }

    public function validate_whSrcId()
    {
        $this->updChildrenWhField("whSrcId");
        return true;
    }

    private function updChildrenWhField($f)
    {
        if($this->$f != DEFAULT_WAREHOUSE)
            if(isset($this->rows) && is_array($this->rows))
                foreach ($this->rows as $r)
                    $r->setValue($f, $this->$f);
    }

    public function getDefaultFor_typeId()
    {
        return app()->warehouse()->getWhmvType();
    }

    /**
     * Sets default value for dt to current date
     */
    public function getDefaultFor_dt()
    {
        return app()->now();
    }

    /**
     * Sets default value for source warehouse
     */
    public function getDefaultFor_whSrcId()
    {
        return app()->warehouse()->getDefaultSrcWarehouse();
    }

    /**
     * Sets default value for destination warehouse
     */
    public function getDefaultFor_whDstId()
    {
        return app()->warehouse()->getDefaultDstWarehouse();
    }

    /**
     * Sets default value for source company
     */
    public function getDefaultFor_companySrcId()
    {
        return DEFAULT_COMPANY;
    }

    /**
     * Sets default value for destination warehouse.
     */
    public function getDefaultFor_companyDstId()
    {
        return DEFAULT_COMPANY;
    }

    /**
     * Calculate totalCost and totalPrice for document.
     */
    private function calcTotals()
    {
        if(isset($this->rows) && is_array($this->rows))
        {
            $tc = 0;
            $tp = 0;
            foreach ($this->rows as $row)
            {
                $tc += $row->totalCost;
                $tp += $row->totalPrice;
            }
            $this->setValue("totalCost", $tc);
            $this->setValue("totalPrice", $tp);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function childValueChanged($path, $value, $tree)
    {
        $this->calcTotals();
    }

    /**
     * {@inheritdoc}
     */
    public function childDeleted($path, $tree)
    {
        $this->calcTotals();
    }

    /**
     * {@inheritdoc}
     */
    public function loadAdditionalData()
    {
        $this->calcTotals();
    }


    /**
     * {@inheritdoc}
     */
    public function insert()
    {
        if($this->typeId != WHMVTYPE_INVENTORY) //inventory whmv batch has same number as source document
            $this->fillNr();
        $ret = parent::insert();
        return $ret;
    }

    /**
     * {@inheritdoc}
     */
    public function delete()
    {
        $r = app()->dbo("whmv");
        $r->batchId = $this->id;
        if($r->find())
            while($r->fetch())
                $r->delete();
        return parent::delete();
    }

    /**
     * {@inheritdoc}
     */
    public function initializeChild($obj)
    {
        if($obj->__table == "whmv")
        {
            $obj->setMandatoryForType($this->typeId);
            foreach (array("whSrcId", "whDstId") as $f)
                if($this->$f !== DEFAULT_WAREHOUSE)
                    $obj->$f = $this->$f;
        }
    }

    /**
     * Calculates number for newly saved batch.
     * Sets nrsequenceId, prefix, suffix, nr.
     */
    public function fillNr()
    {
        $sql = "select id as nrsequenceId, prefix as nrprefix, suffix as nrsuffix,
                    if(nextNr > startNr, nextNr, startNr) as nr from
                    (
                        select n.id, n.prefix, n.suffix, n.startNr, coalesce(max(b.nr), 0) + 1 as nextNr
                        from
                        (
                            select id, prefix, suffix, startNr from nrsequence where id in
                            (
                                select substr(p.robject, 11) from objectproperty p
                                inner join whmvtype t on p.name = concat('Enum', t.name)
                                and t.id = " . ((int)$this->typeId) . " and p.value = 1
                            )
                            order by id limit 0, 1
                        ) n
                        left join whmvbatch b on b.nrsequenceId = n.id
                        group by n.id, n.prefix, n.suffix, n.startNr
                    ) x";

        $o = app()->queryAsArray($sql);
        foreach ($o as $row)
            foreach ($row as $f => $v)
                $this->setValue($f, $v);
        $this->calcFullNr();
    }

    /**
     * Calculates fullNr from number parts
     */
    private function calcFullNr()
    {
        $this->setValue("fullNr", $this->nrprefix . $this->nr . $this->nrsuffix);
    }

    /**
     * {@inheritdoc}
     */
    public function saveChildrenByClass($var, $cls, $tree)
    {
        $fc = array("typeId", "dt", "companySrcId", "companyDstId");
        if($this->typeId == WHMVTYPE_INTRA)
        {
            $fc[] = "whSrcId";
            $fc[] = "whDstId";
        }
        if($cls === "whmv")
            foreach ($this->$var as $row)
            {
                foreach ($fc as $ff)
                    $row->setValue($ff, $this->getValue($ff));

                $row->setMandatoryForType();
            }
        return parent::saveChildrenByClass($var, $cls, $tree);
    }

    /**
     * {@inheritdoc}
     */
    function canLock()
    {
        return !$this->locked;
    }

    /**
     * {@inheritdoc}
     */
    function canUnlock()
    {
        return $this->locked;
    }

    /**
     * {@inheritdoc}
     */
    function lock()
    {
        $this->locked = 1;
        return $this->update();
    }

    /**
     * {@inheritdoc}
     */
    function unlock()
    {
        $this->locked = 0;
        return $this->update();
    }
}
