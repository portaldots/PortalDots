<?php

/**
 * 企画関連モデル
 *
 * もともと Circle は企画を意味していたが、企画単位での参加登録を受け付ける
 * システムに改修したため、モデル上の名前は Circle だが、企画を意味している
 */
class Circles_model extends MY_Model
{

    /**
     * 全企画数を取得する
     * @return int 全企画数
     */
    public function count_all()
    {
        $this->db->select("count(id) AS c", false);
        $this->db->where("status", "approved");
        return (int)$this->db->get("circles")->row()->c ?? 0;
    }

    /**
     * 全ての企画情報を取得する
     * @return object[] 企画情報オブジェクトの配列。存在しない場合は空配列。
     */
    public function get_all()
    {
        $query = $this->db->get("circles");
        return $query->result();
    }

    /**
     * 指定されたユーザーIDのユーザーが所属している企画の情報を取得する
     * @param  int         $user_id ユーザーID( users.id )
     * @return object[]             企画情報オブジェクトの配列。存在しない場合は空配列。
     */
    public function get_circle_info_by_user_id($user_id)
    {
      // select * from circle_user join circles on circles.id = circle_user.circle_id where circle_user.user_id = ?
        $this->db->from("circle_user");
        $this->db->join("circles", "circles.id = circle_user.circle_id");
        $this->db->where("circle_user.user_id", $user_id);
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * 指定された企画IDに所属しているユーザーの情報を取得する
     * @param  int      $circle_id 企画ID( circles.id )
     * @return object[]            ユーザー情報オブジェクトの配列。存在しない場合は空配列。
     */
    public function get_user_info_by_circle_id($circle_id)
    {
        $this->db->where("circle_id", $circle_id)->order_by("is_leader", 'desc');
        $query = $this->db->get("circle_user");
        $result = [];
        foreach ($query->result() as $user) {
            $res = $this->users->get_user_by_user_id($user->user_id);
            $res->is_leader = $user->is_leader;
            $result[] = $res;
        }
        return $result;
    }

    /**
     * 指定された企画IDの企画の情報を取得する
     * @param  int         $circle_id 企画ID( circles.id )
     * @return object|bool            企画情報オブジェクトの配列。存在しない場合 false
     */
    public function get_circle_info_by_circle_id($circle_id)
    {
        $this->db->where("id", $circle_id);
        $query = $this->db->get("circles");
        if ($query->num_rows() === 1) {
            return $query->result()[0];
        } else {
            return false;
        }
    }

    /**
     * 指定された企画IDの企画情報を編集することができるか
     * @param  int $circle_id 企画ID( circles.id )
     * @param  int $user_id   ユーザーID( users.id )
     * @return bool           編集可能の場合 true
     */
    public function can_edit($circle_id, $user_id)
    {
        $circle_info = $this->get_circle_info_by_circle_id($circle_id);
        if ($circle_info === false) {
            return false;
        }

        $this->db->where("circle_id", $circle_id);
        $this->db->where("user_id", $user_id);
        $query = $this->db->get("circle_user");
        if ((int)$query->num_rows() === 0) {
            return false;
        }

        return true;
    }
}
