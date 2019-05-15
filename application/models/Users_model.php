<?php

/**
 * Users モデル
 */
class Users_model extends MY_Model
{

    /**
     * POSTされたログイン情報でログインができるかどうか
     * @return bool ログインが可能の場合は true
     */
    public function login($login_id, $plain_password)
    {
        $this->db->where("student_id", $login_id);
        $this->db->or_where("email", $login_id);
        $query = $this->db->get("users");
        $result = $query->result();

        if ($query->num_rows() === 1 && $this->verify_password_hash($plain_password, $result[0]->password)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * ユーザーIDからユーザー情報を取得する
     * @param string $user_id ユーザーID
     * @return object|bool          ユーザー情報オブジェクト。存在しない場合はfalse
     */
    public function get_user_by_user_id($user_id)
    {
        $this->db->where("id", $user_id);
        $query = $this->db->get("users");
        if ($query->num_rows() === 1) {
            $result = $query->result()[0];
            $result->roles = $this->get_role_user_by_user_id($user_id);
            $result->is_admin = false;
            if (is_array($result->roles) && in_array("0", $result->roles, true)) {
                $result->is_admin = true;
            }
            return $result;
        } else {
            return false;
        }
    }

    /**
     * ログインIDからユーザー情報を取得する
     * @param string $login_id ログインID
     * @return object|bool           ユーザー情報オブジェクト。存在しない場合はfalse
     */
    public function get_user_by_login_id($login_id)
    {
        $this->db->where("student_id", $login_id);
        $this->db->or_where("email", $login_id);
        $query = $this->db->get("users");
        if ($query->num_rows() === 1) {
            $result = $query->result()[0];
            $result->roles = $this->get_role_user_by_user_id($result->id);
            $result->is_admin = false;
            if (is_array($result->roles) && in_array("0", $result->roles, true)) {
                $result->is_admin = true;
            }
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 指定したユーザーIDのユーザーがもつ権限のIDを配列で取得する
     * @param int $user_id ユーザーID( users.id )
     * @return int[] 権限IDの配列
     */
    public function get_role_user_by_user_id($user_id)
    {
        $this->db->where("user_id", $user_id);
        $query = $this->db->get("role_user");
        $return = [];
        foreach ($query->result() as $result) {
            $return[] = $result->role_id;
        }
        return $return;
    }

    /**
     * ユーザー権限IDから権限情報を取得する
     * @param int $role_id ユーザー権限ID( roles.id )
     * @return object|bool          権限情報オブジェクト。存在しない場合はfalse
     */
    public function get_role_info_by_role_id($role_id)
    {
        $this->db->where("id", $role_id);
        $query = $this->db->get("roles");
        if ($query->num_rows() === 1) {
            return $query->result()[0];
        } else {
            return false;
        }
    }

    /**
     * 権限情報を全て取得する
     * @return object[] 権限情報オブジェクトの配列。
     */
    public function get_all_roles()
    {
        return $this->db->get("roles")->result();
    }

    /**
     * パスワードリセット用の認証コードをデータベースに設定する。認証コードを取得する。
     * @param string $login_id ログインID
     * @return string|bool           データベースに設定できたら設定した認証コード。失敗した場合、false
     */
    public function begin_password_reset($login_id)
    {
        $verifycode = $this->create_verifycode();
        $this->db->where("student_id", $login_id);
        $this->db->or_where("email", $login_id);
        $this->db->set("reset_pass_key", $verifycode);
        $this->db->set("reset_pass_key_created_at", date('Y-m-d H:i:s'));
        $result = $this->db->update("users");
        if ($result) {
            return $verifycode;
        }
        return false;
    }

    /**
     * パスワードリセット用の認証コードが正しいかを検証し、正しければデータベースから削除する
     * @param string $verifycode 認証コード
     * @return object|bool 正しければ、該当ユーザーのユーザー情報オブジェクト。間違っていれば false
     */
    public function verify_password_reset_key($verifykey)
    {
        $this->db->where("reset_pass_key", $verifykey);
        $query = $this->db->get('users');
        $userinfo = $query->result();
        if ($query->num_rows() === 1 && $this->timecheck_verifycode($userinfo[0]->reset_pass_key_created_at)) {
            $userinfo = $userinfo[0];
            $this->db->where("reset_pass_key", $verifykey);
            $this->db->set("reset_pass_key", null);
            $this->db->set("reset_pass_key_created_at", null);
            $this->db->update('users');
            return $this->get_user_by_user_id($userinfo->id);
        }
        return false;
    }

    /**
     * 指定されたユーザーのパスワードを変更する
     * @param string $user_id ユーザーID
     * @param string $new_plain_password 新しいパスワード(平文)
     * @return bool                       成功した場合 true
     */
    public function change_password($user_id, $new_plain_password)
    {
        $this->db->where('id', $user_id);
        $this->db->set('password', $this->create_password_hash($new_plain_password));
        return $this->db->update('users');
    }

    /**
     * 指定されたログインIDのデータをUsersテーブルから削除する
     * 原則として、仮登録メールの送信に失敗した場合や、仮登録メール送信から24時間経過した場合を除き、このメソッドを使用してはならない
     * @param string $login_id ログインID
     * @return bool             削除に成功した場合 true
     */
    public function delete_by_login_id($login_id)
    {
        $this->db->where("student_id", $login_id);
        $this->db->or_where("email", $login_id);
        return $this->db->delete("users");
    }

    /**
     * 指定されたログインIDのデータをUsers_preテーブルから削除する
     * @param string $login_id ログインID
     * @return bool             削除に成功した場合 true
     */
    public function delete_pre_by_login_id($login_id)
    {
        $this->db->where("student_id", $login_id);
        $this->db->or_where("email", $login_id);
        return $this->db->delete("users_pre");
    }

    /**
     * 仮登録ユーザーとしてデータベースに登録
     * @return array メール認証コード
     */
    public function add_temp_user(
        $student_id,
        $email,
        $name_family,
        $name_given,
        $name_family_yomi,
        $name_given_yomi,
        $plain_password
    )
    {
        // すでに，学籍番号またはメールアドレスが users_pre に登録されていないかどうかを確認する
        if ($this->get_user_pre_by_login_id($student_id) !== false ||
            $this->get_user_pre_by_login_id($email) !== false) {
            // すでに登録されている情報を削除する
            // (すなわち，該当する学籍番号または連絡先メールアドレスでの，以前の仮登録を無効にする)
            $this->delete_pre_by_login_id($student_id);
            $this->delete_pre_by_login_id($email);
        }

        $this->db->set('name_family', $name_family);
        $this->db->set('name_given', $name_given);
        $this->db->set('email', $email);
        $this->db->set('created_at', date('Y-m-d H:i:s'));

        // 学籍番号が未入力の場合，null にする
        $this->db->set('student_id', $student_id ?? null);

        // 氏名の読みは、カタカナをひらがなに直してからデータベースに入れる
        $this->db->set('name_family_yomi', mb_convert_kana($name_family_yomi, 'c'));
        $this->db->set('name_given_yomi', mb_convert_kana($name_given_yomi, 'c'));

        // パスワードをハッシュにする
        $this->db->set('password', $this->create_password_hash($plain_password));

        // 認証コード作成
        $univemail = $student_id . '@'. PORTAL_UNIVEMAIL_DOMAIN;
        $verifycodes = [];
        if (empty($student_id)) {
            // 学籍番号が空
            $verifycodes['univemail'] = null;
            // 認証コード
            $verifycodes['email'] = $this->create_verifycode();
            $this->db->set('verifycode_email', $verifycodes['email']);
        } elseif ($univemail === $email) {
            // 連絡先メールアドレスと大学提供のメールアドレスが一致するため、連絡先メールアドレスの認証はすでに完了したとみなす
            $verifycodes['email'] = null;
            // 認証コード
            $verifycodes['univemail'] = $this->create_verifycode();
            $this->db->set('verifycode_univemail', $verifycodes['univemail']);
        } else {
            // 大学メール
            $verifycodes['univemail'] = $this->create_verifycode();
            $this->db->set('verifycode_univemail', $verifycodes['univemail']);
            // 連絡先メール
            $verifycodes['email'] = $this->create_verifycode();
            $this->db->set('verifycode_email', $verifycodes['email']);
        }

        $this->db->insert('users_pre');
        return $verifycodes;
    }

    /**
     * メールアドレス認証コードが正しいかどうかを検証する
     * @param string $type univemailかemailか
     * @param string $code メールアドレス認証コード
     * @return object|bool  正しければユーザー情報、誤りであればfalse
     */
    public function validate_verifycode($type, $code)
    {

        if ($type === 'univemail' || $type === 'email') {
            $this->db->where("verifycode_{$type}", $code);
            $query = $this->db->get('users_pre');
            $result = $query->result();
            if ($query->num_rows() === 1 && $this->timecheck_verifycode($result[0]->created_at)) {
                $this->delete_verifycode($type, $result[0]->id);
                return $this->get_user_pre_by_id($result[0]->id); // $result を return すると，verify 未完了のままのデータが return されてしまう．
            } else {
                return false;
            }
        }

        return false;
    }

    /**
     * POSTされたログイン情報で users_pre のログインができるかどうか
     * @return bool ログインが可能の場合は true
     */
    public function login_pre($login_id, $plain_password)
    {
        $this->db->where("student_id", $login_id);
        $this->db->or_where("email", $login_id);
        $query = $this->db->get("users_pre");
        $result = $query->result();

        if ($query->num_rows() === 1 && $this->verify_password_hash($plain_password, $result[0]->password)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * users_pre のユーザー情報を id で取得する
     * @param string $id users_pre の ID
     * @return object|bool     ユーザー情報オブジェクト。存在しない場合はfalse
     */
    public function get_user_pre_by_id($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->get("users_pre");
        if ($query->num_rows() === 1) {
            $result = $query->result()[0];
            return $result;
        } else {
            return false;
        }
    }

    /**
     * ログインIDから users_pre のユーザー情報を取得する
     * @param string $login_id ログインID
     * @return object|bool           ユーザー情報オブジェクト。存在しない場合はfalse
     */
    public function get_user_pre_by_login_id($login_id)
    {
        $this->db->where("student_id", $login_id);
        $this->db->or_where("email", $login_id);
        $query = $this->db->get("users_pre");
        if ($query->num_rows() === 1) {
            $result = $query->result()[0];
            return $result;
        } else {
            return false;
        }
    }

    /**
     * ユーザー情報の仮登録データを、users テーブルへ移動させる
     * @param string $user_pre_id 移動させたいユーザー情報の、 users_pre における ID
     * @return bool                処理が成功すれば true
     */
    public function move_user_from_temp_user($user_pre_id)
    {
        // 仮登録データを取得する
        $userinfo = $this->get_user_pre_by_id($user_pre_id);

        if ($userinfo !== false) {
            // 移行するデータベースフィールド
            $fields = ["student_id", "name_family", "name_family_yomi", "name_given", "name_given_yomi", "email", "password", "created_at"];
            foreach ($fields as $field) {
                $this->db->set($field, $userinfo->$field);
            }
            // updated_at は、現在時刻にセットする
            $this->db->set("updated_at", date("Y-m-d H:i:s"));
            // users テーブルに挿入する
            $result = $this->db->insert("users");
            if ($result === true) {
                // users_pre から削除
                $this->db->where("id", $user_pre_id);
                $this->db->delete("users_pre");
                return true;
            } else {
                // users テーブルに挿入失敗
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 確認メールが送信されてから24時間以内かどうか
     * @param string $created_at データベース上に保存されている、アカウントの作成日時
     * @return bool               24時間以内であればtrue
     */
    private function timecheck_verifycode($created_at)
    {
        $deadline_datetime = (new DateTime($created_at))->modify('+24 hours');
        if ($deadline_datetime > $this->get_datetime_now()) {
            return true;
        }
        return false;
    }

    /**
     * 現在時刻の DateTime オブジェクトを返す
     * @return DateTime 現在時刻の DateTime オブジェクト
     */
    private function get_datetime_now()
    {
        return new DateTime();
    }

    /**
     * データベース上の、メールアドレス認証コードを削除する
     * @param string $type univemailかemailか
     * @param string $user_id 認証コードを削除するユーザーのユーザーID
     * @return void
     */
    private function delete_verifycode($type, $user_id)
    {
        $this->db->set("verifycode_{$type}", null);
        $this->db->where('id', $user_id);
        $this->db->update('users_pre');
    }

    /**
     * メールアドレス認証コードを作成する
     * @return string メールアドレス認証コード
     */
    private function create_verifycode()
    {
        $code = openssl_random_pseudo_bytes(33) . openssl_random_pseudo_bytes(33);
        return hash('sha256', base64_encode($code));
    }

    private function create_password_hash($password)
    {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
    }

    private function verify_password_hash($plain, $hashed)
    {
        return password_verify($plain, $hashed);
    }
}
