<?php

/**
 * 認可関連モデル
 *
 * [認可について]
 * (2019/05/01 追記) 以下のメモが現状の実装と一致しているかどうかは未確認です。
 * 
 * - 認可設定のなされていないページへのアクセスは、原則認可する。
 * - 原則認可(ブラックリスト方式)では、ブラックリストに登録されている権限をもつユーザーによるアクセスを拒否する。なお、ホワイトリストは無視される。
 * - 原則不認可(ホワイトリスト方式)では、ホワイトリストに登録されている権限をもつユーザーによるアクセスのみを許可する。なお、ブラックリストは無視される。
 * - 管理者のみがアクセスできるmain_page_typeについては、認可設定を行っても、管理者しかアクセスできない。
 * - ユーザーが属する role が、ブラックリストとホワイトリストの両方にまたがっている場合、ホワイトリストを優先する。
 *   例えば、ユーザーAが権限P、権限Qを持つと仮定する。ページZが権限Pによるアクセスを認可し、権限Qによるアクセスを不認可としていた場合でも、
 *   ユーザーAは、ページZにアクセスすることができる。なぜならば、ユーザーAは、ページZが認可とする権限Pを持っているからである。
 * - ホワイトリスト、またはブラックリストに Admin を追加しても、管理者によるアクセスを認可したり拒否したりすることはできない。どのような場合であっても、
 *   管理者は、全てのmain_page_typeにアクセスすることができる。
 */
class Auth_model extends MY_Model
{

  /**
   * 指定したスタッフユーザーは、指定したmain_page_typeの閲覧を許可されているか
   *
   * 指定したユーザーの is_staff が 0 であれば、常に false を返す
   * is_admin が true であれば、常に true を返す
   * なお、認可システムの仕様については、このファイル冒頭の[認可について]を参照
   * @param  string  $main_page_type main_page_typeの値。
   * @param  object  $user           ユーザー情報オブジェクト。roles 必須。
   * @return boolean                 閲覧が許可されている場合 true
   */
    public function is_staff_user_authorized($main_page_type, $user)
    {
        if ($user->is_admin === true) {
            return true;
        }
        if ((int)$user->is_staff !== 1) {
            return false;
        }

        $auth_staff_page = $this->get_auth_staff_page_by_main_page_type($main_page_type);
        if ($auth_staff_page !== false) {
            foreach ($user->roles as $role) {
                $auth_staff_role = $this->get_auth_staff_role($auth_staff_page->id, (int)$role);
                if ($auth_staff_role !== false && (int)$auth_staff_role->is_authorized === 1) {
                    return true;
                }
            }

            if ((int)$auth_staff_page->is_authorized === 1) {
                return true;
            }

            return false;
        } else {
            return true;
        }
    }

  /**
   * 指定したmain_page_typeのauth_staff_page情報を取得する
   * @param  string      $msin_page_type main_page_typeの値。
   * @return object|bool                 auth_staff_page情報オブジェクト。存在しない場合 false
   */
    public function get_auth_staff_page_by_main_page_type($main_page_type)
    {
        $this->db->where("main_page_type", $main_page_type);
        $query = $this->db->get("auth_staff_page");
        if ($query->num_rows() === 1) {
            return $query->result()[0];
        } else {
            return false;
        }
    }

  /**
   * 指定したauth_staff_pageのIDとユーザー権限IDのauth_staff_role情報を取得する
   * @param  int         $id      auth_staff_page.id。
   * @param  int         $role_id ユーザー権限ID。
   * @return object|bool          auth_staff_role情報オブジェクト。存在しない場合 false
   */
    public function get_auth_staff_role($id, $role_id)
    {
        $this->db->where("auth_staff_page_id", $id);
        $this->db->where("role_id", $role_id);
        $query = $this->db->get("auth_staff_role");
        if ($query->num_rows() === 1) {
            return $query->result()[0];
        } else {
            return false;
        }
    }

  /**
   * auth_staff_page を全て取得する。
   * @return object auth_staff_page情報オブジェクトの配列。
   */
    public function get_all_auth_staff_page()
    {
        $result = $this->db->get("auth_staff_page")->result();
        $roles = $this->users->get_all_roles();
        $roles_list = [];
        foreach ($roles as $item) {
            $roles_list[$item->id] = $item->name;
        }
        for ($i = 0; $i < count($result); ++$i) {
            $this->db->where("auth_staff_page_id", $result[$i]->id);
            $list = $this->db->get("auth_staff_role")->result();
            $result[$i]->list_white = [];
            $result[$i]->list_black = [];
            foreach ($list as $item) {
                $item->role_name = $roles_list[$item->role_id] ?? null;
                if ($item->role_name === null) {
                    continue;
                }
                if ((int)$item->is_authorized === 1) {
                    $result[$i]->list_white[] = $item;
                } else {
                    $result[$i]->list_black[] = $item;
                }
            }
        }
        return $result;
    }

  /**
   * auth_staff_page に項目を追加する。
   * @return bool 成功した場合 true。
   */
    public function add_auth_staff_page($main_page_type, $is_authorized)
    {
        $this->db->set("main_page_type", $main_page_type);
        $this->db->set("is_authorized", $is_authorized);
        return $this->db->insert("auth_staff_page");
    }

  /**
   * auth_staff_role に項目を追加する。
   * @return bool 成功した場合 true。
   */
    public function add_auth_staff_role($auth_staff_page_id, $role_id, $is_authorized)
    {
        $this->db->set("auth_staff_page_id", $auth_staff_page_id);
        $this->db->set("role_id", $role_id);
        $this->db->set("is_authorized", $is_authorized);
        return $this->db->insert("auth_staff_role");
    }

  /**
   * auth_staff_page の項目を編集する。
   * @return bool 成功した場合 true。
   */
    public function edit_auth_staff_page($auth_staff_page_id, $is_authorized)
    {
        $this->db->set("is_authorized", $is_authorized);
        $this->db->where("id", $auth_staff_page_id);
        return $this->db->update("auth_staff_page");
    }

  /**
   * 指定されたID の auth_staff_page を削除する。
   * @param  int  auth_staff_page.id
   * @return bool 成功した場合 true。
   */
    public function delete_auth_staff_page($id)
    {
        return $this->db->where("id", $id)->delete("auth_staff_page") &&
        $this->db->where("auth_staff_page_id", $id)->delete("auth_staff_role");
    }

  /**
   * 指定されたID の auth_staff_role を削除する。
   * @param  int  auth_staff_role.id
   * @return bool 成功した場合 true。
   */
    public function delete_auth_staff_role($id)
    {
        return $this->db->where("id", $id)->delete("auth_staff_role");
    }
}
