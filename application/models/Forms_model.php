<?php

/**
 * Forms モデル
 */
class Forms_model extends MY_Model
{

    /**
     * 非公開のフォームをこのモデルの get の結果に含めるか
     * @var bool
     */
    public $include_private = false;

    /**
     * すべてのフォームを取得
     * @param string $period_type in_period(受付期間中), closed(受付終了), not_started(受付開始雨), null(全て) のいずれか
     * @return object[]              フォームオブジェクトの配列
     */
    public function get_forms($period_type = null)
    {
        if ($period_type === "in_period") {
            $where = "'" . date("Y-m-d H:i:s") . "'" . " BETWEEN open_at AND close_at";
            $this->db->where($where);
        } elseif ($period_type === "not_started") {
            $where = "'" . date("Y-m-d H:i:s") . "'" . " < open_at";
            $this->db->where($where);
        } elseif ($period_type === "closed") {
            $where = "'" . date("Y-m-d H:i:s") . "'" . " > close_at";
            $this->db->where($where);
        }

        if ($this->include_private !== true) {
            $this->db->where("is_public", 1);
        }

        $this->db->order_by("close_at");
        $query = $this->db->get("forms");
        $results = $query->result();
        for ($i = 0; $i < count($results); ++$i) {
            $results[$i] = $this->processing_form_data($results[$i]);
        }
        return $results;
    }

    public function get_form_by_form_id($form_id)
    {
        // フォーム情報を取得
        $this->db->where("id", $form_id);
        $form_info = $this->db->get("forms")->row();

        // 存在しないフォームの場合 false
        if (!$form_info) {
            return false;
        }

        $form_info->max_answers = (int)$form_info->max_answers;
        $form_info = $this->processing_form_data($form_info);

        // 非公開フォームだった場合 false
        if ($this->include_private !== true && $form_info->is_public !== true) {
            return false;
        }

        $select = [
            // questions
            "questions.id AS question_id",
            "questions.name AS question_name",
            "questions.description AS question_description",
            "questions.type AS question_type",
            "questions.is_required AS question_is_required",
            "questions.number_min AS question_number_min",
            "questions.number_max AS question_number_max",
            "questions.allowed_types AS question_allowed_types",
            "questions.options AS question_options",
            "questions.priority AS question_priority",
        ];
        $this->db->select(implode(",", $select), false);
        $this->db->where("questions.form_id", $form_id);
        $this->db->order_by("questions.priority");
        $query = $this->db->get("questions");
        $results = $query->result();

        // return 用の設問配列
        $questions_for_return = [];

        // 設問情報の中に、選択肢情報が入っている構造にする
        foreach ($results as $result) {
            // 設問オブジェクト
            $question_id = $result->question_id;
            if (empty($questions_for_return[$question_id])) {
                $question = new stdClass();
                $question->id = $question_id;
                $question->name = $result->question_name;
                $question->description = $result->question_description;
                $question->type = $result->question_type;
                $question->is_required = ((int)$result->question_is_required === 1);
                $question->number_min = $result->question_number_min ?? null;
                $question->number_max = $result->question_number_max ?? null;
                $question->allowed_types = $result->question_allowed_types;
                $question->priority = (int)$result->question_priority ?? null;

                // 設問
                $options = explode("\n", $result->question_options);
                $options = array_map('trim', $options);
                $options = array_filter($options, 'strlen');
                $options = array_values($options);
                $question->options = $options;

                $questions_for_return[$question_id] = $question;
            }
        }

        $return = $form_info;
        $return->questions = $questions_for_return;

        // カスタムフォームに関する情報を付加
        $this->db->where('form_id', $form_info->id);
        $return->custom_form = $this->db->get('custom_forms')->row();

        return $return;
    }

