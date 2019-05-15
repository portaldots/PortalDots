<?php
require_once __DIR__ . '/Users_base_controller.php';

/**
 * users/Signup コントローラ
 */
class Signup extends Users_base_controller
{
    public function index()
    {
        if (!empty($this->_get_login_user())) {
            codeigniter_redirect("/");
        }

        $this->load->library("form_validation");

        $vars = [];
        $vars["xs_navbar_title"] = "ユーザー登録";
        $vars["xs_navbar_back"] = true; // 戻るボタンを表示

        // メール送信に失敗した場合true
        $failed_email = false;

        if ($this->input->method() === 'post') {
            $this->_post_index();
        }

        $vars['failed_email'] = $failed_email;
        $this->_render('users/signup', $vars);
    }

    private function _post_index()
    {
        $form_fields = [
            'student_id',
            'name_family',
            'name_family_yomi',
            'name_given',
            'name_given_yomi',
            'email',
            'password'
        ];

        foreach ($form_fields as $field) {
            $vars[$field] = $this->input->post($field);
        }

        $this->form_validation->set_rules("student_id", "学籍番号", self::STUDENT_ID_RULE . "|is_unique[users.student_id]");
        $this->form_validation->set_rules("password", "パスワード", "required|callback__validate_password");
        $this->form_validation->set_rules("password_confirm", "パスワード(確認)", "required|matches[password]");

        $this->form_validation->set_rules("name_family", "姓", "required|trim");
        $this->form_validation->set_rules("name_family_yomi", "姓(よみ)", "required|trim|callback__validate_yomi");
        $this->form_validation->set_rules("name_given", "名", "required|trim");
        $this->form_validation->set_rules("name_given_yomi", "名(よみ)", "required|trim|callback__validate_yomi");
        $this->form_validation->set_rules("email", "連絡先メールアドレス", "required|valid_email|trim|is_unique[users.email]");
        $this->form_validation->set_rules("agree", "同意", "required");

        if ($this->form_validation->run()) {
            // バリデーション成功
            $verifycodes = $this->users->add_temp_user(
                $this->input->post('student_id'),
                $this->input->post('email'),
                $this->input->post('name_family'),
                $this->input->post('name_given'),
                $this->input->post('name_family_yomi'),
                $this->input->post('name_given_yomi'),
                $this->input->post('password')
            );
            /*
              $verifycodes について

              $verifycodes は連想配列となっている
                - $verifycodes['univemail'] : 大学提供メールアドレス用確認メールに記載する認証コード
                - $verifycodes['email'] : 連絡先メールアドレス用確認メールに記載する認証コード。univemailとemailが一致する場合はnull
            */

            // 大学提供メールアドレス
            $univemail = !empty($vars['student_id']) ?
                mb_strtolower($vars['student_id']) . '@'. PORTAL_UNIVEMAIL_DOMAIN : null;

            // 送信先のメールアドレスはどこか
            $email_send_to = [];
            if ($univemail === $vars['email']) {
                // 連絡先メールアドレスと大学提供のメールアドレスが同じ場合、確認メールは１通のみで良い
                $email_send_to = ['univemail' => $univemail];
            } elseif (empty($univemail)) {
                // 学籍番号が空の場合，確認メールは，連絡先メールアドレスのみに対して１通のみで良い
                $email_send_to = ['email' => $vars['email']];
            } else {
                // 連絡先メールアドレス大学提供のメールアドレスが異なる場合、確認メールは、それぞれのメアドに対して１通ずつ(合計2通)送信する
                $email_send_to = ['univemail' => $univemail, 'email' => $vars['email']];
            }

            $name_to = $vars['name_family'] . ' ' . $vars['name_given'];
            $datetime_expired = (new DateTime())->modify('+24 hours')->format('Y/m/d H:i');

            // 仮登録メールを送信する
            try {
                $result = $this->_send_verify_email($email_send_to, $verifycodes, $datetime_expired, $name_to);
                if (!$result) {
                    throw new Exception();
                }
                $this->session->set_flashdata('email_sent_to', $email_send_to);
                codeigniter_redirect('users/sent_confirm_email');
            } catch (Exception $e) {
                // データベースの登録を削除
                $failed_email = true;
                $login_id = !empty($vars['student_id']) ? $vars['student_id'] : $vars['email'];
                $this->users->delete_pre_by_login_id($login_id);
                $this->session->set_flashdata('email_sent_to', $email_send_to);
            }
        }
    }

    /**
     * 名前の読み入力欄に仮名文字が入力されているかどうかを検証するフォームバリデーションコールバック関数
     * @param string $yomi ユーザーによって入力された読み
     * @return bool         適切な読みである場合 true
     */
    public function _validate_yomi($yomi)
    {
        if (preg_match("/^[ァ-ヾぁ-ゞ]+$/u", $yomi)) {
            return true;
        }

        $this->form_validation->set_message("_validate_yomi", "{field}欄は仮名文字で入力してください");
        return false;
    }

    /**
     * 認証コードを送信する
     * @param array $email_send_to 送信先連想配列( type => email )
     * @param array $verifycodes 送信するメール認証コード( type => verifycode )
     * @param string $datetime_expired URLの有効期限
     * @param string $name_to 宛先人の名前
     * @return bool                     送信に成功したらtrue
     */
    public function _send_verify_email($email_send_to, $verifycodes, $datetime_expired, $name_to)
    {
        $result = true;
        foreach ($email_send_to as $type => $to) {
            $verifycode = $verifycodes[$type];

            $vars = [];
            $vars['name_to'] = $name_to;
            $vars['url'] = base_url("users/verify/{$type}/{$verifycode}");
            $vars['datetime_expired'] = $datetime_expired;

            if ((int)count($email_send_to) === 2) {
                // このメールとは別の送信先
                $vars['another_email'] = $type === 'univemail' ? $email_send_to['email'] : $email_send_to['univemail'];
            }
            $result_send = $this->_send_email($to, "認証コード送付【重要】", 'email/verify', $vars);
            if ($result === true) {
                $result = $result_send;
            }
        }
        return $result;
    }
}
