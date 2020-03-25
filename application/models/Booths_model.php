<?php

/**
 * ブース関連モデル
 */
class Booths_model extends MY_Model
{

    /**
     * 全ブース数を取得する
     * @return int 全ブース数
     */
    public function count_all()
    {
        $this->db->select("count(id) AS c", false);
        return (int)$this->db->get("booths")->row()->c ?? 0;
    }

    /**
     * 指定された企画IDの企画が行うブースの情報を取得する
     *  - circle_name : circles.name
     *  - place_name : places.name ( ブースの場所名 )
     *  - booth_id : booths.id ( ブースID )
     *  - booth_name : booths.name ( ブース名 )
     * @param int $circle_id 企画ID( circles.id )
     * @return object[]               ブース情報オブジェクトの配列。存在しない場合は空配列。
     */
    public function get_booth_info_by_circle_id($circle_id)
    {
        $select = [
            "circles.name AS circle_name",
            "places.name AS place_name",
            "places.type AS place_type",
            "booths.id AS booth_id",
            "booths.created_at AS created_at",
            "booths.created_by AS created_by",
            "booths.updated_at AS updated_at",
            "booths.updated_by AS updated_by",
        ];
        $this->db->select(implode(",", $select), false);
        $this->db->from("booths");
        $this->db->join("circles", "circles.id = booths.circle_id");
        $this->db->join("places", "places.id = booths.place_id");
        $this->db->where("circles.id", $circle_id);
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * 指定されたブースIDのブースの情報を取得する
     * @param int $booth_id ブースID( booths.id )
     * @return object|bool            ブース情報オブジェクトの配列。存在しない場合 false
     */
    public function get_booth_info_by_booth_id($booth_id)
    {
        $select = [
            "booths.id AS id",
            "booths.place_id AS place_id",
            "booths.circle_id AS circle_id",
            "places.name AS place_name",
            "booths.created_at AS created_at",
            "booths.created_by AS created_by",
            "booths.updated_at AS updated_at",
            "booths.updated_by AS updated_by",
            "booths.notes AS notes",
            "places.type AS place_type",
        ];
        $this->db->select(implode(",", $select), false);

        $this->db->join("places", "places.id = booths.place_id");
        $this->db->where("booths.id", $booth_id);
        $query = $this->db->get("booths");
        if ($query->num_rows() === 1) {
            return $query->result()[0];
        } else {
            return false;
        }
    }

    /**
     * 指定されたブースIDのブース情報を編集することができるか
     * @param int $booth_id ブースID( circles.id )
     * @param int $user_id ユーザーID( users.id )
     * @return bool          編集可能の場合 true
     */
    public function can_edit($booth_id, $user_id)
    {

        $booth_info = $this->get_booth_info_by_booth_id($booth_id);
        if ($booth_info === false) {
            return false;
        }

        $this->db->where("circle_id", $booth_info->circle_id);
        $this->db->where("user_id", $user_id);
        $query = $this->db->get("circle_user");
        if ((int)$query->num_rows() === 0) {
            return false;
        }

        return true;
    }
}