    /**
     * 指定したフォームIDのフォームの統計情報を取得する
     * @param int $form_id フォームID
     * @return object|bool          統計情報オブジェクト．フォームが見つからない時 false
     */
    public function get_statistics_by_form_id($form_id)
    {
        $return = new stdClass();

        // フォーム情報を取得
        $form = $this->get_form_by_form_id($form_id);

        // 回答者数を取得
        // (回答者数 : typeがboothなら回答ブース数と回答企画数，circleなら回答企画数を取得．同じ企画が
        // 1つのフォームに対し2つ以上の回答をしていても，回答企画数は1とカウントする)
        $this->db->select(
            "count( DISTINCT circle_id ) AS count_circle, count( DISTINCT booth_id ) AS count_booth",
            false
        );
        $this->db->where("form_id", $form_id);
        // 参加登録が受理されている企画による回答のみ取得する
        $this->db->where("EXISTS (SELECT * FROM circles WHERE circles.id = answers.circle_id AND circles.status = 'approved')", null, false);
        $result = $this->db->get("answers")->row();

        $return->form_type = $form->type;
        $return->count_circle = $result->count_circle;
        $return->count_booth = null;

        // 母数を取得する
        $return->count_all_circles = $this->circles->count_all();
        $return->count_all_booths = null;

        // 回答率を計算する
        $return->proportion_circle = $return->count_all_circles === 0
            ? 0
            : round(($return->count_circle / $return->count_all_circles) * 100, 1);
        $return->proportion_booth = null;

        if ($form->type === "booth") {
            // type が booth の場合
            $return->count_booth = $result->count_booth;
            // 母数を取得する
            $return->count_all_booths = $this->booths->count_all();
            // 回答率を計算する
            $return->proportion_booth = $return->count_all_booths === 0
                ? 0
                : round(($return->count_booth / $return->count_all_booths) * 100, 1);
        }

        return $return;
    }

    /**
     * 指定されたタイプに関するカスタムフォーム設定を取得する
     *
     * @param string $type
     * @return object
     */
    public function get_custom_form_by_type($type)
    {
        $this->db->where('type', $type);
        return $this->db->get('custom_forms')->row();
    }

    /**
     * 指定した回答IDから回答を取得する
     * @param int $answer_id 回答ID( answers.id )
     * @return object|bool            回答オブジェクト。見つからない時 false
     */
    public function get_answer_by_answer_id($answer_id)
    {
        // answers
        $this->db->where("id", $answer_id);
        $answer = $this->db->get("answers")->row();
        if (!$answer) {
            return false;
        }

        // 企画情報とブース情報も取得
        $circle = $this->circles->get_circle_info_by_circle_id($answer->circle_id);
        $booth = null;
        if (!empty($answer->circle_id)) {
            $booth = $this->booths->get_booth_info_by_booth_id($answer->booth_id);
        }

        // answer_details
        $this->db->where("answer_id", $answer_id);
        $details = $this->db->get("answer_details")->result();
        $details_for_return = [];
        foreach ($details as $detail) {
            if (empty($details_for_return[$detail->question_id])) {
                $details_for_return[$detail->question_id] = $detail->answer;
            } else {
                if (!is_array($details_for_return[$detail->question_id])) {
                    $tmp = $details_for_return[$detail->question_id];
                    $details_for_return[$detail->question_id] = [];
                    $details_for_return[$detail->question_id][] = $tmp;
                }
                $details_for_return[$detail->question_id][] = $detail->answer;
            }
        }

        $return = $answer;
        $return->circle = $circle;
        $return->booth = $booth;
        $return->answers = $details_for_return;
        return $return;
    }

    /**
     * 指定した回答IDの回答の次の回答情報を取得する(ページング用，スタッフ用)
     * @param int $answer_id 回答ID
     * @param int $form_id フォームID
     * @return object|bool            回答情報オブジェクト．存在しない場合 false
     */
    public function next_answer($answer_id, $form_id)
    {
        $this->db->select("id");
        $this->db->where("id >", $answer_id);
        $this->db->where("form_id", $form_id);
        $this->db->order_by("id", "asc");
        $this->db->limit(1);
        $query = $this->db->get("answers");
        if ($query->num_rows() === 1) {
            return $this->get_answer_by_answer_id($query->row()->id);
        } else {
            return false;
        }
    }

    /**
     * 指定した回答IDの回答の前の回答情報を取得する(ページング用，スタッフ用)
     * @param int $answer_id 回答ID
     * @param int $form_id フォームID
     * @return object|bool            回答情報オブジェクト．存在しない場合 false
     */
    public function prev_answer($answer_id, $form_id)
    {
        $this->db->select("id");
        $this->db->where("id <", $answer_id);
        $this->db->where("form_id", $form_id);
        $this->db->order_by("id", "desc");
        $this->db->limit(1);
        $query = $this->db->get("answers");
        if ($query->num_rows() === 1) {
            return $this->get_answer_by_answer_id($query->row()->id);
        } else {
            return false;
        }
    }

