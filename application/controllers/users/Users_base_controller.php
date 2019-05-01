<?php

/**
 * users ベースコントローラ
 */
class Users_base_controller extends MY_Controller
{
  /**
   * 学籍番号のフォームバリデーション用共通ルール
   * 末尾に「|」(パイブ)をつける必要はない
   * @var string
   */
    const STUDENT_ID_RULE = "trim|exact_length[7]|mb_strtolower";

  /**
   * ログインIDのフォームバリデーション用共通ルール
   * 末尾に「|」(パイブ)をつける必要はない
   * @var string
   */
    const LOGIN_ID_RULE = "required|trim|mb_strtolower";

  /**
   * 適切なパスワードであるかどうかを検証するフォームバリデーションコールバック関数
   *
   * CI_Form_validation クラスから呼び出されるため、
   * このメソッドは public である必要がある
   *
   * @param  string $password ユーザーによって入力されたパスワード文字列
   * @return bool             適切なパスワードである場合 true
   */
    public function _validate_password($password)
    {
        if (preg_match("/\A(?=.*?[a-z])(?=.*?\d)(?=.*?[!-\/:-@[-`{-~])[!-~]{10,1024}+\z/i", $password)) {
            return true;
        }

        $this->form_validation->set_message("_validate_password", "パスワードは、10文字以上、かつ数字・英字・記号を最低1文字含むものにしてください");
        return false;
    }
}
