<?php
require_once __DIR__. '/Users_base_controller.php';

/**
 * users/Login コントローラ
 */
class Login extends Users_base_controller
{
    public function index()
    {
        codeigniter_redirect('/login');
    }
}
