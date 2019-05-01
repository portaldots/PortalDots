<?php

/**
 * Schedules モデル
 */
class Schedules_model extends MY_Model
{

  /**
   * 登録されているスケジュールを全て取得する
   * @return object[] スケジュール全件がオブジェクトとなっている配列
   */
    public function get_all()
    {
        $query = $this->db->get("schedules");
        $results = $query->result();
        for ($i = 0; $i < count($results); ++$i) {
            $results[$i]->description_html = $this->parse_markdown($results[$i]->description);
            $results[$i]->start_at_string = $this->arrange_datetime($results[$i]->start_at);
        }
        return $results;
    }

  /**
   * 指定された日時から一番近い次の予定を取得する
   * @param  string $datetime (省略可)日付として認識される文字列。省略した場合は、現在日時が使用される。
   * @return object           次の予定
   */
    public function get_next_event($datetime = null)
    {
        if (empty($datetime)) {
            $datetime = date('Y-m-d H:i:s');
        }
        $this->db->where('start_at >=', $datetime);
        $this->db->order_by('start_at', 'ASC');
        $this->db->limit(1);
        $query = $this->db->get("schedules");
        if ($query->num_rows() === 1) {
            $result = $query->result()[0];
            $result->description_html = $this->parse_markdown($result->description);
            $result->start_at_string = $this->arrange_datetime($result->start_at);
            return $result;
        } else {
            return new StdClass();
        }
    }

  /**
   * 指定された日時以降の全ての予定を取得する
   * @param  string   $datetime (省略可)日付として認識される文字列。省略した場合は、現在日時が使用される。
   * @return object[]           予定。該当なしの場合は、空の配列。
   */
    public function get_after($datetime)
    {
        if (empty($datetime)) {
            $datetime = date('Y-m-d H:i:s');
        }
        $this->db->where('start_at >=', $datetime);
        $this->db->order_by('start_at', 'ASC');
        $query = $this->db->get("schedules");
        if ($query->num_rows() > 0) {
            $results = $query->result();
            for ($i = 0; $i < count($results); ++$i) {
                $results[$i]->description_html = $this->parse_markdown($results[$i]->description);
                $results[$i]->start_at_string = $this->arrange_datetime($results[$i]->start_at);
            }
            return $results;
        } else {
            return [];
        }
    }

  /**
   * 指定した月と年の予定を取得する
   * @param  int $month 月
   * @param  int $year  年
   * @return object[]   予定。該当なしの場合は、空の配列。
   */
    public function get_month($month, $year = null)
    {
        if (empty($year)) {
            $year = date('Y');
        }
        $month = str_pad($month, 2, "0", STR_PAD_LEFT);
        $this->db->where("DATE_FORMAT(start_at, '%Y%m') =", "{$year}{$month}");
        $this->db->order_by('start_at', 'ASC');
        $query = $this->db->get("schedules");
        if ($query->num_rows() > 0) {
            $results = $query->result();
            for ($i = 0; $i < count($results); ++$i) {
                $results[$i]->description_html = $this->parse_markdown($results[$i]->description);
                $results[$i]->start_at_string = $this->arrange_datetime($results[$i]->start_at);
            }
            return $results;
        } else {
            return [];
        }
    }

  /**
   * 指定した日と月と年の予定を取得する
   * @param  int $day   日
   * @param  int $month 月
   * @param  int $year  年
   * @return object[]   予定。該当なしの場合は、空の配列。
   */
    public function get_day($day, $month = null, $year = null)
    {
        if (empty($month)) {
            $year = date('m');
        }
        if (empty($year)) {
            $year = date('Y');
        }
        $month = str_pad($month, 2, "0", STR_PAD_LEFT);
        $day   = str_pad($day, 2, "0", STR_PAD_LEFT);
        $this->db->where("DATE_FORMAT(start_at, '%Y%m%d') =", "{$year}{$month}{$day}");
        $this->db->order_by('start_at', 'ASC');
        $query = $this->db->get("schedules");
        if ($query->num_rows() > 0) {
            $results = $query->result();
            for ($i = 0; $i < count($results); ++$i) {
                $results[$i]->description_html = $this->parse_markdown($results[$i]->description);
                $results[$i]->start_at_string = $this->arrange_datetime($results[$i]->start_at);
            }
            return $results;
        } else {
            return [];
        }
    }

  /**
   * 指定したIDのスケジュールを取得する
   * @param  int         $id  ID
   * @return object|bool      予定。該当なしの場合は false
   */
    public function get_schedule_by_schedule_id($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->get("schedules");
        if ($query->num_rows() === 1) {
            $result = $query->result()[0];
            $result->description_html = $this->parse_markdown($result->description);
            $result->start_at_string = $this->arrange_datetime($result->start_at);
            return $result;
        } else {
            return [];
        }
    }
}
