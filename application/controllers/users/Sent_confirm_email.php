<?php
require_once __DIR__. '/Users_base_controller.php';

/**
 * users/Sent_confirm_email コントローラ
 *
 * 仮登録完了ページ
 */
class Sent_confirm_email extends Users_base_controller
{
    public function index()
    {
      // これが配列ではない場合エラーとする
        if (!isset($_SESSION['email_sent_to']) || !is_array($_SESSION['email_sent_to'])) {
            $this->_error('エラー', '不正な登録です。');
        } else {
            $vars = [];
            $vars["xs_navbar_title"] = "仮登録完了";
            $vars['email_sent_to'] = $_SESSION['email_sent_to'];
            $this->_render('users/sent_confirm_email', $vars);
        }
    }
}
