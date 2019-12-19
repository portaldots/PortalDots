<?php

/**
 * ルートコントローラ
 */
class Index_controller extends MY_Controller
{
  /**
   * ルートページ
   *
   * ログイン画面にリダイレクトするだけ
   */
    public function index()
    {
        if (empty($this->_get_login_user())) {
            codeigniter_redirect("/login");
        } else {
            codeigniter_redirect("/home");
        }
    }
}
