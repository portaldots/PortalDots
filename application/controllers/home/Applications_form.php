<?php
require_once __DIR__ . '/Home_base_controller.php';

/**
 * home/Applications_form コントローラ
 *
 * 申請フォーム表示
 */
class Applications_form extends Home_base_controller
{
    /**
     * _validate_form_upload() 用アップロード設定保持クラス
     */
    private $form_upload_config = [];

    /**
     * アップロードされたデータについての情報
     * @link http://codeigniter.jp/user_guide/3/libraries/file_uploading.html#CI_Upload::data
     */
    private $form_upload_data;

    /**
     * 申請フォーム表示
     *
     * URL : フォームID/団体ID/タイプ(/ブースID)
     * ( タイプ : new か 回答ID )
     * config/routes.php も参照のこと
     */
    public function index($formId, $circleId, $type, $boothId = null)
    {
        $this->load->library("form_validation");

        $vars = [];
        $vars["main_page_type"] = "applications";

        $answer_id = 0;

        // POST結果
        if (isset($_SESSION["post_result"])) {
            $vars["post_result"] = $_SESSION["post_result"];
        } else {
            $vars["post_result"] = null;
        }

        // DB保存用の回答配列
        $answers = [];

        // フォーム情報を取得
        $vars["form"] = $this->forms->get_form_by_form_id($formId);
        if ($vars["form"] === false) {
            $this->_error("申請フォームエラー", "このフォームは存在しません。", 404);
        }

        $vars["xs_navbar_title"] = $vars["form"]->name;

        // form の type が booth にもかかわらず $booth_id が提供されていない場合、エラー
        // TODO: エラーではなく，ブース選択画面を表示させたほうが親切
        if ($vars["form"]->type === "booth" && empty($boothId)) {
            $this->_error("申請フォームエラー", "ブースが指定されていません。");
        }

        // アクセス権がない場合はエラー
        if ($this->circles->can_edit($circleId, $this->_get_login_user()->id) === false
            || ($vars["form"]->type === "booth" &&
                $this->booths->can_edit($boothId, $this->_get_login_user()->id) === false)) {
            $this->_error("申請フォームエラー", "このページにアクセスする権限がありません。", 403);
        }

        // サークル・ブース情報を取得
        $vars["circle"] = $this->circles->get_circle_info_by_circle_id($circleId);
        if ($vars["form"]->type === "booth" && !empty($boothId)) {
            $vars["booth"] = $this->booths->get_booth_info_by_booth_id($boothId);
            // ブース情報が見つからない場合はエラー
            if ($vars["booth"] === false) {
                $this->_error("申請フォームエラー", "該当するブース情報が見つかりません。", 404);
            }
        }

        // $type で分ける
        $answers_on_db = [];
        if ($type === "new") {
            // 新規申請の作成
            $vars["type"] = "new";

            $answer_list = $this->forms->get_answers($formId, $circleId, $boothId);

            // もし，max_answers が 1で，かつ，すでに申請されている場合， update へリダイレクト
            if ((int)$vars["form"]->max_answers === 1 && count($answer_list) > 0) {
                $url = "home/applications/{$circleId}";
                if ($vars["form"]->type === "booth") {
                    $url .= "/b:{$boothId}";
                }
                $url .= "/forms/{$formId}/" . $answer_list[0]->id;
                codeigniter_redirect($url);
            }

            // もし， max_answers を超える数の申請をしようとしている場合，エラー表示
            if (count($answer_list) >= (int)$vars["form"]->max_answers) {
                $this->_error(
                    "申請フォームエラー",
                    "この申請の申請上限数に達しました(上限数 : " . (int)$vars["form"]->max_answers . " )。" .
                    "これ以上、この申請を提出することはできません。"
                );
            }
        } else {
            // すでに行った申請の確認・変更
            $vars["type"] = "update";

            // $type は 回答ID
            $answer_id = $type;

            // 回答情報を取得
            $answer_info = $this->forms->get_answer_by_answer_id($answer_id);

            // 存在しない回答の時エラー
            if ($answer_info === false) {
                $this->_error("申請フォームエラー", "該当する回答情報が見つかりません。", 404);
            }

            // 回答情報のフォームIDと現在表示中のフォームIDが一致しない場合エラー
            if ((int)$answer_info->form_id !== (int)$formId) {
                $this->_error("申請フォームエラー", "該当する回答情報が見つかりません。", 404);
            }

            // URLで渡されたブースIDとフォームIDが，回答のものと一致しない場合，エラー
            if ((int)$circleId !== (int)$answer_info->circle_id ||
                (!empty($boothId) && (int)$boothId !== (int)$answer_info->booth_id)) {
                $this->_error("申請フォームエラー", "該当する回答情報が見つかりません。", 404);
            }

            $answers = $answers_on_db = $answer_info->answers;
            /*
            $answers : この先，書き換えOK
            $answers_on_db : この先，書き換えNG( DB上のデータ )
             */
        }

        // POST 時
        if ($this->input->method() === "post") {
            $this->_post_index($vars, $answers_on_db, $type, $formId, $boothId, $circleId, $answer_id, $answers);
        } // end if post

        $vars["answers"] = $answers;
        $this->_render('home/applications_form', $vars);
    }

