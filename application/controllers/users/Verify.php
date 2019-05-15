<?php
require_once __DIR__. '/Users_base_controller.php';

/**
 * users/Verify コントローラ
 *
 * メールアドレス認証コード検証ページ
 */
class Verify extends Users_base_controller
{
  /**
   * メールアドレス認証状況確認ページ
   */
    public function status()
    {
        if (isset($_SESSION['user_checked'])) {
            $vars = [];
            $vars["xs_navbar_title"] = "ユーザー登録状況";
            $vars["xs_navbar_back"] = true; // 戻るボタンを表示
            $vars['page_type'] = 'verify_status';
            $vars['user_checked'] = $_SESSION['user_checked'];
            $this->_render('users/verify_info', $vars);
        } else {
            codeigniter_redirect("/");
        }
    }

  /**
   * univemail 用認証コードの検証
   *
   * @param  string $code メールアドレス認証コード
   */
    public function univemail($code)
    {
        $this->_content('univemail', $code);
    }

  /**
   * email 用認証コードの検証
   *
   * @param  string $code メールアドレス認証コード
   */
    public function email($code)
    {
        $this->_content('email', $code);
    }

  /**
   * @param  string $type univemailかemailか
   * @param  string $code メールアドレス認証コード
   */
    private function _content($type = '', $code = '')
    {
        $result = $this->users->validate_verifycode($type, $code);
        if (!empty($type) && !empty($code) && $result !== false) {
          // 確認メールのコード検証が成功した場合
            $vars = [];
            $vars["xs_navbar_title"] = "ユーザー登録";
            $vars['page_type'] = 'verify_success';
            $vars['user_checked'] = $result;
            if (empty($result->verifycode_univemail) && empty($result->verifycode_email)) {
                // 本登録実行
                $this->users->move_user_from_temp_user($result->id);
                // 両方の認証が完了したので、ログインを実行
                $userinfo = $this->users->get_user_by_login_id($result->email);
                $this->_login($userinfo->id);
                // 登録完了メールも送信
                $vars_email = [];
                $vars_email['name_to'] = $result->name_family. ' '. $result->name_given;
                $this->_send_email($result->email, "ご登録ありがとうございました", 'email/signup_success', $vars_email);
            }
            $this->_render('users/verify_info', $vars);
        } else {
            $this->_error('エラー', "このURLは無効です。\nこのURLが発行されてから24時間以上経過したか、使用済みです。");
        }
    }
}
