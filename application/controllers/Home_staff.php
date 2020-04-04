<?php

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
        $this->grocery_crud->display_as('notes', 'ｽﾀｯﾌ用ﾒﾓ');

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
     * _table_render 表示テスト用ページ
     */
    public function test()
    {
        $memo = "- name: イベント名\n- start_at: イベント開始日時\n- place: 場所\n- description: イベントの詳細説明(改行使用可・HTML不可)";
        $this->_table_render('schedules', 'テスト', $memo);
    }

    /**
     * メインページ
     */
    public function index()
    {

        $vars = [];
        $vars["main_page_type"] = "index";
        $vars["xs_navbar_title"] = "スタッフモードホーム";

        $this->_render('home_staff/index', $vars);
    }

    /**
     * お知らせ情報ページ
     */
    public function pages()
    {

        $vars = [];
        $vars["page_title"] = "お知らせ管理";
        $vars["main_page_type"] = "pages";

        $this->grocery_crud->set_table('pages');
        $this->grocery_crud->set_subject('ページ');
        $this->grocery_crud->display_as('id', 'ページID');

        $this->grocery_crud->columns(
            'id',
            'title',
            'body',
            'created_at',
            'updated_at',
            'created_by',
            'updated_by',
            'notes'
        );
        $this->grocery_crud->fields('title', 'body', 'created_at', 'updated_at', 'created_by', 'updated_by', 'notes');
        $this->grocery_crud->change_field_type('created_at', 'invisible');
        $this->grocery_crud->change_field_type('updated_at', 'invisible');
        $this->grocery_crud->change_field_type('created_by', 'invisible');
        $this->grocery_crud->change_field_type('updated_by', 'invisible');

        if ($this->grocery_crud->getstate() !== 'edit' && $this->grocery_crud->getstate() !== 'add') {
            $this->grocery_crud->set_relation('updated_by', 'users', '{student_id} {name_family} {name_given}');
            $this->grocery_crud->set_relation('created_by', 'users', '{student_id} {name_family} {name_given}');
        }

        $vars += (array)$this->grocery_crud->render();

        $this->_render('home_staff/crud', $vars);
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
        $this->grocery_crud->where('NOT EXISTS (SELECT * FROM custom_forms WHERE form_id = forms.id)', null, false);

        $this->grocery_crud->set_subject('フォーム');
        $this->grocery_crud->display_as('id', 'フォームID');
        $this->grocery_crud->display_as('name', 'フォーム名');
        $this->grocery_crud->display_as('description', 'フォームの説明');
        $this->grocery_crud->display_as('open_at', '受付開始日時');
        $this->grocery_crud->display_as('close_at', '受付終了日時');
        $this->grocery_crud->display_as('type', 'フォームタイプ');
        $this->grocery_crud->display_as('max_answers', '(フォームタイプ)毎に回答可能とする回答数');

        $this->grocery_crud->columns(
            'id',
            'name',
            'description',
            'open_at',
            'close_at',
            'created_at',
            'updated_at',
            'type',
            'max_answers',
            'is_public',
            'created_by'
        );
        $this->grocery_crud->fields(
            'name',
            'description',
            'open_at',
            'close_at',
            'created_at',
            'updated_at',
            'type',
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
        }

        // フォームタイプ表示
        $this->grocery_crud->callback_column('type', array($this, '_crud_form_type'));

        // フォームタイプ入力テキストボックス
        $this->grocery_crud->field_type(
            'type',
            'dropdown',
            ['booth' => 'booth:ブース申請', 'circle' => 'circle:サークル申請']
        );

        $this->grocery_crud->unset_delete();
        $this->grocery_crud->set_editor();

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
     * 申請フォーム情報ページ(個別表示)
     *
     * applications/read/:id として使用
     */
    public function _applications_read($form_id, $mode = null)
    {

        $vars = [];
        $vars["page_title"] = "回答一覧";
        $vars["main_page_type"] = "applications";

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
        $string_to_export = "ID\t企画ID\t企画の名前";

        if ($vars["form"]->type === "booth") {
            $string_to_export .= "\tブース名";
        }

        $string_to_export .= "\t作成日時";
        $string_to_export .= "\t更新日時";

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
            // 企画の名前
            $string_to_export .= "\t" . $answer->circle->name;
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
                        str_replace('answer_details/', 'answer_details__', $answer->answers[$question->id] ?? '');
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
     * ユーザー情報ページ
     */
    public function users()
    {

        $vars = [];
        $vars["page_title"] = "ユーザー情報管理";
        $vars["main_page_type"] = "users";

        if ($this->uri->segment(3) === "read") {
            // 個別表示の場合，Grocery CRUD を使用しない
            $user_id = $this->uri->segment(4);
            return $this->_users_read($user_id);
        }

        $this->grocery_crud->set_table('users');
        $this->grocery_crud->set_subject('ユーザー');
        $this->grocery_crud->display_as('id', 'ユーザーID');

        $this->grocery_crud->display_as('verify', 'メール認証');

        $columns = [
            'id',
            'student_id',
            'name_family',
            'name_family_yomi',
            'name_given',
            'name_given_yomi',
            'verify',
            'email',
            'tel',
            'is_staff',
            'created_at',
            'updated_at',
            'notes'
        ];
        if ($this->grocery_crud->getstate() === 'list' || $this->grocery_crud->getstate() === 'ajax_list' ||
            $this->grocery_crud->getstate() === 'print') {
            // list 表示の場合、よみがなカラムを表示しない
            $columns = array_diff($columns, ['name_family_yomi', 'name_given_yomi']);
            $columns = array_values($columns);
        }
        $this->grocery_crud->columns($columns);

        $fields = [
            'student_id',
            'name_family',
            'name_family_yomi',
            'name_given',
            'name_given_yomi',
            'tel',
            'is_staff',
            'updated_at',
            'notes'
        ];
        if ($this->_get_login_user()->is_admin) {
            // 管理者のみ、Rolesの設定をできるようにする
            $fields[] = 'roles';
        }
        $this->grocery_crud->fields($fields);

        $this->grocery_crud->change_field_type('updated_at', 'invisible');

        $this->grocery_crud->set_relation_n_n('roles', 'role_user', 'roles', 'user_id', 'role_id', '{name}');

        $this->grocery_crud->unique_fields(['student_id']);

        $this->grocery_crud->unset_add();
        $this->grocery_crud->unset_delete();

        if ($this->grocery_crud->getstate() === 'list' || $this->grocery_crud->getstate() === 'ajax_list' ||
            $this->grocery_crud->getstate() === 'print') {
            $this->grocery_crud->callback_column('name_family', array($this, '_crud_name_family_yomi'));
            $this->grocery_crud->callback_column('name_given', array($this, '_crud_name_given_yomi'));
        }

        $this->grocery_crud->callback_column('verify', array($this, '_crud_email_verified'));

        $vars += (array)$this->grocery_crud->render();

        $this->_render('home_staff/crud', $vars);
    }

    /**
     * 姓にふりがなをふるための Grocery CRUD コールバック関数
     */
    public function _crud_name_family_yomi($value, $row)
    {
        return "<ruby>" . $value . "<rt>" . $row->name_family_yomi . "</rt></ruby>";
    }

    /**
     * 名にふりがなをふるための Grocery CRUD コールバック関数
     */
    public function _crud_name_given_yomi($value, $row)
    {
        return "<ruby>" . $value . "<rt>" . $row->name_given_yomi . "</rt></ruby>";
    }

    /**
     * メール認証の完了がしているかどうか表示するための Grocery CRUD コールバック関数
     */
    public function _crud_email_verified($value, $row)
    {
        if (empty($row->email_verified_at) || empty($row->univemail_verified_at)) {
            return '<span class="text-danger">未認証</span>';
        }
        return '<span class="text-success">認証済み</span>';
    }

    /**
     * ユーザー情報ページ(個別表示)
     *
     * users/read/:id として使用
     */
    public function _users_read($user_id)
    {

        $vars = [];
        $vars["page_title"] = "ユーザー情報管理";
        $vars["main_page_type"] = "users";

        // ユーザー情報を取得する
        $userinfo = $this->users->get_user_by_user_id($user_id);

        if ($userinfo !== false) {
            // 存在する場合
            $vars["user_read"] = $userinfo;
            // このユーザーが所属する企画も取得する
            $vars["circles"] = $this->circles->get_circle_info_by_user_id($userinfo->id);
        } else {
            // 存在しない場合
            show_404();
        }

        $this->_render('home_staff/users_read', $vars);
    }

    /**
     * 企画情報ページ
     */
    public function circles()
    {
        $vars = [];
        $vars["page_title"] = "企画情報管理";
        $vars["main_page_type"] = "circles";

        if ($this->uri->segment(3) === "read") {
            // 個別表示の場合，Grocery CRUD を使用しない
            $circle_id = $this->uri->segment(4);
            return $this->_circles_read($circle_id);
        } elseif ($this->uri->segment(3) === "edit") {
            $circle_id = $this->uri->segment(4);
            $edit_url = ['staff', 'circles', $circle_id, 'edit'];
            codeigniter_redirect(base_url($edit_url));
        } elseif ($this->uri->segment(3) === "add") {
            $edit_url = ['staff', 'circles', 'create'];
            codeigniter_redirect(base_url($edit_url));
        }

        $this->grocery_crud->set_table('circles');
        $this->grocery_crud->where('submitted_at IS NOT NULL', null, false);
        $this->grocery_crud->set_subject('企画');
        $this->grocery_crud->display_as('id', '企画ID');
        $this->grocery_crud->display_as('name', '企画の名前');
        $this->grocery_crud->display_as('name_yomi', '企画の名前(よみ)');
        $this->grocery_crud->display_as('group_name', '企画団体の名前');
        $this->grocery_crud->display_as('group_name_yomi', '企画団体の名前(よみ)');
        $this->grocery_crud->display_as('submitted_at', '参加登録提出日時');
        $this->grocery_crud->display_as('status', '登録受理状況');
        $this->grocery_crud->display_as('status_set_at', '登録受理状況設定日時');
        $this->grocery_crud->display_as('status_set_by', '登録受理状況設定ユーザー');

        $columns = [
            'id',
            'name',
            'name_yomi',
            'group_name',
            'group_name_yomi',
            'submitted_at',
            'status',
            'status_set_at',
            'status_set_by',
            'created_at',
            'updated_at',
            'notes',
        ];

        if ($this->grocery_crud->getstate() === 'list' || $this->grocery_crud->getstate() === 'ajax_list' ||
            $this->grocery_crud->getstate() === 'print') {
            // list 表示の場合、よみがなカラムを表示しない
            $columns = array_diff($columns, ['name_yomi', 'group_name_yomi']);
            $columns = array_values($columns);

            // よみがなはルビとして表示する
            $this->grocery_crud->callback_column('name', array($this, '_crud_circle_name_yomi'));
            $this->grocery_crud->callback_column('group_name', array($this, '_crud_circle_group_name_yomi'));
        }

        // 登録受理状況
        $this->grocery_crud->callback_column('status', array($this, '_crud_circle_status'));

        $this->grocery_crud->columns($columns);

        if ($this->grocery_crud->getstate() !== 'edit' && $this->grocery_crud->getstate() !== 'add') {
            $this->grocery_crud->set_relation('status_set_by', 'users', '{student_id} {name_family} {name_given}');
        }

        $vars += (array)$this->grocery_crud->render();

        // カスタムフォームが存在する場合、カスタムフォーム設定もビューにわたす
        $vars['custom_form'] = $this->forms->get_custom_form_by_type('circle');

        $this->_render('home_staff/crud', $vars);
    }

    /**
     * 企画の名前にふりがなをふるための Grocery CRUD コールバック関数
     */
    public function _crud_circle_name_yomi($value, $row)
    {
        return "<ruby>" . $value . "<rt>" . $row->name_yomi . "</rt></ruby>";
    }

    /**
     * 企画団体の名前にふりがなをふるための Grocery CRUD コールバック関数
     */
    public function _crud_circle_group_name_yomi($value, $row)
    {
        return "<ruby>" . $value . "<rt>" . $row->group_name_yomi . "</rt></ruby>";
    }

    /**
     * 登録受理状況を表示するための Grocery CRUD コールバック関数
     */
    public function _crud_circle_status($value, $row)
    {
        if ($row->status === 'approved') {
            return '<span class="text-success">受理</span>';
        } elseif ($row->status === 'rejected') {
            return '<span class="text-danger">不受理</span>';
        }
        return '<span class="text-muted">確認中</span>';
    }

    /**
     * 企画情報ページ(個別表示)
     *
     * circles/read/:id として使用
     */
    public function _circles_read($circle_id)
    {

        $vars = [];
        $vars["page_title"] = "企画情報管理";
        $vars["main_page_type"] = "circles";

        // 企画情報を取得する
        $circle_info = $this->circles->get_circle_info_by_circle_id($circle_id);

        if ($circle_info !== false && isset($circle_info->submitted_at)) {
            // 存在する場合
            $vars["circle_info"] = $circle_info;
            // この企画に所属するユーザーも取得する
            $vars["users"] = $this->circles->get_user_info_by_circle_id($circle_info->id);
        } else {
            // 存在しない場合か、参加登録が未提出の企画の場合
            show_404();
        }
        $this->_render('home_staff/circles_read', $vars);
    }

    /**
     * ブース情報ページ
     */
    public function booths()
    {

        $vars = [];
        $vars["page_title"] = "ブース情報管理";
        $vars["main_page_type"] = "booths";

        $this->grocery_crud->set_table('booths');
        $this->grocery_crud->set_subject('ブース');
        $this->grocery_crud->display_as('id', 'ブースID');

        $this->grocery_crud->columns(
            'id',
            'place_id',
            'circle_id',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            'notes'
        );
        $this->grocery_crud->fields(
            'place_id',
            'circle_id',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            'notes'
        );
        $this->grocery_crud->change_field_type('created_at', 'invisible');
        $this->grocery_crud->change_field_type('created_by', 'invisible');
        $this->grocery_crud->change_field_type('updated_at', 'invisible');
        $this->grocery_crud->change_field_type('updated_by', 'invisible');

        $this->grocery_crud->required_fields('place_id', 'circle_id');

        $this->grocery_crud->set_relation('place_id', 'places', '{name}(ID:{id})');
        $this->grocery_crud->set_relation('circle_id', 'circles', '{name}(ID:{id})');
        if ($this->grocery_crud->getstate() !== 'edit' && $this->grocery_crud->getstate() !== 'add') {
            $this->grocery_crud->set_relation('created_by', 'users', '{student_id} {name_family} {name_given}');
            $this->grocery_crud->set_relation('updated_by', 'users', '{student_id} {name_family} {name_given}');
        }

        // ファイル表示リンクにする
        $this->grocery_crud->callback_column('image_filename', array($this, '_crud_download_image_filename'));

        // 存在しない企画IDが設定されている場合、企画不明という表示にする
        $this->grocery_crud->callback_column(
            $this->_unique_field_name('circle_id'),
            [$this, '_crud_unknown_circle_id']
        );

        $vars += (array)$this->grocery_crud->render();

        $this->_render('home_staff/crud', $vars);
    }

    /**
     * 存在しない企画IDが設定されている場合、企画不明という表示にするための Grocery CRUD コールバック関数
     */
    public function _crud_unknown_circle_id($value, $row)
    {
        if ($value === "(ID:)") {
            return "<span class='text-muted'>(企画不明)</span>";
        } else {
            return $value;
        }
    }

    /**
     * 場所情報ページ
     */
    public function places()
    {

        $vars = [];
        $vars["page_title"] = "場所情報管理";
        $vars["main_page_type"] = "places";

        $this->grocery_crud->set_table('places');
        $this->grocery_crud->set_subject('場所');
        $this->grocery_crud->display_as('id', '場所ID');
        $this->grocery_crud->display_as('name', '場所名');
        $this->grocery_crud->display_as('type', 'タイプ');

        $this->grocery_crud->columns('id', 'name', 'type', 'notes');
        $this->grocery_crud->fields('name', 'type', 'notes');

        $this->grocery_crud->required_fields('name', 'type');

        $this->grocery_crud->unique_fields(['name']);

        // 場所タイプ表示
        $this->grocery_crud->callback_column('type', array($this, '_crud_place_type'));

        // 場所タイプ入力テキストボックス
        $this->grocery_crud->field_type(
            'type',
            'dropdown',
            ['1' => '1:屋内', '2' => '2:屋外', '3' => '3:特殊場所']
        );

        $this->grocery_crud->unset_read();

        $vars += (array)$this->grocery_crud->render();

        $this->_render('home_staff/crud', $vars);
    }

    /**
     * 場所タイプをわかりやすく表示させるための Grocery CRUD コールバック関数
     */
    public function _crud_place_type($value, $row)
    {
        if ((int)$row->type === 1) {
            return $value = "1:屋内";
        } elseif ((int)$row->type === 2) {
            return $value = "2:屋外";
        } elseif ((int)$row->type === 3) {
            return $value = "3:特殊場所";
        } else {
            return $value = "(不正な値:{$row->type})";
        }
    }

    /**
     * 場所タイプ入力テキストボックスを表示させるための Grocery CRUD コールバック関数
     */
    function _crud_place_type_edit($value, $primary_key)
    {
        $selected = [
            'option-0' => '',
            'option-1' => '',
            'option-2' => '',
        ];
        $selected['option-' . $value] = ' selected';
        $return = '<select id="field-type" name="type" class="form-control">';
        $return .= '  <option value="0"' . $selected['option-0'] . '>0:屋内</option>';
        $return .= '  <option value="1"' . $selected['option-1'] . '>1:屋外</option>';
        $return .= '  <option value="2"' . $selected['option-2'] . '>2:特殊場所</option>';
        $return .= '</select>';
        return $return;
    }

    /**
     * 配布資料情報ページ
     */
    public function documents()
    {

        $vars = [];
        $vars["page_title"] = "配布資料管理";
        $vars["main_page_type"] = "documents";

        $this->grocery_crud->set_table('documents');
        $this->grocery_crud->set_subject('配布資料');
        $this->grocery_crud->display_as('id', '配布資料ID');
        $this->grocery_crud->display_as('name', '配布資料名');
        $this->grocery_crud->display_as('filename', 'ファイル');
        $this->grocery_crud->display_as('schedule_id', 'イベント');
        $this->grocery_crud->display_as('description', '説明');

        $this->grocery_crud->columns(
            'id',
            'name',
            'description',
            'filename',
            'schedule_id',
            'is_public',
            'is_important',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            'notes'
        );
        $this->grocery_crud->fields(
            'name',
            'description',
            'filename',
            'schedule_id',
            'is_public',
            'is_important',
            'created_at',
            'created_by',
            'updated_at',
            'updated_by',
            'notes'
        );
        $this->grocery_crud->change_field_type('created_at', 'invisible');
        $this->grocery_crud->change_field_type('created_by', 'invisible');
        $this->grocery_crud->change_field_type('updated_at', 'invisible');
        $this->grocery_crud->change_field_type('updated_by', 'invisible');

        $this->grocery_crud->set_relation('schedule_id', 'schedules', '{name}(ID:{id})');
        if ($this->grocery_crud->getstate() !== 'edit' && $this->grocery_crud->getstate() !== 'add') {
            $this->grocery_crud->set_relation('created_by', 'users', '{student_id} {name_family} {name_given}');
            $this->grocery_crud->set_relation('updated_by', 'users', '{student_id} {name_family} {name_given}');
        }

        $this->grocery_crud->required_fields('name', 'filename');

        $this->grocery_crud->set_field_upload('filename', PORTAL_UPLOAD_DIR_CRUD . '/documents');

        // ファイル表示リンクにする
        $this->grocery_crud->callback_column('filename', array($this, '_crud_download_document'));

        $vars += (array)$this->grocery_crud->render();

        $this->_render('home_staff/crud', $vars);
    }

    /**
     * スケジュール情報ページ
     */
    public function schedules()
    {

        $vars = [];
        $vars["page_title"] = "スケジュール管理";
        $vars["main_page_type"] = "schedules";

        $this->grocery_crud->set_table('schedules');
        $this->grocery_crud->set_subject('スケジュール');
        $this->grocery_crud->display_as('id', 'スケジュールID');
        $this->grocery_crud->display_as('name', '名前');
        $this->grocery_crud->display_as('start_at', '開始日時');
        $this->grocery_crud->display_as('place', '場所');
        $this->grocery_crud->display_as('description', '説明');

        $this->grocery_crud->columns('start_at', 'name', 'place', 'description', 'id', 'notes');
        $this->grocery_crud->fields('name', 'start_at', 'place', 'description', 'notes');

        $this->grocery_crud->required_fields('name', 'start_at', 'place');

        $this->grocery_crud->order_by('start_at');

        $vars += (array)$this->grocery_crud->render();

        $this->_render('home_staff/crud', $vars);
    }

    /**
     * ドキュメントファイルのダウンロードリンクを表示させるための Grocery CRUD コールバック関数
     */
    public function _crud_download_document($value, $row)
    {
        if (!empty($row->filename)) {
            return $value = '<a href="'. base_url("documents/". $row->id). '"  target="_blank">'.
                $row->filename. '</a>';
        }
        return $value = "-";
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
     * Execute SQL Statements( Admin's access only )
     */
    public function ess()
    {

        $this->_admin_only();

        $vars = [];
        $vars["main_page_type"] = "ess";

        # GA テスト
        echo "<pre>";
        $ga = new PHPGangsta_GoogleAuthenticator();
        $secret = $ga->createSecret();
        echo "Secret is: " . $secret . "\n\n";

        $qrCodeUrl = $ga->getQRCodeGoogleUrl(APP_NAME, $secret);
        echo "QR-Code: <img src='" . $qrCodeUrl . "'>'\n\n";

        $oneCode = $ga->getCode($secret);
        echo "Checking Code '$oneCode' and Secret '$secret':\n";

        $checkResult = $ga->verifyCode($secret, $oneCode, 2);    // 2 = 2*30sec clock tolerance
        if ($checkResult) {
            echo 'OK';
        } else {
            echo 'FAILED';
        }
        echo "</pre>";
        # End GA テスト

        $this->_render('home_staff/ess', $vars);
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
            $code_on_session = $_SESSION["staff_verify_code"];
            unset($_SESSION["staff_verify_code"]);

            if (isset($code_on_session) &&
                (int)$code_on_session === (int)$this->input->post("verify_code")) {
                // 認証成功
                $_SESSION['staff_authorized'] = true;
                codeigniter_redirect("home_staff/");
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
                    "icon" => "tachometer",
                    "name" => "スタッフモードホーム",
                    "url" => "home_staff",
                ],
                "pages" => [
                    "icon" => "newspaper-o",
                    "name" => "お知らせ管理",
                    "url" => "home_staff/pages",
                ],
                "applications" => [
                    "icon" => "pencil-square-o",
                    "name" => "申請管理",
                    "url" => "home_staff/applications",
                ],
                "users" => [
                    "icon" => "address-book-o",
                    "name" => "ユーザー情報管理",
                    "url" => "home_staff/users",
                ],
                "users_checker" => [
                    "icon" => "address-book-o",
                    "name" => "ユーザー登録チェッカー",
                    "url" => "staff/users/check",
                ],
                "circles" => [
                    "icon" => "users",
                    "name" => "企画情報管理",
                    "url" => "home_staff/circles",
                ],
                "booths" => [
                    "icon" => "star",
                    "name" => "ブース情報管理",
                    "url" => "home_staff/booths",
                ],
                "places" => [
                    "icon" => "map-marker",
                    "name" => "場所情報管理",
                    "url" => "home_staff/places",
                ],
                "documents" => [
                    "icon" => "file-text-o",
                    "name" => "配布資料管理",
                    "url" => "home_staff/documents",
                ],
                "schedules" => [
                    "icon" => "calendar",
                    "name" => "スケジュール管理",
                    "url" => "home_staff/schedules",
                ],
                // "contact" => [
                //   "icon" => "envelope-o",
                //   "name" => "お問い合わせ管理",
                //   "url" => "home_staff/contact",
                // ],
                // "help" => [
                //   "icon" => "question-circle",
                //   "name" => "ヘルプ",
                //   "url" => "home_staff/help",
                // ],
            ];

            // Admin only ページのリンクも用意する
            if ($this->_get_login_user()->is_admin === true) {
                $vars["_sidebar_menu"]["roles"] =
                    [
                        "icon" => "key",
                        "name" => "ユーザー権限管理(Admin)",
                        "url" => "home_staff/roles",
                    ];
                $vars["_sidebar_menu"]["auth_config"] =
                    [
                        "icon" => "key",
                        "name" => "認可設定(Admin)",
                        "url" => "home_staff/auth_config",
                    ];
                $vars["_sidebar_menu"]["ess"] =
                    [
                        "icon" => "terminal",
                        "name" => "ESS(Admin)", // Execute SQL Statements
                        "url" => "home_staff/ess",
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