    private function _post_index($vars, $answers_on_db, $type, $formId, $boothId, $circleId, $answer_id, &$answers)
    {
        $answers = [];

        // 申請完了メール用配列
        //  キーを設問の name とし，値を，ユーザーの回答(日本語)とする
        //  チェックボックスに限り，値は配列とし，キーは連番とする
        $answers_for_email = [];

        // 受付期間外の場合エラー
        if ($vars["form"]->is_in_period !== true) {
            $this->_error("申請フォームエラー", "このフォームは受付期間外です。");
        }

        // 各設問ごとにバリデーション実行
        foreach ($vars["form"]->questions as $question) {
            $rules = [];
            // フォームの name
            $name = "answers[" . $question->id . "]";
            if ($question->type === "checkbox") {
                $name .= "[]";
            }

            $answers_for_email[$question->name] = $this->input->post($name);

            // required 設定の有無
            // ( upload は独自の方法で required 検証するため設定無視 )
            if ($question->type !== "upload" && $question->is_required === true) {
                $rules[] = "required";
            }

            // 共通ルール
            $rules[] = "trim";

            // (number)数値範囲
            if ($question->type === "number") {
                // 数字か
                $rules[] = "numeric";
                // number_min(最小値設定)
                if (!empty($question->number_min)) {
                    $rules[] = "greater_than_equal_to[" . $question->number_min . "]";
                }
                // number_max(最大値設定)
                if (!empty($question->number_max)) {
                    $rules[] = "less_than_equal_to[" . $question->number_max . "]";
                }
            }

            // (radio/select/checkbox)選択肢として妥当かどうかの検証
            if ($question->type === "radio" || $question->type === "select" || $question->type === "checkbox") {
                // 選択肢として妥当かどうかを検証
                $rule = "in_list[";
                foreach ($question->options as $option) {
                    $rule .= $option->id . ",";
                }
                $rule .= "]";
                $rules[] = $rule;

                // メール送信用
                if ($question->type === "checkbox") {
                    $answers_for_email[$question->name] = [];
                    if (is_iterable($this->input->post($name))) {
                        foreach ($this->input->post($name) as $value) {
                            $answers_for_email[$question->name][] = $question->options[$value]->value;
                        }
                    }
                } else {
                    $value = $this->input->post($name);
                    $answers_for_email[$question->name] = $question->options[$value]->value;
                }
            }

            // (upload)検証とアップロード処理
            if ($question->type === "upload") {
                if ($vars["type"] === "new" ||
                    ($vars["type"] === "update" &&
                        (!empty($_FILES["answers"]["name"][$question->id])
                            && $this->input->post("answers[" . $question->id . "]") !== "__delete__"))) {
                    // アップロードされたとき

                    $config_fields = ["is_required", "allowed_types"];
                    // is_required は，ファイルアップロードクラスの $config で必要なものではないが，
                    // 独自に使用する
                    foreach ($config_fields as $field) {
                        // $question->$field の $filed に， allowed_types や is_required などが入る
                        $this->form_upload_config[$question->id][$field] = $question->$field;
                    }
                    $this->form_upload_config[$question->id]['max_size'] = $question->number_max;

                    // アップロードフィールドの設問IDや名前を指定する
                    // ( ファイルアップロードクラスの $config で必要なものではない )
                    $this->form_upload_config[$question->id]["question_id"] = $question->id;
                    $this->form_upload_config[$question->id]["name"] = $name;

                    $rules[] = "callback__validate_form_upload";

                    // メール送信用
                    // ( 申請にエラーがなかった場合にのみメールが送信されるため，アップロードが成功した前提の文言となっている )
                    if (!empty($_FILES["answers"]["name"][$question->id])) {
                        $answers_for_email[$question->name] = "(アップロードしました)";
                    } else {
                        $answers_for_email[$question->name] = "(未アップロード)";
                    }
                } else {
                    // update の場合で，削除の場合か，何も送信されなかった場合か
                    if ($this->input->post("answers[" . $question->id . "]") === "__delete__" &&
                        $question->is_required === false) {
                        // DB上のデータを削除する
                        $answers[$question->id] = null;

                        // メール送信用
                        $answers_for_email[$question->name] = "(アップロードしたファイルを削除しました)";
                    } else {
                        // 現状，DBにあるデータを，そのまま維持する
                        $answers[$question->id] = $answers_on_db[$question->id];

                        // メール送信用
                        $answers_for_email[$question->name] = "";
                    }
                }
            }

            // (text/textarea)文字数制限
            if ($question->type === "text" || $question->type === "textarea") {
                // number_min(最小文字数設定)
                if (!empty($question->number_min)) {
                    $rules[] = "min_length[" . $question->number_min . "]";
                }
                // number_max(最大文字数設定)
                if (!empty($question->number_max)) {
                    $rules[] = "max_length[" . $question->number_max . "]";
                }
            }

            // バリデーションルールを設定する
            $this->form_validation->set_rules($name, $question->name, $rules);

            // DB保存用のデータ
            if ($question->type !== "upload") {
                $answers[$question->id] = $this->input->post("answers[" . $question->id . "]");
            }

            // メール送信用の値が empty の場合，ハイフンにする
            if (empty($answers_for_email[$question->name])) {
                $answers_for_email[$question->name] = "-";
            }
        }

        // バリデーション実行
        if ($this->form_validation->run()) {
            // バリデーション成功

            // アップロードしたファイルの情報を格納する
            $answers += $this->form_upload_data ?? [];

            // 失敗時のエラーメッセージ
            $error_msg = "申請の送信に失敗しました。恐れ入りますが、再度の送信をお試しください。\n";
            $error_msg .= "万が一、何度試してもこのエラーが表示される場合、手動で対応させていただきますので";
            $error_msg .= " " . PORTAL_CONTACT_EMAIL . " ";
            $error_msg .= "宛に、申請フォームの内容を記載した上でメールを送信してください。";

            if ($type === "new") {
                $answer_id = $this->forms->add_answer(
                    $answers,
                    $vars["form"]->type,
                    $formId,
                    $circleId,
                    $boothId
                );
                if ($answer_id === false) {
                    // new 失敗
                    $this->_error("申請フォームエラー", $error_msg);
                }
            } else {
                if (!$this->forms->update_answer($answers, $answer_id)) {
                    // update 失敗
                    $this->_error("申請フォームエラー", $error_msg);
                }
            }

            // リダイレクト先のURL
            $url = "home/applications/{$circleId}";
            if ($vars["form"]->type === "booth") {
                $url .= "/b:{$boothId}";
            }
            $url .= "/forms/{$formId}/{$answer_id}";

            // 完了メールを送信
            $vars_email = [];
            $vars_email["name_to"] = $this->_get_login_user()->name_family . " " .
                $this->_get_login_user()->name_given;
            $vars_email["form_name"] = $vars["form"]->name;
            $vars_email["type"] = $type === "new" ? "新規作成" : "更新";
            $vars_email["update_form_url"] = base_url($url);
            $vars_email["circle"] = $vars["circle"];
            if (!empty($vars["booth"])) {
                $vars_email["booth"] = $vars["booth"];
            }
            $vars_email["datetime"] = date("Y/m/d H:i:s");
            $vars_email["answers"] = $answers_for_email;

            // 回答者に送信するメール
            $this->_send_email(
                $this->_get_login_user()->email,
                "申請「" . $vars_email["form_name"] . "」を承りました",
                "email/applications_form_sent",
                $vars_email
            );

            // フォーム管理者に送信するメール
            $manager = $this->users->get_user_by_user_id($vars["form"]->created_by);
            $this->_send_email(
                $manager->email,
                "[スタッフ用控え]申請「" . $vars_email["form_name"] . "」を承りました",
                "email/applications_form_sent",
                $vars_email
            );

            // リダイレクト
            $this->session->set_flashdata("post_result", true);
            codeigniter_redirect($url);
        }
    }

