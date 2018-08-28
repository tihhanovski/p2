<?php
/*
 * Created on Aug 28, 2018
 *
 * (c) Rene Korss, Redwall OÜ
 *
 */

class FWSpecialright extends WFWObject
{
    function getCaption()
    {
        return t("spr_" . $this->name);
    }
}