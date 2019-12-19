<?php
/*
 * Created on 29.08.2015
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

class FWCompany extends WFWCodedAndNamed
{
    protected $validators = array(
        "code" => VALIDATION_NOT_EMPTY,
        "name" => VALIDATION_NOT_EMPTY,
    );

    protected $addrRepresentations = array("addr");

    protected $captionFields = array("code", "name");

    public function keySelColumns()
    {
        return array(
                array(
                    "columnName" => "code",
                    "label" => t("Code"),
                    "width" => "30",
                    "align" => "left"
                ),
                array(
                    "columnName" => "name",
                    "label" => t("Name"),
                    "width" => "70",
                    "align" => "left"
                )
        );
    }

    public function advancedComboColumns()
    {
        return array(
            array(
                "columnName" => "code",
                "label" => t("Code"),
                "width" => "30",
                "align" => "left"
            ),
            array(
                "columnName" => "name",
                "label" => t("Name"),
                "width" => "30",
                "align" => "left"
            ),
            array(
                "columnName" => "addr",
                "label" => t("Address"),
                "width" => "40",
                "align" => "left"
            )
        );
    }

    protected $formats = array(
        "mdCreated" =>      FORMAT_DATETIME,
        "mdUpdated" =>      FORMAT_DATETIME,
    );

    protected $closedField = "closed";  //closable

    /**
     * used in RequestHandler->comboData to filter output
     */
    public function keySelAdditionalSql($m)
    {
        if($m == customer)
            return " where o.customer = 1";
        if($m == supplier)
            return " where o.supplier = 1";
        return "";
    }
}
