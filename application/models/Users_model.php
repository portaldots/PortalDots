<?php

/**
 * Users モデル
 */
class Users_model extends MY_Model
{
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
}
