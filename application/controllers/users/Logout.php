<?php
require_once __DIR__. '/Users_base_controller.php';

/**
 * users/Logout コントローラ
 */
class Logout extends Users_base_controller
{
    public function index()
    {
        $this->_logout();
        codeigniter_redirect('/');
    }
}
