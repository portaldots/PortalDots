<?php
require_once __DIR__. '/Users_base_controller.php';

/**
 * users/Login コントローラ
 */
class Login extends Users_base_controller
{
    public function index()
    {
        if (! empty($this->_get_login_user())) {
            redirect("home");
        }

        $vars = [];
        $vars["xs_navbar_title"] = RP_PORTAL_NAME;

        $this->load->library("form_validation");

        $login_id = '';

        if ($this->input->method() === 'post') {
            $this->_post_index();
        }

        $vars["login_id"] = $login_id;
        $vars["login_return_uri"] = $_SESSION["login_return_uri"] ?? null;
        $this->_render('users/login', $vars);
    }

  /**
   * index に POST された時の処理
   */
    private function _post_index()
    {
        $login_id = $this->input->post('login_id');

        $this->form_validation->set_rules("login_id", "学籍番号・連絡先メールアドレス", self::LOGIN_ID_RULE. "|callback__validate_credentials");
        $this->form_validation->set_rules("password", "パスワード", "required");

        if ($this->form_validation->run()) {
          // バリデーション成功
            $userinfo = $this->users->get_user_by_login_id($login_id);
            $this->_login($userinfo->id);
            redirect("/");
        } else {
          // バリデーション失敗
          // 仮登録されているかどうかをチェックする
            if ($this->users->login_pre(
                $this->input->post("login_id"),
                $this->input->post("password")
            ) ) {
                session_regenerate_id(true);
                $userinfo = $this->users->get_user_pre_by_login_id($login_id);
                $this->session->set_flashdata('user_checked', $userinfo);
                redirect("users/verify/status");
            }
        }
    }

  /**
   * ログイン情報検証用フォームバリデーションコールバック関数
   * @return bool ログインが可能な可能なログイン情報の場合 true
   */
    public function _validate_credentials()
    {
        if ($this->users->login(
            $this->input->post('login_id'),
            $this->input->post('password')
        )) {
            return true;
        } else {
            $this->form_validation->set_message("_validate_credentials", "学籍番号かパスワードが違います");
            return false;
        }
    }
}