    /**
     * 検索条件に合致する回答リストを取得する
     * @param int $form_id フォームID
     * @param int $circle_id 企画ID
     * @param int $booth_id ブースID
     * @return array            リスト配列
     */
    public function get_answers($form_id = null, $circle_id = null, $booth_id = null)
    {
        if (!empty($form_id)) {
            $this->db->where("form_id", $form_id);
        }
        if (!empty($circle_id)) {
            $this->db->where("circle_id", $circle_id);
        }
        if (!empty($booth_id)) {
            $this->db->where("booth_id", $booth_id);
        }

        // 参加登録が提出済の企画による回答のみ取得する
        $this->db->where("EXISTS (SELECT * FROM circles WHERE circles.id = answers.circle_id AND circles.submitted_at IS NOT NULL)", null, false);

        $query = $this->db->get("answers");

        // TODO: N+1問題の解決
        $return = [];
        foreach ($query->result() as $answer) {
            $item = $this->get_answer_by_answer_id($answer->id);
            $return[] = $item;
        }
        return $return;
    }

    /**
     * 回答を追加する
     * TODO: add_answer と update_answer の統合
     * @param array $answers 回答情報
     * @param string $type circle か booth か
     * @param int $form_id フォームID
     * @param int $circle_id 企画ID
     * @param int $booth_id ブースID
     * @param int|bool            insertした回答の回答ID．insertに失敗した場合 false
     */
    public function add_answer($answers, $type, $form_id, $circle_id, $booth_id)
    {
        $now = date("Y-m-d H:i:s");

        $this->db->trans_start();

        // answers に insert
        $this->db->set("form_id", $form_id);
        $this->db->set("created_at", $now);
        $this->db->set("updated_at", $now);
        $this->db->set("circle_id", $circle_id);
        if ($type === "booth") {
            $this->db->set("booth_id", $booth_id);
        }
        $this->db->insert("answers");

        // 回答ID
        $answer_id = $this->db->insert_id();

        // answer_details に insert
        foreach ($answers as $question_id => $answer) {
            if (is_array($answer)) {
                foreach ($answer as $option) {
                    $this->db->set("answer_id", $answer_id);
                    $this->db->set("question_id", $question_id);
                    $this->db->set("answer", $option);
                    $this->db->insert("answer_details");
                }
            } else {
                $this->db->set("answer_id", $answer_id);
                $this->db->set("question_id", $question_id);
                $this->db->set("answer", $answer);
                $this->db->insert("answer_details");
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        return $answer_id;
    }

    /**
     * 回答を更新する
     * @param array $answers 回答情報
     * @param int $answer_id 更新する回答の回答ID
     */
    public function update_answer($answers, $answer_id)
    {
        $now = date("Y-m-d H:i:s");

        $this->db->trans_start();

        // answers を update
        $this->db->where("id", $answer_id);
        $this->db->set("updated_at", $now);
        $this->db->update("answers");

        // answer_details を update
        foreach ($answers as $question_id => $answer) {
            $this->db->where("answer_id", $answer_id);
            $this->db->where("question_id", $question_id);
            $this->db->delete("answer_details");
            if (is_array($answer)) {
                foreach ($answer as $option) {
                    $this->db->set("answer_id", $answer_id);
                    $this->db->set("question_id", $question_id);
                    $this->db->set("answer", $option);
                    $this->db->insert("answer_details");
                }
            } else {
                $this->db->set("answer_id", $answer_id);
                $this->db->set("question_id", $question_id);
                $this->db->set("answer", $answer);
                $this->db->insert("answer_details");
            }
        }

        $this->db->trans_complete();

        if ($this->db->trans_status() === false) {
            return false;
        }

        return true;
    }

    private function processing_form_data($data)
    {
        $data->description_html = $this->parse_markdown($data->description);
        $data->open_at_string = $this->arrange_datetime($data->open_at, true);
        $data->close_at_string = $this->arrange_datetime($data->close_at, true);

        $data->is_public = ((int)$data->is_public === 1);
        // ↑ is_public === 1 なら true を代入

        // 受付期間内か
        $data->is_in_period = true;
        $data->now_period = "open";
        $now = new DateTime();
        $start_at_obj = new DateTime($data->open_at);
        $close_at_obj = new DateTime($data->close_at);
        if ($now < $start_at_obj) {
            $data->is_in_period = false;
            $data->now_period = "not_started";
        }
        if ($close_at_obj < $now) {
            $data->is_in_period = false;
            $data->now_period = "ended";
        }

        return $data;
    }
}
