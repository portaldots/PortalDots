<?php
require_once __DIR__. '/Home_base_controller.php';

/**
 * home/Applications コントローラ
 *
 * 申請ページ
 */
// TODO: まだまだリファクタリングできるはず
class Applications extends Home_base_controller
{
    public function index()
    {
        $vars = [];
        $vars["main_page_type"] = "applications";
        $vars["xs_navbar_title"] = "申請";

        $vars["circle_info"] = $this->circles->get_circle_info_by_user_id($this->_get_login_user()->id);

        if (count($vars["circle_info"]) === 1) {
          // アクセスできる団体が１つしかない場合，その団体の申請一覧ページに直接アクセスする
            codeigniter_redirect("home/applications/circles/". $vars["circle_info"][0]->id);
        } elseif (count($vars["circle_info"]) === 0) {
          // アクセスできる団体が１つもない場合，エラーを表示する
            $this->_error("エラー", "どの団体にも所属していないため、申請ページは表示できません。", 403);
        }

      // 団体セレクタ
        $this->_render('home/applications_selector', $vars);
    }

    public function circles($circleId, $type = null)
    {
        $vars = [];
        $vars["main_page_type"] = "applications";
        $vars["xs_navbar_title"] = "申請";

        $vars["circle_info"] = $this->circles->get_circle_info_by_user_id($this->_get_login_user()->id);

        // 該当する団体情報の取得
        $vars["circle"] = $this->circles->get_circle_info_by_circle_id($circleId);

        // アクセス権がない場合はエラー
        if ($this->circles->can_edit($circleId, $this->_get_login_user()->id) === false) {
            $this->_error("エラー", "このページにアクセスする権限がありません。", 403);
        }

        // 該当する団体による回答一覧の取得
        $answers = $this->forms->get_answers(null, $circleId);

        // キーがフォームIDとなっている回答一覧配列を作成
        $vars["answers"] = [];
        foreach ($answers as $answer) {
            $vars["answers"][ $answer->form_id ] = $answer;
        }

        if (empty($type)) {
            // 受付期間中の申請の取得
            $vars["type"] = "open";
            $vars["forms"] = $this->forms->get_forms("in_period");
            // TODO: get_answers
        } elseif ($type === "closed") {
          // 受付終了の申請
            $vars["type"] = "closed";
            $vars["forms"] = $this->forms->get_forms("closed");
        } elseif ($type === "all") {
            // すべて
            $vars["type"] = "all";
            $vars["forms"] = $this->forms->get_forms();
        } else {
            $this->_error("エラー", "ページが見つかりません", 404);
        }

        $this->_render('home/applications_home', $vars);
    }
}