    /**
     * 申請フォームファイル送信用フォームバリデーションコールバック関数
     */
    public function _validate_form_upload()
    {
        // アップロード設定
        $config = array_shift($this->form_upload_config);
        // ファイルアップロードフィールドの設問IDと名前
        $question_id = $config["question_id"];
        // required 設定
        $is_required = $config["is_required"];
        if ($is_required === true && empty($_FILES["answers"]["name"][$question_id])) {
            $this->form_validation->set_message("_validate_form_upload", "{field}欄は必須です");
            return false;
        }
        if ($is_required === false && empty($_FILES["answers"]["name"][$question_id])) {
            $this->form_upload_data[$question_id] = null;
            return true;
        }
        // ファイルアップロードクラスの動作に支障がないよう，以上のデータは unset しておく
        unset($config["name"]);
        unset($config["is_required"]);
        // パスなどのアップロード設定
        $config["upload_path"] = RP_UPLOAD_DIR . "/form_file";
        $config["encrypt_name"] = true;
        // ファイルアップロードクラスを load する
        $this->load->library("upload", $config);
        // アップロードを実行する
        // https://gist.github.com/zitoloco/1558423 を参考にしたトリック
        $_FILES["form_file"]["name"] = $_FILES["answers"]["name"][$question_id];
        $_FILES["form_file"]["type"] = $_FILES["answers"]["type"][$question_id];
        $_FILES["form_file"]["tmp_name"] = $_FILES["answers"]["tmp_name"][$question_id];
        $_FILES["form_file"]["error"] = $_FILES["answers"]["error"][$question_id];
        $_FILES["form_file"]["size"] = $_FILES["answers"]["size"][$question_id];
        if (!$this->upload->do_upload("form_file")) {
            // アップロード失敗時
            $errors = str_replace("</p><p>", "/////", $this->upload->display_errors());
            $errors = str_replace(["<p>", "</p>"], "", $errors);
            $errors = explode("/////", $errors);
            $display_error = array_pop($errors);
            $this->form_validation->set_message(
                "_validate_form_upload",
                $display_error . " : " . $_FILES["form_file"]["name"]
            );
            return false;
        }
        // アップロードしたデータのファイル名を格納
        $this->form_upload_data[$question_id] = ($this->upload->data())["file_name"];
        return true;
    }
}
