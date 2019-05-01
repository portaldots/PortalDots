<?php

/**
 * home_staff ベースコントローラ
 *
 * スタッフモード
 */
class Home_staff_base_controller extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->_require_login();
        $this->_staff_only();
    }
}
