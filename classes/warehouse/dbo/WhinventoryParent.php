<?php
/**
 * Parent class for whmvbatch
 * @author Ilja Tihhanovski <ilja.tihhanovski@gmail.com>
 * @copyright (c) 2015 Intellisoft OÜ
 *
*/


class WhinventoryParent extends WFWObject
{
    protected $formats = array(
        "dt" =>             FORMAT_DATE,
        "mdCreated" =>      FORMAT_DATETIME,
        "mdUpdated" =>      FORMAT_DATETIME,
    );

    protected $validators = array(
        "whId" => array(VALIDATION_NOT_EMPTY, VALIDATION_CLASS_METHOD),
    );

    public function validate_whId()
    {
        if($this->whId == DEFAULT_WAREHOUSE)
        {
            $this->addWarning(new Warning("whId cant be default", "whId", WARNING_ERROR));
            return false;
        }
        return true;
    }

    /**
     * Creates rows on first inventory save
     */
    public function fillRows()
    {
        $showMods = app()->warehouse()->isArticleModifiersEnabled();
        //$innerFilter = " and (whSrcId = {$this->whId} or whDstId = {$this->whId})";
        $modPart = $showMods ? "modifierId," : "";
        $sd = quote($this->dt);
        $outerFilter = $this->articlegroupId ? "where a.groupId = {$this->articlegroupId}" : "";
        $userId = app()->user()->id;

        $qmodSql = $this->whId == DEFAULT_WAREHOUSE ?
            "if(whSrcId = 1, 1, -1)" :
            "if(whDstId = {$this->whId}, 1, -1)";
        $innerFilter =
            ($this->whId != DEFAULT_WAREHOUSE ? " and (whSrcId = {$this->whId} or whDstId = {$this->whId})" : "");


        $sql = "insert into whinventoryrow(whinventoryId, articleId, $modPart quantity,
            mdCreated, mdUpdated, mdCreatorId, mdUpdaterId)
            select {$this->id}, a.id, $modPart m.qty, now(), now(), $userId, $userId
            from article a
            left join (
                select articleId, $modPart sum(qty * qmod) as qty
                from (
                    select articleId, $modPart quantity as qty,
                    $qmodSql as qmod
                    from whmv
                    where dt <= $sd $innerFilter
                ) m group by articleId $modPart
            ) m on m.articleId = a.id
            $outerFilter
            order by a.code";

        app()->query($sql);
    }

    /**
     * Sets default value for dt to current date
     */
    public function getDefaultFor_dt()
    {
        return app()->now();
    }

    /**
     * Sets default value for warehouse
     */
    public function getDefaultFor_whId()
    {
        return app()->warehouse()->getDefaultSrcWarehouse();
    }

    /**
     * {@inheritdoc}
     */
    public function insert()
    {
        $this->fillNr();
        $ret = parent::insert();
        $this->fillRows();
        return $ret;
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
                        select id, prefix, suffix, startNr, if(nextNr > nextNr1, nextNr, nextNr1) as nextNr from
                        (
                            select n.id, n.prefix, n.suffix, n.startNr, coalesce(max(b.nr), 0) + 1 as nextNr, coalesce(max(b1.nr), 0) + 1 as nextNr1
                            from
                            (
                                select id, prefix, suffix, startNr from nrsequence where id in
                                (
                                    select substr(p.robject, 11) from objectproperty p
                                    where p.name = 'Enumwhinventory' and p.value = 1
                                )
                                order by id limit 0, 1
                            ) n
                            left join whinventory b on b.nrsequenceId = n.id
                            left join whmvbatch b1 on b1.nrsequenceId = n.id
                            group by n.id, n.prefix, n.suffix, n.startNr
                        ) y
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
        //TODO make movements
        $batch = app()->dbo("whmvbatch");
        $batch->whinventoryId = $this->id;
        $batch->typeId = WHMVTYPE_INVENTORY;
        $batch->dt = $this->dt;
        $batch->nrprefix = $this->nrprefix;
        $batch->nr = $this->nr;
        $batch->nrsuffix = $this->nrsuffix;
        $batch->nrsequenceId = $this->nrsequenceId;
        $batch->fullNr = $this->fullNr;
        $batch->whSrcId = DEFAULT_WAREHOUSE;
        $batch->whDstId = DEFAULT_WAREHOUSE;
        $batch->companySrcId = DEFAULT_COMPANY;
        $batch->companyDstId = DEFAULT_COMPANY;
        $batch->totalCost = 0;
        $batch->totalPrice = 0;
        $batch->insert();
        if(!$batch->id)
            return false;
        $r = app()->dbo("whinventoryrow");
        $r->whinventoryId = $this->id;
        if($r->find())
            while($r->fetch())
                if($r->quantity != $r->realQuantity)
                {
                    $m = $r->getWhmv($this);
                    $m->batchId = $batch->id;
                    $m->dt = $batch->dt;
                    $m->insert();
                    $batch->totalCost += $m->cost * $m->quantity;
                    $m->free();
                }
        if($batch->totalCost != 0)
            $batch->update;

        $this->locked = 1;
        return $this->update();
    }

    /**
     * {@inheritdoc}
     */
    function unlock()
    {
        //TODO remove movements
        $batch = app()->dbo("whmvbatch");
        $batch->whinventoryId = $this->id;
        if($batch->find())
            while($batch->fetch())
                $batch->delete();
        $this->locked = 0;
        return $this->update();
    }

    function isLocked()
    {
        return $this->locked == 1;
    }

    function isLockable()
    {
        return true;
    }

    public function fillQuantitiesNotFilledYet()
    {
        if($this->isInDatabase() && !$this->isLocked())
        {
            if(isset($this->rows) && is_array($this->rows))
                foreach ($this->rows as $row)
                    $row->fillQuantitiyNotFilledYet();
        }
        else
            return false;
    }
}
