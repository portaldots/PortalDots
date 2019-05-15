<?php
require_once __DIR__. '/Users_base_controller.php';

class Reset_password extends Users_base_controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library("form_validation");
    }

    public function index()
    {
        $vars = [];
        $vars["xs_navbar_title"] = "パスワードリセット";
        $vars["xs_navbar_back"] = true; // 戻るボタンを表示

      # 学籍番号・連絡先メールアドレス入力フォーム

        if (! empty($this->_get_login_user())) {
            codeigniter_redirect("/");
        }

      // メール送信に失敗した場合true
        $failed_email = false;

        if ($this->input->method() === 'post') {
            $this->_post_index();
        }

        $vars['success_msg'] = null;
        if (isset($_SESSION['success_msg'])) {
            $vars['success_msg'] = $_SESSION['success_msg'];
        }

        $vars['failed_email'] = false;
        if (isset($_SESSION['failed_email'])) {
            $vars['failed_email'] = $_SESSION['failed_email'];
        }

        $this->_render('users/reset_password', $vars);
    }

    private function _post_index()
    {
        $this->form_validation->set_rules("login_id", "学籍番号・連絡先メールアドレス", self::LOGIN_ID_RULE);

        if ($this->form_validation->run()) {
          // バリデーション成功
            $login_id = $this->input->post('login_id');
            $userinfo = $this->users->get_user_by_login_id($login_id);
            if ($userinfo !== false) {
                // 該当ユーザーを発見
                $verifycode = $this->users->begin_password_reset($login_id);
                $email = $userinfo->email;
                $datetime_expired = (new DateTime())->modify('+24 hours')->format('Y/m/d H:i');
                $failed_email = !($this->_send_passwordreset_email($email, $verifycode, $datetime_expired, $login_id));
            } else {
              // 該当ユーザーなし
                $failed_email = false;
            }
        }

        if (!$failed_email) {
            $this->session->set_flashdata('success_msg', '指定された学籍番号・連絡先メールアドレスのユーザーがデータベースに登録されている場合、パスワードリセットメールを「連絡先メールアドレス」に送信しました。パスワードリセットの手順については、そのメールを確認してください。');
        }
        $this->session->set_flashdata('failed_email', $failed_email);
        codeigniter_redirect('users/reset_password');
    }

  /**
   * パスワードリセットメールを送信する
   * @param  string $email_send_to    送信先メールアドレス
   * @param  string $verifycode       送信する認証コード
   * @param  string $datetime_expired URLの有効期限
   * @param  string $name_to          宛先人の名前
   * @return bool                     送信に成功したらtrue
   */
    private function _send_passwordreset_email($email_send_to, $verifycode, $datetime_expired, $name_to)
    {
        $vars = [];
        $vars['name_to']          = $name_to;
        $vars['url']              = base_url("users/reset_password/verify/{$verifycode}");
        $vars['datetime_expired'] = $datetime_expired;
        return $this->_send_email($email_send_to, "パスワードリセットの手順のご案内【重要】", 'email/passwordreset', $vars);
    }

  /**
   * @param string $recieved_code 認証コード
   */
    public function verify($recieved_code)
    {
        $vars = [];
        $vars["xs_navbar_title"] = "パスワードリセット";
        $vars["xs_navbar_back"] = true; // 戻るボタンを表示

      # パスワード再設定フォーム
        if (empty($recieved_code)) {
            $this->_error('エラー', "このURLは無効です。");
        };

      // 認証
        $userinfo = $this->users->verify_password_reset_key($recieved_code);
        if ($userinfo !== false) {
          // 該当あり
            $this->session->set_flashdata('user_resetpassword', $userinfo);
            $this->_render('users/reset_password_2', $vars);
        } else {
          // 該当なし
            $this->_error('エラー', "このURLは無効です。\nこのURLが発行されてから24時間以上経過したか、使用済みです。");
        }
    }

    public function change_password()
    {
        $vars = [];
        $vars["xs_navbar_title"] = "パスワードリセット";
        $vars["xs_navbar_back"] = true; // 戻るボタンを表示

        if ($this->input->method() === 'post') {
          # パスワード設定
            $this->form_validation->set_rules("password", "パスワード", "required|callback__validate_password");
            $this->form_validation->set_rules("password_confirm", "パスワード(確認)", "required|matches[password]");

            if ($this->form_validation->run() && isset($_SESSION['user_resetpassword']) && $this->users->change_password($_SESSION['user_resetpassword']->id, $this->input->post('password'))) {
                // バリデーション&パスワード設定成功
                unset($_SESSION['user_resetpassword']);
                $vars["success"] = true;
                $this->_render('users/reset_password_3', $vars);
            } else {
              // バリデーション失敗
                $this->session->keep_flashdata('user_resetpassword');
                $this->_render('users/reset_password_2', $vars);
            }
        }
    }
}
