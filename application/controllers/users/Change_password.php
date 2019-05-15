<?php
require_once __DIR__. '/Users_base_controller.php';

/**
 * users/Change_password コントローラ
 */
class Change_password extends Users_base_controller
{
    public function index()
    {
        $this->_require_login();

        $this->load->library("form_validation");

        $vars = [];
        $vars["xs_navbar_title"] = "パスワード変更";
        $vars["xs_navbar_back"] = true; // 戻るボタンを表示

        $vars["login_id"] = $this->_get_login_user()->email;

        if ($this->input->method() === 'post') {
            $this->_post_index();
        }

        $this->_render('users/change_password', $vars);
    }

    private function _post_index()
    {
        $this->form_validation->set_rules("login_id", "学籍番号・連絡先メールアドレス", self::LOGIN_ID_RULE. "|callback__validate_credentials_for_change_password");
        $this->form_validation->set_rules("password", "現在のパスワード", "required");
        $this->form_validation->set_rules("new_password", "新パスワード", "required|callback__validate_password");
        $this->form_validation->set_rules("new_password_confirm", "新パスワード(確認)", "required|matches[new_password]");

        if ($this->form_validation->run()) {
            $userinfo = $this->users->get_user_by_login_id($this->input->post("login_id"));
            $this->users->change_password($userinfo->id, $this->input->post('new_password'));
            $this->session->set_flashdata("flashdata_success", "パスワードは正常に変更されました。");
            codeigniter_redirect("home");
        }
    }

  /**
   * パスワード変更フォームでの，ログイン情報検証用フォームバリデーションコールバック関数
   * @return bool ログインが可能な可能なログイン情報の場合 true
   */
    public function _validate_credentials_for_change_password()
    {
        if ($this->users->login(
            $this->input->post('login_id'),
            $this->input->post('password')
        )) {
            return true;
        } else {
            $this->form_validation->set_message("_validate_credentials_for_change_password", "現在のパスワードが違います");
            return false;
        }
    }
}
