<?php
/*
 * Created on 29.08.2015
 *
 * (c) Ilja Tihhanovski, Intellisoft
 *
 */

class FWArticle extends WFWObject
{
    protected $validators = array(
        "code" => VALIDATION_NOT_EMPTY,
        "name" => VALIDATION_NOT_EMPTY,
    );


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
                ),
        );
    }
}
