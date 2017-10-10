<?php

namespace commons;
use commons\framework\UIBaseAction;
/**
 * User: sweetbai
 * Date: 2017/4/22
 * Time: 14:39
 */
class UIAdminPageAction extends UIBaseAction
{
    public function __construct($settings = ACTION_PAGE_ADMIN_USE, $newDB = null)
    {
        parent::__construct($settings, $newDB);
    }

}