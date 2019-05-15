<?php

/**
 * 団体関連モデル
 */
class Circles_model extends MY_Model
{

    /**
     * 全団体数を取得する
     * @return int 全団体数
     */
    public function count_all()
    {
        $this->db->select("count(id) AS c", false);
        return (int)$this->db->get("circles")->row()->c ?? 0;
    }

    /**
     * 全ての団体情報を取得する
     * @return object[] 団体情報オブジェクトの配列。存在しない場合は空配列。
     */
    public function get_all()
    {
        $query = $this->db->get("circles");
        return $query->result();
    }

    /**
     * 指定されたユーザーIDのユーザーが所属している団体の情報を取得する
     * @param  int         $user_id ユーザーID( users.id )
     * @return object[]             団体情報オブジェクトの配列。存在しない場合は空配列。
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
     * 指定された団体IDに所属しているユーザーの情報を取得する
     * @param  int      $circle_id 団体ID( circles.id )
     * @return object[]            ユーザー情報オブジェクトの配列。存在しない場合は空配列。
     */
    public function get_user_info_by_circle_id($circle_id)
    {
        $this->db->where("circle_id", $circle_id);
        $query = $this->db->get("circle_user");
        $result = [];
        foreach ($query->result() as $user) {
            $result[] = $this->users->get_user_by_user_id($user->user_id);
        }
        return $result;
    }

    /**
     * 指定された団体IDの団体の情報を取得する
     * @param  int         $circle_id 団体ID( circles.id )
     * @return object|bool            団体情報オブジェクトの配列。存在しない場合 false
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
     * 指定された団体IDの団体情報を編集することができるか
     * @param  int $circle_id 団体ID( circles.id )
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
