<?php

/**
 * Pages モデル
 */
class Pages_model extends MY_Model
{

  /**
   * POST された内容をもとに，ページを追加する
   * @return int 追加されたデータの，テーブル上のID
   */
    public function add(
        $title,
        $body,
        $user,
        $notes
    ) {
        $this->db->set("title", $title);
        $this->db->set("body", $body);
        $datetime = date("Y-m-d H:i:s");
        $this->db->set("created_at", $datetime);
        $this->db->set("updated_at", $datetime);
        $this->db->set("created_by", $user->id);
        $this->db->set("updated_by", $user->id);
        $this->db->set("notes", $notes);
        $this->db->insert("pages");
        return $this->db->insert_id();
    }

  /**
   * ページを全て取得する
   * @return object[] ページ全件がオブジェクトとなっている配列
   */
    public function get_all()
    {
        $this->db->order_by("created_at", "desc");
        $query = $this->db->get("pages");
        $results = $query->result();
        for ($i = 0; $i < count($results); ++$i) {
            $results[$i] = $this->processing_data($results[$i]);
        }
        return $results;
    }

  /**
   * 件数を取得する
   * @return int 件数
   */
    public function count_all()
    {
        return $this->db->count_all_results("pages");
    }

  /**
   * 指定した件数だけページを取得する
   * @param  int $count  取得したい件数
   * @param  int $offset オフセット
   * @return object[]    取得したページがオブジェクトとなっている配列
   */
    public function get_list($count, $offset = 0)
    {
        $this->db->order_by("created_at", "desc");
        $this->db->limit($count, $offset);
        $query = $this->db->get("pages");
        $results = $query->result();
        for ($i = 0; $i < count($results); ++$i) {
            $results[$i] = $this->processing_data($results[$i]);
        }
        return $results;
    }

  /**
   * ページIDからページ情報を取得する
   * @param  int         $page_id ページID
   * @return object|bool          ページ情報オブジェクト。存在しない場合はfalse
   */
    public function get_page_by_page_id($page_id)
    {
        $this->db->where("id", $page_id);
        $query = $this->db->get("pages");
        if ($query->num_rows() === 1) {
            $result = $query->result()[0];
            $result = $this->processing_data($result);
            return $result;
        } else {
            return false;
        }
    }

  /**
   * Pages テーブルから取得した情報を加工する
   *
   *  - body の内容をリスト表示用に省略して body_trimmed とする
   *  - body の内容を HTML にパースして body_html とする
   *  - 作成日時を人間にとってわかりやすい形式に変換し created_at_string とする
   *  - 更新日時を人間にとってわかりやすい形式に変換し updated_at_string とする
   * @param  object $data ページ情報オブジェクト。
   * @return object       加工されたページ情報オブジェクト。
   */
    private function processing_data($data)
    {
        $data->is_new = $this->is_new($data->created_at);
        $data->body_html = $this->parse_markdown($data->body);
        $data->body_trimmed = mb_strimwidth(strip_tags($data->body_html), 0, 100, "...");
        $data->created_at_string = $this->arrange_datetime($data->created_at);
        $data->updated_at_string = $this->arrange_datetime($data->updated_at);
        return $data;
    }
}
