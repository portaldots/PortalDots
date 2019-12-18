<?php
require_once __DIR__. '/Home_base_controller.php';

/**
 * home/Contacts コントローラ
 *
 * お問い合わせページ
 */
class Contacts extends Home_base_controller
{
    public function index()
    {
        $vars = [];
        $vars["main_page_type"] = "contacts";
        $vars["xs_navbar_title"] = "お問い合わせ";

        $this->_render('home/contacts', $vars);

        if ($this->input->method() === "post") {
            $this->_post_index();
        }
    }

    private function _post_index()
    {
        if (empty($this->input->post("body"))) {
            $this->_error("エラー", "お問い合わせ内容を入力してください。");
        }

        $vars_email = [
            "name_to" => $this->_get_login_user()->name_family. " ". $this->_get_login_user()->name_given,
            "name" => $this->_get_login_user()->name_family. " ". $this->_get_login_user()->name_given,
            "student_id" => $this->_get_login_user()->student_id,
            "email" => $this->_get_login_user()->email,
            "body" => $this->input->post("body"),
        ];
        $result = $this->_send_email(PORTAL_CONTACT_EMAIL, APP_NAME. "からのお問い合わせ", "email/contact", $vars_email, $this->_get_login_user()->email);

        if (! empty(RP_LINE_NOTIFY_TOKEN)) {
            $this->_send_to_line("email/contact_line_notify", $vars_email);
        }

        if ($result === true) {
            $this->_send_email($this->_get_login_user()->email, "お問い合わせを承りました", "email/contact_thanks", $vars_email);
            $this->session->set_flashdata("flashdata_success", "お問い合わせありがとうございました。");
        } else {
            $this->session->set_flashdata("flashdata_danger", "お問い合わせを送信できませんでした。恐れ入りますが、 ". PORTAL_CONTACT_EMAIL. " にメールでお問い合わせください。");
        }
        codeigniter_redirect("home");
    }
}
