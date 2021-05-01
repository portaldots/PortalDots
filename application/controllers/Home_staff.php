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
            default:
                $this->_error("エラー", "このページは存在しません。", 404);
                break;
        }
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
            codeigniter_redirect("staff/verify");
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
                    "url" => "staff/forms",
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
                        "name" => "PortalDots の設定(管理者)",
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
}
