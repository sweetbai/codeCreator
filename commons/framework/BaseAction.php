<?php

namespace commons\framework;
/**
 * User: sweetbai
 * Date: 2017/4/25
 * Time: 17:31
 */
class BaseAction extends Action
{
    protected $UI_ERROR_PAGE = "site/error.html";
    public function __construct($settings=ACTION_ALL_OFF,$newDB=null)
    {
        //$this->checkUILogin();
        self::$ITEM_ROWS = 15;
        parent::__construct($settings,$newDB);
    }
    public function __destruct()
    {
        parent::__destruct();
    }
}