<?php

/**
 * Documents モデル
 */

// TODO: 配布資料に紐付けられたスケジュールを取得する処理において発生してしまった N+1 問題の修正

class Documents_model extends MY_Model
{

  /**
   * 公開されている配布資料情報を全て取得する
   * @return object[] 配布資料情報全件がオブジェクトとなっている配列
   */
    public function get_all_public()
    {
        $this->db->order_by("created_at", "desc");
        $this->db->where('is_public', 1);
        $query = $this->db->get("documents");
        $results = $query->result();
        for ($i = 0; $i < count($results); ++$i) {
            $results[$i]->is_new = $this->is_new($results[$i]->created_at);
            $results[$i]->created_at_string = $this->arrange_datetime($results[$i]->created_at);
            $results[$i]->updated_at_string = $this->arrange_datetime($results[$i]->updated_at);
            if (!empty($results[$i]->schedule_id)) {
                $results[$i]->schedule_info = $this->schedules->get_schedule_by_schedule_id($results[$i]->schedule_id);
            } else {
                $results[$i]->schedule_info = null;
            }
        }
        return $results;
    }

  /**
   * 公開されている配布資料から検索する
   * @param string    $query 検索クエリ
   * @return object[]        該当する配布資料情報がオブジェクトとなっている配列
   */
    public function search_public($query)
    {
        $this->db->order_by("created_at", "desc");
        $this->db->where('is_public', 1);
        $this->db->like('name', $query);
        $this->db->or_like('description', $query);
        $query = $this->db->get("documents");
        $results = $query->result();
        for ($i = 0; $i < count($results); ++$i) {
            $results[$i]->is_new = $this->is_new($results[$i]->created_at);
            $results[$i]->created_at_string = $this->arrange_datetime($results[$i]->created_at);
            $results[$i]->updated_at_string = $this->arrange_datetime($results[$i]->updated_at);
            if (!empty($results[$i]->schedule_id)) {
                $results[$i]->schedule_info = $this->schedules->get_schedule_by_schedule_id($results[$i]->schedule_id);
            } else {
                $results[$i]->schedule_info = null;
            }
        }
        return $results;
    }

  /**
   * 公開されている配布資料の件数を取得する
   * @return int 件数
   */
    public function count_all_public()
    {
        $this->db->where('is_public', 1);
        return $this->db->count_all_results("documents");
    }

  /**
   * 公開されている配布資料情報を指定件数だけ取得する
   * @param  int $count  取得したい件数
   * @param  int $offset オフセット
   * @return object[]    配布資料情報全件がオブジェクトとなっている配列
   */
    public function get_list_public($count, $offset = 0)
    {
        $this->db->order_by("created_at", "desc");
        $this->db->where('is_public', 1);
        $this->db->limit($count, $offset);
        $query = $this->db->get("documents");
        $results = $query->result();
        for ($i = 0; $i < count($results); ++$i) {
            $results[$i]->is_new = $this->is_new($results[$i]->created_at);
            $results[$i]->created_at_string = $this->arrange_datetime($results[$i]->created_at);
            $results[$i]->updated_at_string = $this->arrange_datetime($results[$i]->updated_at);
            if (!empty($results[$i]->schedule_id)) {
                $results[$i]->schedule_info = $this->schedules->get_schedule_by_schedule_id($results[$i]->schedule_id);
            } else {
                $results[$i]->schedule_info = null;
            }
        }
        return $results;
    }

  /**
   * 配布資料IDから配布資料情報を取得する
   * 公開されている配布資料のみ取得可能
   * @param  int         $document_id 配布資料ID
   * @return object|bool              配布資料情報オブジェクト。存在しない場合はfalse
   */
    public function get_public_document_by_document_id($document_id)
    {
        $this->db->where("id", $document_id);
        $this->db->where("is_public", 1);
        $query = $this->db->get("documents");
        if ($query->num_rows() === 1) {
            $result = $query->result()[0];
            $result->is_new = $this->is_new($result->created_at);
            $result->created_at_string = $this->arrange_datetime($result->created_at);
            $result->updated_at_string = $this->arrange_datetime($result->updated_at);
            if (!empty($result->schedule_id)) {
                $result->schedule_info = $this->schedules->get_schedule_by_schedule_id($result->schedule_id);
            } else {
                $result->schedule_info = null;
            }
            return $result;
        } else {
            return false;
        }
    }

  /**
   * 公開されている配布資料情報をスケジュールIDから全て取得する
   * @param  int      $schedule_id スケジュールID
   * @return object[]              配布資料情報がオブジェクトとなっている配列
   */
    public function get_public_documents_by_schedule_id($schedule_id)
    {
        $this->db->order_by("created_at", "desc");
        $this->db->where('schedule_id', $schedule_id);
        $this->db->where('is_public', 1);
        $query = $this->db->get("documents");
        $results = $query->result();
        for ($i = 0; $i < count($results); ++$i) {
            $results[$i]->is_new = $this->is_new($results[$i]->created_at);
            $results[$i]->created_at_string = $this->arrange_datetime($results[$i]->created_at);
            $results[$i]->updated_at_string = $this->arrange_datetime($results[$i]->updated_at);
          // スケジュール情報は，他のメソッドと同じように代入しない予定
        }
        return $results;
    }

  /**
   * 配布資料IDから配布資料情報を取得する
   * @param  int         $document_id 配布資料ID
   * @return object|bool              配布資料情報オブジェクト。存在しない場合はfalse
   */
    public function get_document_by_document_id($document_id)
    {
        $this->db->where("id", $document_id);
        $query = $this->db->get("documents");
        if ($query->num_rows() === 1) {
            $result = $query->result()[0];
            $result->created_at_string = $this->arrange_datetime($result->created_at);
            $result->updated_at_string = $this->arrange_datetime($result->updated_at);
            if (!empty($result->schedule_id)) {
                $result->schedule_info = $this->schedules->get_schedule_by_schedule_id($result->schedule_id);
            } else {
                $result->schedule_info = null;
            }
            return $result;
        } else {
            return false;
        }
    }
}
