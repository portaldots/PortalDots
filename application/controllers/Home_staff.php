<?php

use Carbon\CarbonImmutable;

/**
 * ホーム(スタッフ用)コントローラ
 */
class Home_staff extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->_require_login();
        $this->_staff_only();
        $this->_init_crud();
    }

    /**
     * Grocry CRUD の初期化をする
     */
    private function _init_crud()
    {
        // カラム名の日本語化
        $this->grocery_crud->display_as('student_id', '学籍番号');
        $this->grocery_crud->display_as('title', 'タイトル');
        $this->grocery_crud->display_as('category_id', 'カテゴリーID');
        $this->grocery_crud->display_as('body', '本文');
        $this->grocery_crud->display_as('booth_id', 'ブースID');
        $this->grocery_crud->display_as('place_id', '場所');
        $this->grocery_crud->display_as('circle_id', '企画');
        $this->grocery_crud->display_as('genre', 'ジャンル');
        $this->grocery_crud->display_as('image_filename', '画像');
        $this->grocery_crud->display_as('description', '紹介');
        $this->grocery_crud->display_as('url', 'URL');
        $this->grocery_crud->display_as('twitter_id', 'Twitter ID');
        $this->grocery_crud->display_as('facebook_url', 'Facebook URL');
        $this->grocery_crud->display_as('google_plus_url', 'Google+ URL');
        $this->grocery_crud->display_as('is_sell_foods', '飲食物販売');
        $this->grocery_crud->display_as('is_sell_things', '成果物販売');
        $this->grocery_crud->display_as('is_exhibition', '展示');
        $this->grocery_crud->display_as('is_performance', '実演');
        $this->grocery_crud->display_as('is_public', '公開');
        $this->grocery_crud->display_as('is_important', '重要');
        $this->grocery_crud->display_as('is_locked', '編集禁止');
        $this->grocery_crud->display_as('created_at', '作成日時');
        $this->grocery_crud->display_as('updated_at', '最終変更日時');
        $this->grocery_crud->display_as('created_by', '作成者');
        $this->grocery_crud->display_as('updated_by', '最終変更者');
        $this->grocery_crud->display_as('name_family', '姓');
        $this->grocery_crud->display_as('name_family_yomi', '姓(よみ)');
        $this->grocery_crud->display_as('name_given', '名');
        $this->grocery_crud->display_as('name_given_yomi', '名(よみ)');
        $this->grocery_crud->display_as('email', '連絡先メールアドレス');
        $this->grocery_crud->display_as('tel', '電話番号');
        $this->grocery_crud->display_as('verified_univemail', '大学ﾒｱﾄﾞ認証済');
        $this->grocery_crud->display_as('verified_email', '連絡先ﾒｱﾄﾞ認証済');
        $this->grocery_crud->display_as('leader', '責任者');
        $this->grocery_crud->display_as('members', '所属者');
        $this->grocery_crud->display_as('is_staff', 'スタッフ');
        $this->grocery_crud->display_as('is_admin', '管理者');
        $this->grocery_crud->display_as('notes', 'ｽﾀｯﾌ用ﾒﾓ');
        $this->grocery_crud->display_as('last_accessed_at', '最終アクセス');

        // id順に表示する
        $this->grocery_crud->order_by('id', 'asc');

        // textフィールドを表示する際の改行自動追加
        $this->grocery_crud->callback_column('name', array($this, '_crud_full_text'));
        $this->grocery_crud->callback_column('genre', array($this, '_crud_full_text'));
        $this->grocery_crud->callback_column('description', array($this, '_crud_full_text'));
        $this->grocery_crud->callback_column('notes', array($this, '_crud_full_text'));

        // 更新日時や更新者を自動設定する
        $this->grocery_crud->callback_before_insert(array($this,'_crud_before_insert'));
        $this->grocery_crud->callback_before_update(array($this,'_crud_before_update'));
    }

    /**
     * description を適切に改行して表示させるための Grocery CRUD コールバック関数
     */
    public function _crud_full_text($value, $row)
    {
        return $value = $this->_mb_wordwrap($value, 30, "<br>");
    }

    /**
     * Update 前に実行する処理のための Grocery CRUD コールバック関数
     */
    public function _crud_before_update($post_array)
    {
        /*
         * Grocery CRUD の fields または edit_fields に指定されていないカラムの情報をここで設定
         * することはできません。つまり、以下で設定しているカラムが存在しない場合は、単に無視されるだけなので
         * 難しく考える必要がありません。その代わり、change_field_type で、第１引数に、フォームで
         * 非表示にしたいカラム名を指定し、第２引数に invisible を指定すれば OK です。
         * See Also : https://www.grocerycrud.com/documentation/options_functions/callback_before_insert
         */
        $post_array['updated_at'] = date('Y-m-d H:i:s');
        $post_array['updated_by'] = $this->_get_login_user()->id;

        return $post_array;
    }

    /**
     * Insert 前に実行する処理のための Grocery CRUD コールバック関数
     */
    public function _crud_before_insert($post_array)
    {
        $post_array = $this->_crud_before_update($post_array);

        $post_array['created_at'] = date('Y-m-d H:i:s');
        $post_array['created_by'] = $this->_get_login_user()->id;

        return $post_array;
    }

    /**
     * 申請管理ページ
     */
    public function applications()
    {
        $vars = [];
        $vars["page_title"] = "申請管理";
        $vars["main_page_type"] = "applications";

        $this->forms->include_private = true;

        if ($this->uri->segment(3) === "read") {
            // 個別表示の場合，Grocery CRUD を使用しない
            $form_id = $this->uri->segment(4);
            $mode = $this->uri->segment(5) ?? null;
            return $this->_applications_read($form_id, $mode);
        }

        if ($this->uri->segment(3) === "preview") {
            // フォームプレビュー
            $form_id = $this->uri->segment(4);
            $vars["form"] = $this->forms->get_form_by_form_id($form_id);
            $vars["is_preview"] = true;
            if ($vars["form"] === false) {
                $this->_error("申請フォームエラー", "このフォームは存在しません。", 404);
            }
            $this->_render('home_staff/applications_form', $vars);
            return;
        }

        $this->grocery_crud->set_table('forms');

        // カスタムフォームは一覧に表示しない
        $this->grocery_crud->where("NOT EXISTS (SELECT * FROM {$this->db->dbprefix}custom_forms WHERE form_id = {$this->db->dbprefix}forms.id)", null, false);

        $this->grocery_crud->set_subject('フォーム');
        $this->grocery_crud->display_as('id', 'フォームID');
        $this->grocery_crud->display_as('name', 'フォーム名');
        $this->grocery_crud->display_as('description', 'フォームの説明');
        $this->grocery_crud->display_as('open_at', '受付開始日時');
        $this->grocery_crud->display_as('close_at', '受付終了日時');
        $this->grocery_crud->display_as('max_answers', '企画毎に回答可能とする回答数');

        $this->grocery_crud->columns(
            'id',
            'name',
            'description',
            'tags',
            'open_at',
            'close_at',
            'created_at',
            'updated_at',
            'max_answers',
            'is_public',
            'created_by'
        );
        $this->grocery_crud->fields(
            'name',
            'description',
            'tags',
            'open_at',
            'close_at',
            'created_at',
            'updated_at',
            'max_answers',
            'is_public',
            'created_by'
        );
        $this->grocery_crud->change_field_type('created_at', 'invisible');
        $this->grocery_crud->change_field_type('updated_at', 'invisible');
        $this->grocery_crud->change_field_type('created_by', 'invisible');

        $this->grocery_crud->required_fields('name', 'open_at', 'close_at', 'type', 'is_public');

        if ($this->grocery_crud->getstate() !== 'edit' && $this->grocery_crud->getstate() !== 'add') {
            $this->grocery_crud->set_relation('created_by', 'users', '{student_id} {name_family} {name_given}');
            $this->grocery_crud->display_as('tags', '回答可能な企画のタグ');
        } else {
            $this->grocery_crud->display_as('tags', '回答可能な企画のタグ(空欄の場合、全企画が回答可能)');
        }

        // フォームに回答可能なタグ一覧
        $this->grocery_crud->set_relation_n_n('tags', 'form_answerable_tags', 'tags', 'form_id', 'tag_id', 'name');

        // フォームタイプ表示
        $this->grocery_crud->callback_column('type', array($this, '_crud_form_type'));

        // フォームタイプ入力テキストボックス
        $this->grocery_crud->field_type(
            'type',
            'dropdown',
            ['booth' => 'booth:ブース申請', 'circle' => 'circle:サークル申請']
        );

        // 受付開始日時と終了日時のバリデーション
        $this->grocery_crud->set_rules('close_at', '受付終了日時', 'callback__crud_form_check_dates['. $this->input->post('open_at', true). ']');

        $this->grocery_crud->unset_delete();
        $this->grocery_crud->set_editor();
        $this->grocery_crud->set_copy_url();

        $vars += (array)$this->grocery_crud->render();

        $this->_render('home_staff/crud', $vars);
    }

    /**
     * フォームタイプをわかりやすく表示させるための Grocery CRUD コールバック関数
     */
    public function _crud_form_type($value, $row)
    {
        if ($row->type === "booth") {
            return $value = "ブース申請";
        } elseif ($row->type === "circle") {
            return $value = "サークル申請";
        } else {
            return $value = "(不正な値:{$row->type})";
        }
    }

    /**
     * フォームの受付終了日時が受付開始日時より後かどうかを判断するための Grocery CRUD コールバック関数
     */
    public function _crud_form_check_dates($close_at, $open_at)
    {
        $carbon_close_at = new CarbonImmutable($close_at);
        $carbon_open_at = new CarbonImmutable($open_at);

        if ($carbon_close_at->lte($carbon_open_at)) {
            $this->form_validation->set_message('_crud_form_check_dates', '受付終了日時には、受付開始日時より後の日付を指定してください。');
            return false;
        }

        return true;
    }

    /**
     * 申請フォーム情報ページ(個別表示)
     *
     * applications/read/:id として使用
     */
    public function _applications_read($form_id, $mode = null)
    {
        $vars = [];
        $vars["page_title"] = "回答一覧";
        $vars["main_page_type"] = "applications";
        $vars["copied"] = $_GET['copied'] ?? '0';
        $this->forms->include_private = true;

        // フォーム情報を取得する
        $form_info = $this->forms->get_form_by_form_id($form_id);

        if ($form_info !== false) {
            // 存在する場合
            $vars["form"] = $form_info;
            // 全回答を取得する
            $vars["answers"] = $this->forms->get_answers($form_id);
            // 統計情報を取得する
            $vars["statistics"] = $this->forms->get_statistics_by_form_id($form_id);
            // 申請フォームのURL
            $vars["public_form_url"] = base_url("/forms/{$form_id}/answers/create");
            // エディターのURL
            $vars["editor_url"] = base_url("/staff/forms/{$form_id}/editor");
        } else {
            // 存在しない場合
            show_404();
        }

        // $mode で場合分け
        switch ($mode) {
            case "csv":
                $this->_application_read_csv($vars);
                break;
            case "print":
                $this->_render('home_staff/applications_read_print', $vars);
                break;
            case null:
                $this->_render('home_staff/applications_read', $vars);
                break;
            default:
                $this->_error("エラー", "このページは存在しません。", 404);
                break;
        }
    }

    private function _application_read_csv($vars)
    {
        // カラム名
        $string_to_export = "ID\t企画ID\t企画名\t企画名(よみ)\t企画を出店する団体の名称\t企画を出店する団体の名称(よみ)";

        if ($vars["form"]->type === "booth") {
            $string_to_export .= "\tブース名";
        }

        $string_to_export .= "\t回答作成日時";
        $string_to_export .= "\t回答更新日時";

        foreach ($vars["form"]->questions as $question) {
            if ($question->type !== 'heading') {
                $string_to_export .= "\t" . $question->name;
            }
        }

        $string_to_export .= "\n";

        // 回答内容
        foreach ($vars["answers"] as $answer) {
            // 回答ID
            $string_to_export .= $answer->id;
            // 企画ID
            $string_to_export .= "\t" . $answer->circle->id;
            // 企画名
            $string_to_export .= "\t" . $answer->circle->name;
            // 企画名(よみ)
            $string_to_export .= "\t" . $answer->circle->name_yomi;
            // 企画を出店する団体の名称
            $string_to_export .= "\t" . $answer->circle->group_name;
            // 企画を出店する団体の名称(よみ)
            $string_to_export .= "\t" . $answer->circle->group_name_yomi;
            // ブース名
            if ($vars["form"]->type === "booth") {
                $string_to_export .= "\t" . $answer->booth->place_name;
            }
            // 作成日時
            $string_to_export .= "\t" . $answer->created_at;
            // 更新日時
            $string_to_export .= "\t" . $answer->updated_at;
            // 回答本体
            foreach ($vars["form"]->questions as $question) {
                if ($question->type === "heading") {
                    continue;
                }
                if ($question->type === "checkbox" || $question->type === "radio" ||
                    $question->type === "select") {
                    // 多肢選択式
                    $string_to_export .= "\t";
                    if (isset($answer->answers[$question->id])) {
                        if (is_array($answer->answers[$question->id])) {
                            $string_to_export .= implode('/', $answer->answers[$question->id]);
                        } else {
                            $string_to_export .= $answer->answers[$question->id];
                        }
                    }
                } elseif ($question->type === "upload") {
                    // ファイルアップロード
                    $string_to_export .= "\t" .
                        str_replace('answer_details/', '', $answer->answers[$question->id] ?? '');
                } else {
                    // Not多肢選択式、Notファイルアップロード
                    $string_to_export .= "\t" .
                        str_replace(
                            ["\r\n", "\n", "\r", "\t"],
                            ["{{改行}}", "{{改行}}", "{{改行}}", "{{タブ文字}}"],
                            $answer->answers[$question->id] ?? ''
                        );
                }
            }

            // 行末
            $string_to_export .= "\n";
        }

        // Convert to UTF-16LE and Prepend BOM
        $string_to_export = "\xFF\xFE" . mb_convert_encoding($string_to_export, 'UTF-16LE', 'UTF-8');
        $filename = "export-" . date("Y-m-d_H:i:s") . ".csv";
        header('Content-type: text/tab-separated-values;charset=UTF-16LE');
        header('Content-Disposition: attachment; filename=' . $filename);
        header("Cache-Control: no-cache");
        echo $string_to_export;
    }

    /**
     * 回答個別表示
     */
    public function applications_answer_read($answer_id)
    {
        $vars = [];
        $vars["page_title"] = "申請管理";
        $vars["main_page_type"] = "applications";

        $this->forms->include_private = true;

        // 回答情報を取得する
        $answer = $this->forms->get_answer_by_answer_id($answer_id);
        if ($answer !== false) {
            // 存在する場合
            $vars["form"] = $this->forms->get_form_by_form_id($answer->form_id);
            $vars["answer_info"] = $answer;
            $vars["answers"] = $answer->answers;
            $vars["next_answer"] = $this->forms->next_answer($answer_id, $vars["form"]->id);
            $vars["prev_answer"] = $this->forms->prev_answer($answer_id, $vars["form"]->id);
        } else {
            // 存在しない場合
            show_404();
        }

        $this->_render('home_staff/applications_answer_read', $vars);
    }

    /**
     * ユーザー権限管理ページ( Admin によるアクセスのみを許可する )
     */
    public function roles()
    {
        $this->_admin_only();

        $vars = [];
        $vars["page_title"] = "ユーザー権限管理(Admin)";
        $vars["main_page_type"] = "roles";

        // ユーザー権限ID=0 (Admin) の情報の編集を禁止
        $this->grocery_crud->where('id !=', 0);

        $this->grocery_crud->set_table('roles');
        $this->grocery_crud->set_subject('ユーザー権限');
        $this->grocery_crud->display_as('id', 'ユーザー権限ID');
        $this->grocery_crud->display_as('name', '権限名');

        $this->grocery_crud->columns('id', 'name', 'notes');
        $this->grocery_crud->fields('id', 'name', 'notes');

        $this->grocery_crud->required_fields('name');

        $vars += (array)$this->grocery_crud->render();

        $this->_render('home_staff/crud', $vars);
    }

    /**
     * 認可設定( Admin によるアクセスのみを許可する )
     */
    public function auth_config($mode = null, $mode2 = null)
    {
        $this->_admin_only();

        $vars = [];
        $vars["main_page_type"] = "auth_config";

        switch ($mode) {
            case null:
                $vars["pages"] = $this->auth_model->get_all_auth_staff_page();
                $vars["roles"] = $this->users->get_all_roles();
                $vars["csrf_test_name"] = $this->input->cookie("csrf_cookie_name");
                $this->_render('home_staff/auth_index', $vars);
                break;

            case "new":
                $this->_auth_config_new();
                break;

            case "ajax":
                if (!$this->input->is_ajax_request() || $this->input->method() !== 'post') {
                    show_404();
                }

                if ($mode2 === "AddRole") {
                    $this->_auth_config_add_role(
                        $this->input->post("id"),
                        $this->input->post("is_authorized"),
                        $this->input->post("role_id")
                    );
                }
                if ($mode2 === "DeletePage") {
                    $this->_auth_config_delete_page($this->input->post("id"));
                }
                if ($mode2 === "DeleteRole") {
                    $this->_auth_config_delete_role($this->input->post("id"));
                }
                if ($mode2 === "ToggleMode") {
                    $this->_auth_config_toggle_mode($this->input->post("id"), $this->input->post("is_authorized"));
                }

                break;

            default:
                show_404();
                break;
        }
    }

    /**
     * 認可設定 / 設定を追加
     */
    public function _auth_config_new()
    {
        $this->_admin_only();

        $vars = [];
        $vars["main_page_type"] = "auth_config";

        if ($this->input->method() === 'post') {
            // POST された時
            $this->load->library("form_validation");

            $this->form_validation->set_rules(
                "main_page_type",
                "main_page_type",
                "required|trim|is_unique[auth_staff_page.main_page_type]"
            );
            $this->form_validation->set_rules("is_authorized", "モード", "trim|in_list[0,1]");

            if ($this->form_validation->run()) {
                $result = $this->auth_model->add_auth_staff_page(
                    $this->input->post('main_page_type'),
                    $this->input->post('is_authorized')
                );
                $this->session->set_flashdata('post_result', $result);
                codeigniter_redirect("home_staff/auth_config/new");
            }
        }

        $vars["post_result"] = $_SESSION["post_result"] ?? null;
        $this->_render('home_staff/auth_new', $vars);
    }

    /**
     * 認可設定 / 権限を追加
     */
    public function _auth_config_add_role($id, $is_authorized, $role_id)
    {
        $this->_admin_only();
        $this->load->library("form_validation");

        $result = new stdClass();

        if ((int)$role_id === 0) {
            $result->result = "failed";
            $result->failed_msg = "Admin を追加することはできません。";
            echo json_encode($result);
            return;
        }

        if ($this->auth_model->get_auth_staff_role($id, $role_id) !== false) {
            $result->result = "failed";
            $result->failed_msg = "同じ項目がすでに追加されています。";
            echo json_encode($result);
            return;
        }

        if ($this->auth_model->add_auth_staff_role(
            $this->input->post("id"),
            $this->input->post("role_id"),
            $this->input->post("is_authorized")
        )
        ) {
            $result->result = "success";
            $result->id = $id; // auth_staff_page.id
            $result->is_authorized = $is_authorized;
            $result->role_id = $role_id;
            $result->role_name = $this->users->get_role_info_by_role_id($role_id)->name;
            $result->auth_staff_role_id = $this->auth_model->get_auth_staff_role($id, $role_id)->id;
            echo json_encode($result);
        } else {
            $result->result = "failed";
            $result->failed_msg = "追加に失敗しました。";
            echo json_encode($result);
        }
    }

    /**
     * 認可設定 / 設定(ページ)を削除
     */
    public function _auth_config_delete_page($id)
    {
        $this->_admin_only();
        if ($this->auth_model->delete_auth_staff_page($id)) {
            echo "success";
        } else {
            echo "failed";
        }
    }

    /**
     * 認可設定 / 設定(権限)を削除
     */
    public function _auth_config_delete_role($id)
    {
        $this->_admin_only();
        if ($this->auth_model->delete_auth_staff_role($id)) {
            echo "success";
        } else {
            echo "failed";
        }
    }

    /**
     * 認可設定 / モードを切り替える
     */
    public function _auth_config_toggle_mode($id, $is_authorized)
    {
        $this->_admin_only();
        if ($this->auth_model->edit_auth_staff_page(
            $this->input->post("id"),
            $this->input->post("is_authorized")
        )) {
            echo "success";
        } else {
            echo "failed";
        }
    }

    /**
     * スタッフ認証ページ
     */
    public function verify_access()
    {
        $vars = [];
        $vars["main_page_type"] = "verify_access";
        $vars["xs_navbar_title"] = "スタッフ認証";
        $vars["xs_navbar_back"] = true; // 戻るボタンを表示

        if ($this->input->method() !== "post") {
            // POST でないとき
            // 認証コードを作成して送付
            $code = random_int(100000, 999999);
            $_SESSION["staff_verify_code"] = $code;

            $vars_email = [];
            $vars_email["name_to"] = $this->_get_login_user()->name_family . " " . $this->_get_login_user()->name_given;
            $vars_email["verify_code"] = $code;
            $this->_send_email(
                $this->_get_login_user()->email,
                "スタッフ用認証コード送付",
                'email/verify_staff',
                $vars_email
            );
        } else {
            // POST のとき
            $code_on_session = isset($_SESSION["staff_verify_code"]) ? $_SESSION["staff_verify_code"] : null;
            unset($_SESSION["staff_verify_code"]);

            if (isset($code_on_session) &&
                (int)$code_on_session === (int)$this->input->post("verify_code")) {
                // 認証成功
                $_SESSION['staff_authorized'] = true;
                codeigniter_redirect("staff");
            } else {
                // 認証失敗
                $this->_error("認証失敗", "入力されたコードが間違っています。");
            }
        }

        $this->_render('home_staff/verify_access', $vars);
    }

    public function _render($template_filename, $vars = [], $file_type = 'html')
    {
        $vars["_home_type"] = "staff"; // staff or default

        // 認可されていないページへのアクセスをブロックする
        if (isset($vars["main_page_type"]) &&
            $this->auth_model->is_staff_user_authorized($vars["main_page_type"], $this->_get_login_user()) === false) {
            $this->_error("アクセス禁止", "このページにアクセスする権限がありません。\nアクセスを希望する場合は、システム管理者に連絡してください。", 403);
        }

        // メールによる２段階認証が完了していない場合，ログイン画面を表示する
        if (isset($vars["main_page_type"]) && $vars["main_page_type"] !== "verify_access"
            && (!isset($_SESSION['staff_authorized']) || $_SESSION['staff_authorized'] === false)) {
            codeigniter_redirect("home_staff/verify_access");
        }

        // xs_main_title をセット
        if (!empty($vars["page_title"])) {
            $vars["xs_navbar_title"] = $vars["page_title"];
        }

        if (isset($vars["main_page_type"]) && $vars["main_page_type"] !== "verify_access") {
            $vars["xs_navbar_toggle"] = true; // ナビバーにサイドバートグルボタンを表示
        }

        // 今どのページにいるか
        $vars["_crud_state"] = $this->grocery_crud->getstate();

        if (!isset($vars["_sidebar_menu"])) {
            $vars["_sidebar_menu"] = [
                "index" => [
                    "icon" => "home",
                    "name" => "スタッフモード ホーム",
                    "url" => "staff",
                ],
                "users" => [
                    "icon" => "address-book-o",
                    "name" => "ユーザー情報管理",
                    "url" => "staff/users",
                ],
                "circles" => [
                    "icon" => "star",
                    "name" => "企画情報管理",
                    "url" => "staff/circles",
                ],
                "tags" => [
                    "icon" => "tags",
                    "name" => "企画タグ管理",
                    "url" => "staff/tags",
                ],
                "places" => [
                    "icon" => "map-marker",
                    "name" => "場所情報管理",
                    "url" => "staff/places",
                ],
                "pages" => [
                    "icon" => "bullhorn",
                    "name" => "お知らせ管理",
                    "url" => "staff/pages",
                ],
                "documents" => [
                    "icon" => "file-text-o",
                    "name" => "配布資料管理",
                    "url" => "staff/documents",
                ],
                "applications" => [
                    "icon" => "pencil-square-o",
                    "name" => "申請管理",
                    "url" => "home_staff/applications",
                ],
                "schedules" => [
                    "icon" => "calendar",
                    "name" => "スケジュール管理",
                    "url" => "staff/schedules",
                ],
                "contact-categories" => [
                    "icon" => "at",
                    "name" => "お問い合わせ受付設定",
                    "url" => "staff/contacts/categories",
                ],
            ];

            // Admin only ページのリンクも用意する
            if ($this->_get_login_user()->is_admin === true) {
                $vars["_sidebar_menu"]["roles"] =
                    [
                        "icon" => "key",
                        "name" => "ユーザー権限管理(管理者)",
                        "url" => "home_staff/roles",
                    ];
                $vars["_sidebar_menu"]["auth_config"] =
                    [
                        "icon" => "key",
                        "name" => "認可設定(管理者)",
                        "url" => "home_staff/auth_config",
                    ];
                $vars["_sidebar_menu"]["system"] =
                    [
                        "icon" => "gear",
                        "name" => "ポータル情報設定(管理者)",
                        "url" => "admin/portal",
                    ];
            } else {
                // 現在ログインしているユーザーが認可されていないページへのリンクをサイドバーから削除する
                foreach ($vars["_sidebar_menu"] as $page_type => $item) {
                    if ($this->auth_model->is_staff_user_authorized($page_type, $this->_get_login_user()) === false) {
                        unset($vars["_sidebar_menu"][$page_type]);
                    }
                }
            }
        }
        parent::_render($template_filename, $vars, $file_type);
    }

    /**
     * Grocery CRUD で set_relation した際に使用される内部的なカラム名を取得する
     * @param string $field_name 内部的なカラム名を取得したい、テーブル上のカラム名
     * @return string             内部的なカラム名
     */
    public function _unique_field_name($field_name)
    {
        return $this->grocery_crud->_unique_field_name($field_name);
    }
}
