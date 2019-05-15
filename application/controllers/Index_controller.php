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
        codeigniter_redirect("users/login");
    }
}
